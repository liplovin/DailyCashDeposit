<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Home, Users, BarChart3, Settings, LogOut } from 'lucide-vue-next';

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
        name: 'User Management',
        component: Users,
    },
    {
        name: 'System Reports',
        component: BarChart3,
    },
    {
        name: 'Settings',
        component: Settings,
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
                <p class="text-xs text-yellow-100">Administrator</p>
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
                        <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
                        <p class="text-sm text-gray-500 mt-1">Welcome back! Here's what's happening with your system today.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="rounded-full bg-yellow-100 px-4 py-2 text-sm font-medium text-yellow-800">
                            Administrator
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
                        You have full system access as an administrator. Manage users, view reports, and configure system settings from the navigation menu above.
                    </p>
                </div>

                <!-- Admin Controls Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- User Management Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">User Management</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Manage system users, assign roles, and control access permissions.</p>
                        </div>
                    </div>

                    <!-- System Reports Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">System Reports</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Generate and view comprehensive system analytics and performance reports.</p>
                        </div>
                    </div>

                    <!-- Settings Card -->
                    <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-200 cursor-pointer">
                        <div class="h-16 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-lg font-semibold text-gray-900">Settings</h4>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm">Configure system preferences, email settings, and security options.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
