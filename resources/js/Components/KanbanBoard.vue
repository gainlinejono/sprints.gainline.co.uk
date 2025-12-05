<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import draggable from 'vuedraggable';
import type { Story, KanbanColumn } from '@/Types';

interface Props {
    stories: Story[];
    projectId: number;
    sprintId?: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['update']);

const columns = ref<KanbanColumn[]>([
    { id: 'backlog', title: 'Backlog', status: 'backlog', stories: [] },
    { id: 'todo', title: 'To Do', status: 'todo', stories: [] },
    { id: 'in_progress', title: 'In Progress', status: 'in_progress', stories: [] },
    { id: 'review', title: 'Review', status: 'review', stories: [] },
    { id: 'done', title: 'Done', status: 'done', stories: [] },
]);

// Initialize columns with stories
const initializeColumns = () => {
    columns.value.forEach(column => {
        column.stories = props.stories
            .filter(s => s.status === column.status)
            .sort((a, b) => a.order - b.order);
    });
};

initializeColumns();

const columnColors = {
    backlog: 'bg-gray-400',
    todo: 'bg-blue-500',
    in_progress: 'bg-yellow-500',
    review: 'bg-purple-500',
    done: 'bg-green-500',
};

const priorityColors = {
    low: 'border-l-gray-400',
    medium: 'border-l-blue-500',
    high: 'border-l-orange-500',
    critical: 'border-l-red-500',
};

const onDragEnd = (column: KanbanColumn) => {
    // Collect all stories with updated order and status
    const updates: { id: number; order: number; status: string }[] = [];

    columns.value.forEach((col, colIndex) => {
        col.stories.forEach((story, storyIndex) => {
            updates.push({
                id: story.id,
                order: storyIndex,
                status: col.status,
            });
        });
    });

    // Send batch update to server
    router.post(`/projects/${props.projectId}/stories/reorder`, {
        stories: updates,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const getColumnCount = (column: KanbanColumn) => {
    return column.stories.length;
};

const getColumnHours = (column: KanbanColumn) => {
    return column.stories.reduce((sum, s) => sum + parseFloat(String(s.estimated_hours || 0)), 0);
};
</script>

<template>
    <div class="flex space-x-4 overflow-x-auto pb-4 min-h-[calc(100vh-280px)]">
        <div
            v-for="column in columns"
            :key="column.id"
            class="flex-shrink-0 w-80"
        >
            <!-- Column Header -->
            <div class="flex items-center justify-between mb-3 px-2">
                <div class="flex items-center space-x-2">
                    <div :class="['w-3 h-3 rounded-full', columnColors[column.status]]"></div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ column.title }}</h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400">({{ getColumnCount(column) }})</span>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">{{ getColumnHours(column) }}h</span>
            </div>

            <!-- Draggable Column -->
            <draggable
                v-model="column.stories"
                :group="{ name: 'stories' }"
                item-key="id"
                class="kanban-column space-y-3"
                ghost-class="sortable-ghost"
                drag-class="sortable-drag"
                @end="() => onDragEnd(column)"
            >
                <template #item="{ element: story }">
                    <div
                        :class="[
                            'kanban-card border-l-4',
                            priorityColors[story.priority]
                        ]"
                    >
                        <!-- Story Header -->
                        <div class="flex items-start justify-between mb-2">
                            <Link
                                :href="`/projects/${projectId}/stories/${story.id}`"
                                class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                            >
                                {{ story.story_key }}
                            </Link>
                            <span v-if="story.estimated_hours" class="text-xs text-gray-500 dark:text-gray-400">
                                {{ story.estimated_hours }}h
                            </span>
                        </div>

                        <!-- Story Title -->
                        <Link
                            :href="`/projects/${projectId}/stories/${story.id}`"
                            class="block text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 mb-2"
                        >
                            {{ story.title }}
                        </Link>

                        <!-- Epic Badge -->
                        <div v-if="story.epic" class="mb-2">
                            <span
                                class="text-xs px-2 py-0.5 rounded-full"
                                :style="{ backgroundColor: story.epic.color + '20', color: story.epic.color }"
                            >
                                {{ story.epic.name }}
                            </span>
                        </div>

                        <!-- Story Footer -->
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <!-- Tasks Progress -->
                            <div v-if="story.tasks_count > 0" class="flex items-center text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                </svg>
                                {{ story.completed_tasks_count }}/{{ story.tasks_count }}
                            </div>
                            <div v-else></div>

                            <!-- Assignee -->
                            <div v-if="story.assignee" class="flex items-center">
                                <img
                                    v-if="story.assignee.avatar"
                                    :src="story.assignee.avatar"
                                    :alt="story.assignee.name"
                                    :title="story.assignee.name"
                                    class="w-6 h-6 rounded-full"
                                />
                                <div
                                    v-else
                                    :title="story.assignee.name"
                                    class="w-6 h-6 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-medium"
                                >
                                    {{ story.assignee.name?.charAt(0) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </draggable>

            <!-- Add Story Button -->
            <Link
                :href="`/projects/${projectId}/stories/create${sprintId ? `?sprint_id=${sprintId}` : ''}`"
                class="mt-3 w-full flex items-center justify-center p-3 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg text-gray-500 dark:text-gray-400 hover:border-indigo-400 hover:text-indigo-600 dark:hover:border-indigo-500 dark:hover:text-indigo-400"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Story
            </Link>
        </div>
    </div>
</template>
