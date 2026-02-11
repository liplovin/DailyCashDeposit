<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const roleLabel = computed(() => {
    const roles = {
        admin: 'Administrator',
        treasury: 'Treasury',
        accounting: 'Accounting'
    };
    return roles[user.value.role] || 'User';
});
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Dashboard
                </h2>
                <span class="rounded-full bg-blue-100 px-4 py-1 text-sm font-medium text-blue-800">
                    {{ roleLabel }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Welcome, {{ user.name }}!
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            You are logged in as a {{ roleLabel }}.
                        </p>
                    </div>

                    <div class="px-4 py-5 sm:p-6">
                        <!-- Admin Dashboard -->
                        <div v-if="user.role === 'admin'" class="space-y-6">
                            <div class="rounded-md bg-blue-50 p-4">
                                <h4 class="text-lg font-medium text-blue-900">Admin Controls</h4>
                                <p class="mt-2 text-sm text-blue-800">
                                    You have access to all system features including user management, reports, and settings.
                                </p>
                                <div class="mt-4 grid grid-cols-3 gap-4">
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">User Management</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">System Reports</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Settings</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Treasury Dashboard -->
                        <div v-if="user.role === 'treasury'" class="space-y-6">
                            <div class="rounded-md bg-green-50 p-4">
                                <h4 class="text-lg font-medium text-green-900">Treasury Operations</h4>
                                <p class="mt-2 text-sm text-green-800">
                                    Manage deposits, transactions, and financial operations.
                                </p>
                                <div class="mt-4 grid grid-cols-3 gap-4">
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Daily Deposits</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Transactions</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Treasury Reports</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Accounting Dashboard -->
                        <div v-if="user.role === 'accounting'" class="space-y-6">
                            <div class="rounded-md bg-purple-50 p-4">
                                <h4 class="text-lg font-medium text-purple-900">Accounting Operations</h4>
                                <p class="mt-2 text-sm text-purple-800">
                                    View and manage accounting records and financial statements.
                                </p>
                                <div class="mt-4 grid grid-cols-3 gap-4">
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Financial Records</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Account Analysis</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                    <div class="rounded-lg bg-white p-4 text-center shadow">
                                        <p class="text-gray-600">Audit Reports</p>
                                        <p class="mt-2 text-2xl font-bold text-gray-900">→</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
