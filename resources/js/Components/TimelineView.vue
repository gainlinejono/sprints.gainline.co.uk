<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { format, differenceInDays, addDays, startOfDay, isWithinInterval } from 'date-fns';
import type { Story, Sprint } from '@/Types';

interface Props {
    stories: Story[];
    sprint: Sprint;
    projectId: number;
}

const props = defineProps<Props>();

const sprintStart = computed(() => new Date(props.sprint.start_date));
const sprintEnd = computed(() => new Date(props.sprint.end_date));
const totalDays = computed(() => differenceInDays(sprintEnd.value, sprintStart.value) + 1);

const today = startOfDay(new Date());
const todayPosition = computed(() => {
    const diff = differenceInDays(today, sprintStart.value);
    if (diff < 0 || diff > totalDays.value) return null;
    return (diff / totalDays.value) * 100;
});

const dates = computed(() => {
    const result = [];
    let current = sprintStart.value;
    for (let i = 0; i < totalDays.value; i++) {
        result.push(addDays(current, i));
    }
    return result;
});

const statusColors = {
    backlog: 'bg-gray-400',
    todo: 'bg-blue-500',
    in_progress: 'bg-yellow-500',
    review: 'bg-purple-500',
    done: 'bg-green-500',
};

const priorityOrder = {
    critical: 0,
    high: 1,
    medium: 2,
    low: 3,
};

const sortedStories = computed(() => {
    return [...props.stories].sort((a, b) => {
        // Sort by priority first
        const priorityDiff = priorityOrder[a.priority] - priorityOrder[b.priority];
        if (priorityDiff !== 0) return priorityDiff;
        // Then by status
        const statusOrder = ['in_progress', 'review', 'todo', 'backlog', 'done'];
        return statusOrder.indexOf(a.status) - statusOrder.indexOf(b.status);
    });
});

// Calculate bar position and width based on estimated hours and sprint duration
const getBarStyle = (story: Story, index: number) => {
    // Distribute stories evenly across the sprint
    const storiesCount = sortedStories.value.length;
    const segmentSize = 100 / Math.max(storiesCount, 1);
    const startPercent = index * segmentSize;

    // Width based on estimated hours relative to total sprint hours
    const totalHours = props.stories.reduce((sum, s) => sum + parseFloat(String(s.estimated_hours || 0)), 0);
    let widthPercent = totalHours > 0
        ? (parseFloat(String(story.estimated_hours || 0)) / totalHours) * 100
        : segmentSize;

    // Ensure minimum width
    widthPercent = Math.max(widthPercent, 5);
    // Ensure doesn't exceed bounds
    widthPercent = Math.min(widthPercent, 100 - startPercent);

    return {
        left: `${startPercent}%`,
        width: `${widthPercent}%`,
    };
};
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Sprint Timeline</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ format(sprintStart, 'MMM d') }} - {{ format(sprintEnd, 'MMM d, yyyy') }}
            </p>
        </div>

        <!-- Timeline Grid -->
        <div class="overflow-x-auto">
            <div class="min-w-[800px]">
                <!-- Date Headers -->
                <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <div class="w-64 flex-shrink-0 p-3 border-r border-gray-200 dark:border-gray-700">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Story</span>
                    </div>
                    <div class="flex-1 flex relative">
                        <div
                            v-for="date in dates"
                            :key="date.toISOString()"
                            :class="[
                                'flex-1 p-2 text-center text-xs border-r border-gray-200 dark:border-gray-700 last:border-r-0',
                                date.getDay() === 0 || date.getDay() === 6
                                    ? 'bg-gray-100 dark:bg-gray-800/50'
                                    : ''
                            ]"
                        >
                            <div class="font-medium text-gray-700 dark:text-gray-300">
                                {{ format(date, 'EEE') }}
                            </div>
                            <div class="text-gray-500 dark:text-gray-400">
                                {{ format(date, 'd') }}
                            </div>
                        </div>
                        <!-- Today Marker -->
                        <div
                            v-if="todayPosition !== null"
                            class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10"
                            :style="{ left: `${todayPosition}%` }"
                        >
                            <div class="absolute -top-1 left-1/2 -translate-x-1/2 px-1.5 py-0.5 bg-red-500 text-white text-xs rounded">
                                Today
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Story Rows -->
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    <div
                        v-for="(story, index) in sortedStories"
                        :key="story.id"
                        class="flex hover:bg-gray-50 dark:hover:bg-gray-700/30"
                    >
                        <!-- Story Info -->
                        <div class="w-64 flex-shrink-0 p-3 border-r border-gray-200 dark:border-gray-700">
                            <Link
                                :href="`/projects/${projectId}/stories/${story.id}`"
                                class="block"
                            >
                                <div class="flex items-center space-x-2 mb-1">
                                    <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                        {{ story.story_key }}
                                    </span>
                                    <span :class="['w-2 h-2 rounded-full', statusColors[story.status]]"></span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ story.title }}
                                </p>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ story.estimated_hours }}h
                                    </span>
                                    <img
                                        v-if="story.assignee?.avatar"
                                        :src="story.assignee.avatar"
                                        :alt="story.assignee.name"
                                        class="w-5 h-5 rounded-full"
                                    />
                                </div>
                            </Link>
                        </div>

                        <!-- Timeline Bar -->
                        <div class="flex-1 p-2 relative">
                            <div class="absolute inset-y-2 flex items-center" style="left: 0; right: 0;">
                                <!-- Grid lines -->
                                <div
                                    v-for="date in dates"
                                    :key="date.toISOString()"
                                    :class="[
                                        'flex-1 h-full border-r border-gray-100 dark:border-gray-700/50 last:border-r-0',
                                        date.getDay() === 0 || date.getDay() === 6
                                            ? 'bg-gray-50 dark:bg-gray-800/30'
                                            : ''
                                    ]"
                                />
                            </div>
                            <!-- Story Bar -->
                            <div
                                class="relative h-full flex items-center"
                                :style="getBarStyle(story, index)"
                            >
                                <div
                                    :class="[
                                        'timeline-bar w-full',
                                        statusColors[story.status],
                                        story.status === 'done' ? 'opacity-60' : ''
                                    ]"
                                >
                                    <span class="absolute inset-0 flex items-center justify-center text-xs text-white font-medium truncate px-2">
                                        {{ story.estimated_hours }}h
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="stories.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                    No stories in this sprint
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
            <div class="flex items-center justify-center space-x-6 text-sm">
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">To Do</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">In Progress</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">Review</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">Done</span>
                </div>
            </div>
        </div>
    </div>
</template>
