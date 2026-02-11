<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Home, FileText, CheckCircle, BarChart3, LogOut } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
const sidebarOpen = ref(true);

const handleMenuClick = (itemName) => {
    if (itemName === 'Logout') {
        Swal.fire({
            title: 'Are you sure?',
            text: 'You will be logged out of the system.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#D4A017',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, logout'
        }).then((result) => {
            if (result.isConfirmed) {
                router.post('/logout');
            }
        });
    }
};

const menuItems = [
    {
        name: 'Dashboard',
        component: Home,
        active: true
    },
    {
        name: 'Account Records',
        component: FileText,
    },
    {
        name: 'Verify Deposits',
        component: CheckCircle,
    },
    {
        name: 'Financial Statements',
        component: BarChart3,
    }
];
</script>

<template>
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <div class="w-64 bg-gradient-to-b from-yellow-600 to-yellow-700 text-white shadow-xl overflow-y-auto">
            <!-- Logo Area -->
            <div class="p-6 border-b border-yellow-500">
                <div class="flex items-center space-x-3">
                    <div class="bg-white/20 rounded-lg p-2">
                        <img src="/logoonly.png" alt="Daily Deposit Logo" class="h-6 w-6">
                    </div>
                    <div>
                        <h1 class="text-lg font-bold">Daily Deposit</h1>
                        <p class="text-xs text-yellow-100">Management System</p>
                    </div>
                </div>
            </div>

            <!-- User Info -->
            <div class="px-6 py-4 border-b border-yellow-500">
                <p class="text-sm text-yellow-100">Logged in as</p>
                <p class="font-semibold text-white">{{ user.name }}</p>
                <p class="text-xs text-yellow-100">Accounting Officer</p>
            </div>

            <!-- Navigation Menu -->
            <nav class="mt-6 px-3 flex-1">
                <div v-for="(item, index) in menuItems" :key="index" class="mb-2">
                    <button
                        @click="handleMenuClick(item.name)"
                        :class="[
                            'w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors duration-200',
                            item.active
                                ? 'bg-white/20 text-white'
                                : 'text-yellow-100 hover:bg-white/10'
                        ]"
                    >
                        <component :is="item.component" class="h-5 w-5 flex-shrink-0" />
                        <span class="font-medium">{{ item.name }}</span>
                    </button>
                </div>
            </nav>

            <!-- Logout Button -->
            <div class="px-3 pb-6 border-t border-yellow-500 pt-4">
                <button
                    @click="handleMenuClick('Logout')"
                    class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-200 hover:bg-red-500/20 transition-colors text-sm font-medium"
                >
                    <LogOut class="h-5 w-5 flex-shrink-0" />
                    <span>Logout</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Header -->
            <div class="bg-white border-b border-gray-200 px-6 py-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Accounting Dashboard</h2>
                        <p class="text-sm text-gray-500 mt-1">View and manage accounting records and financial statements.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="rounded-full bg-yellow-100 px-4 py-2 text-sm font-medium text-yellow-800">
                            Accounting
                        </span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="flex-1 overflow-auto p-6">
                <!-- Welcome Card -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Welcome, {{ user.name }}!
                    </h3>
                    <p class="text-gray-600">
                        Manage accounting records, verify transactions, and generate financial statements. Ensure all records are accurate and compliant.
                    </p>
                </div>

                <!-- Accounting Operations Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Account Records Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">Account Records</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">View and manage detailed account records and ledgers.</p>
                        </div>
                    </div>

                    <!-- Verify Deposits Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">Verify Deposits</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Confirm and verify all deposit records for accuracy.</p>
                        </div>
                    </div>

                    <!-- Financial Statements Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">Financial Statements</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Generate and review comprehensive financial statements.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
