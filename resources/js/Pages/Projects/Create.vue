<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

const form = useForm({
    name: '',
    description: '',
    key: '',
    color: '#6366f1',
});

const colors = [
    '#6366f1', '#8b5cf6', '#ec4899', '#ef4444',
    '#f97316', '#eab308', '#22c55e', '#14b8a6',
    '#06b6d4', '#3b82f6',
];

const submit = () => {
    form.post('/projects');
};

const generateKey = () => {
    if (form.name && !form.key) {
        form.key = form.name
            .split(' ')
            .map(word => word.charAt(0))
            .join('')
            .toUpperCase()
            .substring(0, 4);
    }
};
</script>

<template>
    <Head title="Create Project" />

    <MainLayout>
        <template #header>
            <div class="flex items-center space-x-4">
                <Link href="/projects" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Project</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <!-- Name -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Project Name
                    </label>
                    <input
                        v-model="form.name"
                        @blur="generateKey"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="My Awesome Project"
                        required
                    />
                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                </div>

                <!-- Key -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Project Key
                    </label>
                    <input
                        v-model="form.key"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent uppercase"
                        placeholder="PROJ"
                        maxlength="10"
                        required
                    />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used for story IDs (e.g., PROJ-123)</p>
                    <p v-if="form.errors.key" class="mt-1 text-sm text-red-500">{{ form.errors.key }}</p>
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Brief description of the project..."
                    />
                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-500">{{ form.errors.description }}</p>
                </div>

                <!-- Color -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Project Color
                    </label>
                    <div class="flex items-center space-x-3">
                        <button
                            v-for="color in colors"
                            :key="color"
                            type="button"
                            @click="form.color = color"
                            :class="[
                                'w-8 h-8 rounded-lg transition-transform',
                                form.color === color ? 'ring-2 ring-offset-2 ring-indigo-500 scale-110' : 'hover:scale-110'
                            ]"
                            :style="{ backgroundColor: color }"
                        />
                        <input
                            v-model="form.color"
                            type="color"
                            class="w-8 h-8 rounded-lg cursor-pointer"
                        />
                    </div>
                    <p v-if="form.errors.color" class="mt-1 text-sm text-red-500">{{ form.errors.color }}</p>
                </div>

                <!-- Preview -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Preview</p>
                    <div class="flex items-center space-x-4">
                        <div
                            class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold"
                            :style="{ backgroundColor: form.color }"
                        >
                            {{ form.key?.substring(0, 2) || '??' }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ form.name || 'Project Name' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ form.key || 'KEY' }}-1</p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4">
                    <Link
                        href="/projects"
                        class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                    >
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Project' }}
                    </button>
                </div>
            </form>
        </div>
    </MainLayout>
</template>
