<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project } from '@/Types';

interface Props {
    projects: Project[];
}

defineProps<Props>();
</script>

<template>
    <Head title="Projects" />

    <MainLayout>
        <template #header>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Projects</h1>
        </template>

        <div class="flex justify-between items-center mb-6">
            <p class="text-gray-600 dark:text-gray-400">Manage your sprint projects</p>
            <Link
                href="/projects/create"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Project
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
                v-for="project in projects"
                :key="project.id"
                :href="`/projects/${project.id}`"
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-600 transition-all"
            >
                <div class="flex items-start justify-between mb-4">
                    <div
                        class="w-14 h-14 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-md"
                        :style="{ backgroundColor: project.color }"
                    >
                        {{ project.key?.substring(0, 2) }}
                    </div>
                    <div v-if="project.sprints?.some(s => s.status === 'active')" class="flex items-center text-green-500">
                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                        <span class="text-xs font-medium">Active</span>
                    </div>
                </div>

                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ project.name }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2 mb-4">
                    {{ project.description || 'No description' }}
                </p>

                <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <div class="flex items-center space-x-4">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            {{ project.stories_count || 0 }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ project.sprints_count || 0 }}
                        </span>
                    </div>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m9 5.197v1" />
                        </svg>
                        {{ project.members_count || 0 }}
                    </span>
                </div>
            </Link>
        </div>

        <div v-if="projects.length === 0" class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No projects yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by creating your first project.</p>
            <Link
                href="/projects/create"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"
            >
                Create Project
            </Link>
        </div>
    </MainLayout>
</template>
