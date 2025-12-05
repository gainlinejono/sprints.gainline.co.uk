<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Story, Task } from '@/Types';

interface Props {
    project: Project;
    story: Story;
}

const props = defineProps<Props>();

const showTaskModal = ref(false);
const showTimeModal = ref(false);
const selectedTask = ref<Task | null>(null);

const taskForm = useForm({
    title: '',
    description: '',
    estimated_hours: 0,
    assignee_id: null as number | null,
});

const timeForm = useForm({
    hours: 0.5,
    description: '',
    logged_at: new Date().toISOString().split('T')[0],
});

const commentForm = useForm({
    content: '',
});

const createTask = () => {
    taskForm.post(`/projects/${props.project.id}/stories/${props.story.id}/tasks`, {
        onSuccess: () => {
            showTaskModal.value = false;
            taskForm.reset();
        },
    });
};

const updateTaskStatus = (task: Task, status: string) => {
    router.patch(`/projects/${props.project.id}/stories/${props.story.id}/tasks/${task.id}/status`, {
        status,
    });
};

const openTimeLog = (task: Task) => {
    selectedTask.value = task;
    showTimeModal.value = true;
};

const logTime = () => {
    if (!selectedTask.value) return;
    timeForm.post(`/projects/${props.project.id}/stories/${props.story.id}/tasks/${selectedTask.value.id}/log-time`, {
        onSuccess: () => {
            showTimeModal.value = false;
            timeForm.reset();
            selectedTask.value = null;
        },
    });
};

const addComment = () => {
    commentForm.post(`/projects/${props.project.id}/stories/${props.story.id}/comments`, {
        onSuccess: () => {
            commentForm.reset();
        },
    });
};

const statusColors = {
    backlog: 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
    todo: 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300',
    in_progress: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300',
    review: 'bg-purple-100 text-purple-700 dark:bg-purple-900 dark:text-purple-300',
    done: 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300',
};

const priorityColors = {
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-600',
    high: 'bg-orange-100 text-orange-600',
    critical: 'bg-red-100 text-red-600',
};

const taskStatusColors = {
    todo: 'bg-gray-200 dark:bg-gray-700',
    in_progress: 'bg-yellow-200 dark:bg-yellow-900',
    done: 'bg-green-200 dark:bg-green-900',
};
</script>

