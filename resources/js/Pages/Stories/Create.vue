<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import type { Project, Epic, Sprint, User } from '@/Types';

interface Props {
    project: Project;
    epicId?: number;
    sprintId?: number;
}

const props = defineProps<Props>();

const form = useForm({
    title: '',
    description: '',
    acceptance_criteria: '',
    estimated_hours: 0,
    priority: 'medium' as const,
    epic_id: props.epicId || null,
    sprint_id: props.sprintId || null,
    assignee_id: null as number | null,
});

const priorities = [
    { value: 'low', label: 'Low', color: 'bg-gray-100 text-gray-600' },
    { value: 'medium', label: 'Medium', color: 'bg-blue-100 text-blue-600' },
    { value: 'high', label: 'High', color: 'bg-orange-100 text-orange-600' },
    { value: 'critical', label: 'Critical', color: 'bg-red-100 text-red-600' },
];

const submit = () => {
    form.post(`/projects/${props.project.id}/stories`);
};
</script>

<template>
    <Head title="Create Story" />

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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Story</h1>
            </div>
        </template>

        <div class="max-w-3xl">
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <!-- Title -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="form.title"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="As a user, I want to..."
                        required
                    />
                    <p v-if="form.errors.title" class="mt-1 text-sm text-red-500">{{ form.errors.title }}</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Detailed description of the story..."
                    />
                </div>

                <!-- Acceptance Criteria -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Acceptance Criteria
                    </label>
                    <textarea
                        v-model="form.acceptance_criteria"
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                        placeholder="- [ ] Criteria 1&#10;- [ ] Criteria 2&#10;- [ ] Criteria 3"
                    />
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Estimated Hours -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Estimated Hours <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model.number="form.estimated_hours"
                            type="number"
                            step="0.5"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                            required
                        />
                        <p v-if="form.errors.estimated_hours" class="mt-1 text-sm text-red-500">{{ form.errors.estimated_hours }}</p>
                    </div>

                    <!-- Priority -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Priority
                        </label>
                        <div class="flex space-x-2">
                            <button
                                v-for="priority in priorities"
                                :key="priority.value"
                                type="button"
                                @click="form.priority = priority.value"
                                :class="[
                                    'flex-1 px-3 py-2 text-sm font-medium rounded-lg border transition-all',
                                    form.priority === priority.value
                                        ? `${priority.color} border-transparent ring-2 ring-offset-2 ring-indigo-500`
                                        : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
                                ]"
                            >
                                {{ priority.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6 mb-6">
                    <!-- Epic -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Epic
                        </label>
                        <select
                            v-model="form.epic_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option :value="null">No Epic</option>
                            <option v-for="epic in project.epics" :key="epic.id" :value="epic.id">
                                {{ epic.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Sprint -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Sprint
                        </label>
                        <select
                            v-model="form.sprint_id"
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        >
                            <option :value="null">Backlog</option>
                            <option
                                v-for="sprint in project.sprints?.filter(s => s.status !== 'completed')"
                                :key="sprint.id"
                                :value="sprint.id"
                            >
                                {{ sprint.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Assignee -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assignee
                    </label>
                    <select
                        v-model="form.assignee_id"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                    >
                        <option :value="null">Unassigned</option>
                        <option v-for="member in project.members" :key="member.id" :value="member.id">
                            {{ member.name }}
                        </option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <Link
                        :href="`/projects/${project.id}`"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Story' }}
                    </button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
