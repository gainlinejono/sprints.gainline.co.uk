<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import TimelineView from '@/Components/TimelineView.vue';
import type { Project, Sprint } from '@/Types';

interface Props {
    project: Project;
    sprint: Sprint;
}

defineProps<Props>();
</script>

<template>
    <Head :title="`${sprint.name} - Timeline`" />

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
                    class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400"
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
        </div>

        <!-- Sprint Stats -->
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Days Remaining</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.days_remaining }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Stories</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.stories?.length || 0 }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Hours</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.total_hours }}h</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Progress</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ sprint.progress_percentage }}%</p>
            </div>
        </div>

        <!-- Timeline View -->
        <TimelineView
            :stories="sprint.stories || []"
            :sprint="sprint"
            :project-id="project.id"
        />
    </MainLayout>
</template>
