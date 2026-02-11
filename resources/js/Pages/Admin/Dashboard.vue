<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Admin Dashboard
                </h2>
                <span class="rounded-full bg-blue-100 px-4 py-1 text-sm font-medium text-blue-800">
                    Administrator
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-blue-50 to-blue-100 px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-blue-900">
                            Welcome, {{ user.name }}!
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-blue-700">
                            You have full access to all system features and controls.
                        </p>
                    </div>
                </div>

                <!-- Statistics Section -->
                <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">3</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Active Sessions</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">1</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">System Health</p>
                        <p class="mt-2 text-3xl font-bold text-emerald-600">100%</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Pending Tasks</p>
                        <p class="mt-2 text-3xl font-bold text-orange-600">0</p>
                    </div>
                </div>

                <!-- Admin Controls -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Administrative Controls
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-blue-600 text-2xl">👥</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">User Management</h4>
                                    <p class="mt-1 text-sm text-gray-500">Create, edit, and manage users</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-green-600 text-2xl">📊</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">System Reports</h4>
                                    <p class="mt-1 text-sm text-gray-500">View comprehensive reports</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-purple-600 text-2xl">⚙️</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">System Settings</h4>
                                    <p class="mt-1 text-sm text-gray-500">Configure system parameters</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-red-600 text-2xl">🔐</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Security</h4>
                                    <p class="mt-1 text-sm text-gray-500">Manage security settings</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-yellow-600 text-2xl">📝</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Audit Logs</h4>
                                    <p class="mt-1 text-sm text-gray-500">View system activity logs</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-indigo-600 text-2xl">📲</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Backup & Restore</h4>
                                    <p class="mt-1 text-sm text-gray-500">Manage system backups</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
