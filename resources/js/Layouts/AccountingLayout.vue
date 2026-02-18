<script setup>
import { computed, ref } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { Home, LogOut, Menu, Wallet } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
const sidebarOpen = ref(true);

const currentRoute = computed(() => page.url);

const isRouteActive = (routeName) => {
    const routeList = {
        'dashboard': '/dashboard',
        'accounting.operating-accounts': '/accounting/operating-accounts'
    };
    
    const routePath = routeList[routeName];
    const currentPath = currentRoute.value;
    
    return currentPath === routePath || currentPath.startsWith(routePath + '/');
};

const handleMenuClick = (item) => {
    if (item === 'Logout') {
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
    } else if (item.route) {
        const routeUrls = {
            'dashboard': '/dashboard',
            'accounting.operating-accounts': '/accounting/operating-accounts'
        };
        router.get(routeUrls[item.route]);
    }
};

const menuItems = [
    {
        name: 'Dashboard',
        component: Home,
        route: 'dashboard'
    },
    {
        name: 'Operating Accounts',
        component: Wallet,
        route: 'accounting.operating-accounts'
    }
];
</script>

<template>
    <div class="flex h-screen bg-gray-50">
        <!-- Clean Modern Sidebar -->
        <div :class="['bg-white shadow-sm flex flex-col h-screen transition-all duration-300', sidebarOpen ? 'w-64' : 'w-20']">
            <!-- Header - Clean Branding -->
            <div class="px-4 py-4 border-b border-gray-100">
                <div class="flex items-start gap-3">
                    <!-- Logo and Text - Left Aligned -->
                    <button
                        @click="sidebarOpen = true"
                        class="flex items-start gap-3 transition-all duration-200 hover:opacity-80"
                        :title="!sidebarOpen ? 'Expand sidebar' : ''"
                    >
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-xl flex items-center justify-center shadow-md">
                                <img src="/logoonly.png" alt="Daily Deposit Logo" class="h-6 w-6">
                            </div>
                        </div>
                        
                        <div v-if="sidebarOpen" class="transition-opacity duration-300 min-w-0 text-left">
                            <h1 class="text-sm font-black text-gray-900 tracking-tight leading-tight truncate">Daily Deposit</h1>
                            <p class="text-xs text-gray-500 font-medium truncate">Accounting Management</p>
                        </div>
                    </button>

                    <!-- Menu Button - Only visible when expanded, minimizes sidebar -->
                    <button
                        v-if="sidebarOpen"
                        @click="sidebarOpen = false"
                        class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition-all duration-200 flex-shrink-0 ml-auto"
                        title="Minimize sidebar"
                    >
                        <Menu class="h-5 w-5" />
                    </button>
                </div>
            </div>

            <!-- Navigation - Minimal & Clean -->
            <nav class="flex-1 px-2 py-8 space-y-2 overflow-y-auto">
                <p v-if="sidebarOpen" class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4 transition-opacity duration-300">Main Menu</p>
                <div v-for="(item, index) in menuItems" :key="index">
                    <button
                        @click="handleMenuClick(item)"
                        :class="[
                            'w-full flex items-center rounded-lg transition-all duration-200 text-sm font-medium group relative',
                            sidebarOpen ? 'space-x-3 px-4 py-2.5 justify-start' : 'justify-center p-2.5',
                            isRouteActive(item.route)
                                ? 'bg-yellow-50 text-yellow-700 font-semibold'
                                : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'
                        ]"
                        :title="!sidebarOpen ? item.name : ''"
                    >
                        <component :is="item.component" :class="[
                            'flex-shrink-0 transition-all duration-200',
                            sidebarOpen ? 'h-5 w-5' : 'h-5 w-5',
                            isRouteActive(item.route) ? 'text-yellow-600' : 'text-gray-400 group-hover:text-gray-600'
                        ]" />
                        <span v-if="sidebarOpen" class="flex-1 text-left transition-opacity duration-300">{{ item.name }}</span>
                        <div v-if="isRouteActive(item.route) && sidebarOpen" class="w-1 h-1 rounded-full bg-yellow-600 flex-shrink-0"></div>
                    </button>
                </div>
            </nav>

            <!-- Divider -->
            <div class="px-4">
                <div class="h-px bg-gray-100"></div>
            </div>

            <!-- User Profile Section -->
            <div :class="['px-4 py-6 space-y-4', sidebarOpen ? '' : 'px-2']">
                <!-- User Info Card -->
                <div v-if="sidebarOpen" class="bg-gradient-to-br from-yellow-50 to-amber-50 border border-yellow-100 rounded-xl p-4 transition-opacity duration-300">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-11 h-11 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center flex-shrink-0 text-white font-bold text-sm shadow-md">
                            {{ user.name.split(' ').map(n => n[0]).join('') }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</p>
                            <p class="text-xs text-gray-600 font-medium">Accounting Officer</p>
                        </div>
                    </div>
                </div>

                <!-- Logout Button -->
                <button
                    @click="handleMenuClick('Logout')"
                    :class="['flex items-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition-all duration-200 text-sm font-semibold border border-red-200', sidebarOpen ? 'w-full justify-center space-x-2 px-4 py-2.5' : 'w-full justify-center p-2.5']"
                    :title="!sidebarOpen ? 'Logout' : ''"
                >
                    <LogOut class="h-4 w-4" />
                    <span v-if="sidebarOpen" class="transition-opacity duration-300">Logout</span>
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div class="flex-1 overflow-auto p-8">
                <slot />
            </div>
            
            <!-- Footer -->
            <div class="border-t border-gray-200 bg-gray-50 px-8 py-4">
                <p class="text-center text-sm text-gray-600">
                    © {{ new Date().getFullYear() }} Crafted by John Philip Pera & Robert Janipin
                </p>
            </div>
        </div>
    </div>
</template>