<template>
    <Head :title="story.story_key" />

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
                <span class="text-xl font-bold text-indigo-600 dark:text-indigo-400">{{ story.story_key }}</span>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Story Header -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', statusColors[story.status]]">
                                {{ story.status.replace('_', ' ') }}
                            </span>
                            <span :class="['px-3 py-1 text-sm font-medium rounded-full', priorityColors[story.priority]]">
                                {{ story.priority }}
                            </span>
                        </div>
                        <Link
                            :href="`/projects/${project.id}/stories/${story.id}/edit`"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </Link>
                    </div>

                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">{{ story.title }}</h1>

                    <div v-if="story.description" class="prose dark:prose-invert max-w-none mb-6">
                        <p class="text-gray-600 dark:text-gray-300 whitespace-pre-wrap">{{ story.description }}</p>
                    </div>

                    <div v-if="story.acceptance_criteria" class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Acceptance Criteria</h3>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                            <pre class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-wrap font-mono">{{ story.acceptance_criteria }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Tasks -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Tasks ({{ story.completed_tasks_count }}/{{ story.tasks_count }})
                        </h2>
                        <button
                            @click="showTaskModal = true"
                            class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline"
                        >
                            + Add Task
                        </button>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="task in story.tasks"
                            :key="task.id"
                            :class="['p-4', taskStatusColors[task.status]]"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex items-start space-x-3">
                                    <button
                                        @click="updateTaskStatus(task, task.status === 'done' ? 'todo' : 'done')"
                                        :class="[
                                            'mt-0.5 w-5 h-5 rounded border-2 flex items-center justify-center',
                                            task.status === 'done'
                                                ? 'bg-green-500 border-green-500 text-white'
                                                : 'border-gray-300 dark:border-gray-600 hover:border-green-500'
                                        ]"
                                    >
                                        <svg v-if="task.status === 'done'" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div>
                                        <p :class="['text-sm font-medium', task.status === 'done' ? 'text-gray-500 line-through' : 'text-gray-900 dark:text-white']">
                                            {{ task.title }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            {{ task.logged_hours }}h / {{ task.estimated_hours }}h
                                        </p>
                                    </div>
                                </div>
                                <button
                                    @click="openTimeLog(task)"
                                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    Log Time
                                </button>
                            </div>
                        </div>
                        <div v-if="!story.tasks?.length" class="p-8 text-center text-gray-500 dark:text-gray-400">
                            No tasks yet
                        </div>
                    </div>
                </div>

                <!-- Comments -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Comments</h2>
                    </div>
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div
                            v-for="comment in story.comments"
                            :key="comment.id"
                            class="p-4"
                        >
                            <div class="flex items-start space-x-3">
                                <img
                                    v-if="comment.user?.avatar"
                                    :src="comment.user.avatar"
                                    :alt="comment.user.name"
                                    class="w-8 h-8 rounded-full"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ comment.user?.name }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ new Date(comment.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ comment.content }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                        <form @submit.prevent="addComment" class="flex space-x-3">
                            <input
                                v-model="commentForm.content"
                                type="text"
                                class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Add a comment..."
                            />
                            <button
                                type="submit"
                                :disabled="!commentForm.content || commentForm.processing"
                                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50"
                            >
                                Post
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Details -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Estimated Hours</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ story.estimated_hours }}h</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Logged Hours</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ story.logged_hours }}h</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Remaining</dt>
                            <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ story.remaining_hours }}h</dd>
                        </div>
                        <div v-if="story.epic">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Epic</dt>
                            <dd>
                                <Link
                                    :href="`/projects/${project.id}/epics/${story.epic.id}`"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    {{ story.epic.name }}
                                </Link>
                            </dd>
                        </div>
                        <div v-if="story.sprint">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">Sprint</dt>
                            <dd>
                                <Link
                                    :href="`/projects/${project.id}/sprints/${story.sprint.id}`"
                                    class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline"
                                >
                                    {{ story.sprint.name }}
                                </Link>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- People -->
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">People</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 mb-2">Assignee</dt>
                            <dd v-if="story.assignee" class="flex items-center space-x-2">
                                <img
                                    v-if="story.assignee.avatar"
                                    :src="story.assignee.avatar"
                                    :alt="story.assignee.name"
                                    class="w-6 h-6 rounded-full"
                                />
                                <span class="text-sm text-gray-900 dark:text-white">{{ story.assignee.name }}</span>
                            </dd>
                            <dd v-else class="text-sm text-gray-500 dark:text-gray-400">Unassigned</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500 dark:text-gray-400 mb-2">Reporter</dt>
                            <dd class="flex items-center space-x-2">
                                <img
                                    v-if="story.reporter?.avatar"
                                    :src="story.reporter.avatar"
                                    :alt="story.reporter?.name"
                                    class="w-6 h-6 rounded-full"
                                />
                                <span class="text-sm text-gray-900 dark:text-white">{{ story.reporter?.name }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Task Modal -->
        <div v-if="showTaskModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showTaskModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Task</h3>
                    <form @submit.prevent="createTask">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Title</label>
                            <input
                                v-model="taskForm.title"
                                type="text"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estimated Hours</label>
                            <input
                                v-model.number="taskForm.estimated_hours"
                                type="number"
                                step="0.5"
                                min="0"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showTaskModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" :disabled="taskForm.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Time Log Modal -->
        <div v-if="showTimeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/50" @click="showTimeModal = false"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Log Time</h3>
                    <form @submit.prevent="logTime">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Hours</label>
                            <input
                                v-model.number="timeForm.hours"
                                type="number"
                                step="0.25"
                                min="0.25"
                                max="24"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                required
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date</label>
                            <input
                                v-model="timeForm.logged_at"
                                type="date"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                            <textarea
                                v-model="timeForm.description"
                                rows="2"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                            ></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" @click="showTimeModal = false" class="px-4 py-2 text-gray-700 dark:text-gray-300">Cancel</button>
                            <button type="submit" :disabled="timeForm.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-lg">Log</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
