<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Sprint, Story } from '@/Types';

interface Props {
    project: Project;
    activeSprint: Sprint | null;
    backlogStories: Story[];
}

const props = defineProps<Props>();

const moveToSprint = (storyId: number, sprintId: number | null) => {
    router.post(`/projects/${props.project.id}/stories/${storyId}/move-to-sprint`, {
        sprint_id: sprintId,
    });
};

const priorityColors = {
    low: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    medium: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300',
    high: 'bg-orange-100 text-orange-600 dark:bg-orange-900 dark:text-orange-300',
    critical: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300',
};
</script>

<template>
    <Head :title="project.name" />

    <MainLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link href="/projects" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ project.name }}</h1>
            </div>
        </template>

        <!-- Navigation Tabs -->
        <div class="flex items-center space-x-1 mb-6 border-b border-gray-200 dark:border-gray-700">
            <Link
                :href="`/projects/${project.id}`"
                class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400"
            >
                Overview
            </Link>
            <Link
                :href="`/projects/${project.id}/sprints`"
                class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            >
                Sprints
            </Link>
            <Link
                :href="`/projects/${project.id}/epics`"
                class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            >
                Epics
            </Link>
            <Link
                :href="`/projects/${project.id}/stories`"
                class="px-4 py-2 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
            >
                Stories
            </Link>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Active Sprint -->
                <div v-if="activeSprint" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ activeSprint.name }}</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ activeSprint.days_remaining }} days remaining</p>
                        </div>
                        <Link
                            :href="`/projects/${project.id}/sprints/${activeSprint.id}/board`"
                            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg"
                        >
                            Open Board
                        </Link>
                    </div>

                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 mb-2">
                        <div
                            class="bg-indigo-600 h-3 rounded-full transition-all"
                            :style="{ width: `${activeSprint.progress_percentage}%` }"
                        />
                    </div>
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                        <span>{{ activeSprint.completed_hours }}h completed</span>
                        <span>{{ activeSprint.progress_percentage }}%</span>
                        <span>{{ activeSprint.remaining_hours }}h remaining</span>
                    </div>
                </div>

                <!-- Backlog -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Backlog</h2>
                        <Link
                            :href="`/projects/${project.id}/stories/create`"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            + Add Story
                        </Link>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="story in backlogStories"
                            :key="story.id"
                            class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                        >
                            <div class="flex items-start justify-between">
                                <Link :href="`/projects/${project.id}/stories/${story.id}`" class="flex-1">
                                    <div class="flex items-center space-x-2 mb-1">
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ story.story_key }}</span>
                                        <span :class="['text-xs px-2 py-0.5 rounded-full', priorityColors[story.priority]]">
                                            {{ story.priority }}
                                        </span>
                                        <span v-if="story.epic" class="text-xs px-2 py-0.5 rounded-full" :style="{ backgroundColor: story.epic.color + '20', color: story.epic.color }">
                                            {{ story.epic.name }}
                                        </span>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ story.title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ story.estimated_hours }}h</p>
                                </Link>
                                <button
                                    v-if="activeSprint"
                                    @click="moveToSprint(story.id, activeSprint.id)"
                                    class="ml-4 px-3 py-1 text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-200 dark:hover:bg-indigo-800"
                                >
                                    Add to Sprint
                                </button>
                            </div>
                        </div>
                        <div v-if="backlogStories.length === 0" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No stories in backlog
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Project Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Project Stats</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Stories</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ project.stories?.length || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Total Hours</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ project.total_hours }}h</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Completed</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ project.completed_hours }}h</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Sprints</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ project.sprints?.length || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Epics</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ project.epics?.length || 0 }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <Link
                            :href="`/projects/${project.id}/sprints/create`"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Create Sprint
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/epics/create`"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                            Create Epic
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/stories/create`"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Create Story
                        </Link>
                        <Link
                            :href="`/projects/${project.id}/edit`"
                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                        >
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Settings
                        </Link>
                    </div>
                </div>

                <!-- Team -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Team</h3>
                        <Link
                            :href="`/projects/${project.id}/members`"
                            class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            Manage
                        </Link>
                    </div>
                    <div class="flex -space-x-2">
                        <div
                            v-for="member in project.members?.slice(0, 5)"
                            :key="member.id"
                            class="relative"
                        >
                            <img
                                v-if="member.avatar"
                                :src="member.avatar"
                                :alt="member.name"
                                :title="member.name"
                                class="w-8 h-8 rounded-full border-2 border-white dark:border-gray-800"
                            />
                            <div
                                v-else
                                :title="member.name"
                                class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-xs font-medium border-2 border-white dark:border-gray-800"
                            >
                                {{ member.name?.charAt(0) }}
                            </div>
                        </div>
                        <div
                            v-if="(project.members?.length || 0) > 5"
                            class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-600 dark:text-gray-300 text-xs font-medium border-2 border-white dark:border-gray-800"
                        >
                            +{{ (project.members?.length || 0) - 5 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
