<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import KanbanBoard from '@/Components/KanbanBoard.vue';
import type { Project, Sprint } from '@/Types';

interface Props {
    project: Project;
    sprint: Sprint;
}

const props = defineProps<Props>();

const completeSprint = () => {
    if (confirm('Are you sure you want to complete this sprint? Incomplete stories will be moved to backlog.')) {
        router.post(`/projects/${props.project.id}/sprints/${props.sprint.id}/complete`);
    }
};
</script>

<template>
    <Head :title="`${sprint.name} - Board`" />

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
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400"
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
                    class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                >
                    Reports
                </Link>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Sprint Status Badge -->
                <span
                    :class="[
                        'px-3 py-1 text-sm font-medium rounded-full',
                        sprint.status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' :
                        sprint.status === 'completed' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' :
                        'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                    ]"
                >
                    {{ sprint.status }}
                </span>

                <!-- Complete Sprint Button -->
                <button
                    v-if="sprint.status === 'active'"
                    @click="completeSprint"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg"
                >
                    Complete Sprint
                </button>
            </div>
        </div>

        <!-- Sprint Stats -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.days_remaining }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Progress</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.progress_percentage }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Completed</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.completed_hours }}h</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Remaining</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.remaining_hours }}h</p>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mb-6">
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div
                    class="bg-indigo-600 h-2 rounded-full transition-all duration-500"
                    :style="{ width: `${sprint.progress_percentage}%` }"
                />
            </div>
        </div>

        <!-- Kanban Board -->
        <KanbanBoard
            :stories="sprint.stories || []"
            :project-id="project.id"
            :sprint-id="sprint.id"
        />
    </MainLayout>
</template>
