<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Home, Wallet, BarChart3, LogOut } from 'lucide-vue-next';

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
        name: 'Deposits',
        component: Wallet,
    },
    {
        name: 'Reports',
        component: BarChart3,
    }
];
</script>

<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Clean Modern Sidebar -->
        <div class="w-64 bg-white shadow-sm flex flex-col h-screen">
            <!-- Header - Clean Branding -->
            <div class="px-6 py-4">
                <div class="flex items-center space-x-3">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center shadow-md">
                            <img src="/logoonly.png" alt="Daily Deposit Logo" class="h-6 w-6">
                        </div>
                    </div>
                    <div>
                        <h1 class="text-base font-black text-gray-900 tracking-tight">Daily Deposit</h1>
                        <p class="text-xs text-gray-500 font-medium">Treasury Management</p>
                    </div>
                </div>
            </div>

            <!-- Navigation - Minimal & Clean -->
            <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
                <p class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Main Menu</p>
                <div v-for="(item, index) in menuItems" :key="index">
                    <button
                        @click="handleMenuClick(item.name)"
                        :class="[
                            'w-full flex items-center space-x-3 px-4 py-2.5 rounded-lg transition-all duration-200 text-sm font-medium group relative',
                            item.active
                                ? 'bg-yellow-50 text-yellow-700 font-semibold'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                    >
                        <component :is="item.component" :class="[
                            'h-5 w-5 flex-shrink-0 transition-all duration-200',
                            item.active ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-600'
                        ]" />
                        <span class="flex-1 text-left">{{ item.name }}</span>
                        <div v-if="item.active" class="w-1 h-1 rounded-full bg-yellow-600 flex-shrink-0"></div>
                    </button>
                </div>
            </nav>

            <!-- Divider -->
            <div class="px-4">
                <div class="h-px bg-gray-100"></div>
            </div>

            <!-- User Profile Section -->
            <div class="px-4 py-6 space-y-4">
                <!-- User Info Card -->
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 border border-yellow-100 rounded-xl p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center flex-shrink-0 text-white font-bold text-sm shadow-md">
                            {{ user.name.split(' ').map(n => n[0]).join('') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-600 font-medium">Treasury Officer</p>
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <button
                    @click="handleMenuClick('Logout')"
                    class="w-full flex items-center justify-center space-x-2 px-4 py-2.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 text-sm font-semibold border border-red-200"
                >
                    <LogOut class="h-4 w-4" />
                    <span>Logout</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-auto p-8">
                <slot />
            </div>
        </div>
    </div>
</template>
