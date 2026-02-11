<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Treasury Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Treasury Dashboard
                </h2>
                <span class="rounded-full bg-green-100 px-4 py-1 text-sm font-medium text-green-800">
                    Treasury Officer
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Welcome Section -->
                <div class="mb-8 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-100 px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-green-900">
                            Welcome, {{ user.name }}!
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-green-700">
                            Manage treasury operations and daily deposits.
                        </p>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Total Deposits Today</p>
                        <p class="mt-2 text-3xl font-bold text-green-600">$0.00</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Pending Deposits</p>
                        <p class="mt-2 text-3xl font-bold text-orange-600">0</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Yesterday Total</p>
                        <p class="mt-2 text-3xl font-bold text-blue-600">$0.00</p>
                    </div>
                    <div class="rounded-lg bg-white p-6 shadow">
                        <p class="text-sm font-medium text-gray-600">Monthly Total</p>
                        <p class="mt-2 text-3xl font-bold text-indigo-600">$0.00</p>
                    </div>
                </div>

                <!-- Treasury Operations -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white px-4 py-5 sm:px-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">
                            Treasury Operations
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-6 p-6 md:grid-cols-2 lg:grid-cols-3">
                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-green-600 text-2xl">💰</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Daily Deposits</h4>
                                    <p class="mt-1 text-sm text-gray-500">Record and manage deposits</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-blue-600 text-2xl">💳</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Transactions</h4>
                                    <p class="mt-1 text-sm text-gray-500">View transaction history</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-purple-600 text-2xl">📊</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Treasury Reports</h4>
                                    <p class="mt-1 text-sm text-gray-500">Generate treasury reports</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-orange-600 text-2xl">📝</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Reconciliation</h4>
                                    <p class="mt-1 text-sm text-gray-500">Reconcile accounts</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-red-600 text-2xl">📥</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Withdrawal Requests</h4>
                                    <p class="mt-1 text-sm text-gray-500">Process withdrawal requests</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-gray-200 p-6 hover:shadow-lg transition-shadow cursor-pointer">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="text-indigo-600 text-2xl">🔍</div>
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-900">Audit Trail</h4>
                                    <p class="mt-1 text-sm text-gray-500">View audit trail</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
