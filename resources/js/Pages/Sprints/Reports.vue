<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BurndownChart from '@/Components/BurndownChart.vue';
import type { Project, Sprint, BurndownData } from '@/Types';

interface SprintMetrics {
    total_stories: number;
    completed_stories: number;
    total_tasks: number;
    completed_tasks: number;
    total_hours: number;
    completed_hours: number;
    remaining_hours: number;
    progress_percentage: number;
    days_remaining: number;
    is_on_track: boolean;
}

interface Props {
    project: Project;
    sprint: Sprint;
    metrics: SprintMetrics;
    burndownData: BurndownData[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="`${sprint.name} - Reports`" />

    <MainLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link
                    :href="`/projects/${project.id}`"
                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <div
                    class="w-10 h-10 rounded-lg flex items-center justify-center text-white font-bold"
                    :style="{ backgroundColor: project.color }"
                >
                    {{ project.key?.substring(0, 2) }}
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ sprint.name }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ project.name }}</p>
                </div>
            </div>
        </template>

        <!-- Sprint Navigation -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-1 border-b border-gray-200 dark:border-gray-700">
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}`"
                    class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    Overview
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/board`"
                    class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    Board
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/timeline`"
                    class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    Timeline
                </Link>
                <Link
                    :href="`/projects/${project.id}/sprints/${sprint.id}/reports`"
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400"
                >
                    Reports
                </Link>
            </div>

            <!-- Sprint Status -->
            <div class="flex items-center space-x-4">
                <div
                    :class="[
                        'flex items-center px-4 py-2 rounded-lg',
                        metrics.is_on_track
                            ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                            : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                    ]"
                >
                    <svg
                        v-if="metrics.is_on_track"
                        class="w-5 h-5 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg
                        v-else
                        class="w-5 h-5 mr-2"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span class="font-medium">{{ metrics.is_on_track ? 'On Track' : 'At Risk' }}</span>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Days Remaining</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ metrics.days_remaining }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Progress</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ metrics.progress_percentage }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Hours Completed</p>
                <p class="text-3xl font-bold text-green-600 dark:text-green-400">{{ metrics.completed_hours }}h</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Hours Remaining</p>
                <p class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ metrics.remaining_hours }}h</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Burndown Chart -->
            <BurndownChart :data="burndownData" title="Sprint Burndown" />

            <!-- Progress Breakdown -->
            <div class="chart-container">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Progress Breakdown</h3>

                <!-- Stories Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Stories</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ metrics.completed_stories }} / {{ metrics.total_stories }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div
                            class="bg-indigo-600 h-4 rounded-full transition-all"
                            :style="{ width: `${metrics.total_stories ? (metrics.completed_stories / metrics.total_stories) * 100 : 0}%` }"
                        />
                    </div>
                </div>

                <!-- Tasks Progress -->
                <div class="mb-6">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Tasks</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ metrics.completed_tasks }} / {{ metrics.total_tasks }}
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div
                            class="bg-green-600 h-4 rounded-full transition-all"
                            :style="{ width: `${metrics.total_tasks ? (metrics.completed_tasks / metrics.total_tasks) * 100 : 0}%` }"
                        />
                    </div>
                </div>

                <!-- Hours Progress -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Hours</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            {{ metrics.completed_hours }}h / {{ metrics.total_hours }}h
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4">
                        <div
                            class="bg-purple-600 h-4 rounded-full transition-all"
                            :style="{ width: `${metrics.total_hours ? (metrics.completed_hours / metrics.total_hours) * 100 : 0}%` }"
                        />
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Story Completion</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ metrics.total_stories ? Math.round((metrics.completed_stories / metrics.total_stories) * 100) : 0 }}%
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Task Completion</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ metrics.total_tasks ? Math.round((metrics.completed_tasks / metrics.total_tasks) * 100) : 0 }}%
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sprint Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sprint Details</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ new Date(sprint.start_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">End Date</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ new Date(sprint.end_date).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sprint Length</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        {{ Math.ceil((new Date(sprint.end_date).getTime() - new Date(sprint.start_date).getTime()) / (1000 * 60 * 60 * 24)) }} days
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                    <span
                        :class="[
                            'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                            sprint.status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                            sprint.status === 'completed' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                            'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                        ]"
                    >
                        {{ sprint.status }}
                    </span>
                </div>
            </div>
            <div v-if="sprint.goal" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Sprint Goal</p>
                <p class="text-sm text-gray-900 dark:text-white">{{ sprint.goal }}</p>
            </div>
        </div>
    </MainLayout>
</template>
