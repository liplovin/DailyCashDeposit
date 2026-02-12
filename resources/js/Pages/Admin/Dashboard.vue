<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Settings, Users } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div class="space-y-8">
            <!-- Welcome Section -->
            <div>
                <h1 class="text-3xl font-black text-gray-900">Welcome, {{ user.name }}!</h1>
                <p class="text-gray-600 mt-2">Manage your system administration tasks and settings from here.</p>
            </div>

            <!-- Admin Controls Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Management Card -->
                <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="h-16 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">User Management</h3>
                            <Users class="h-6 w-6 text-yellow-600" />
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Manage system users, assign roles, and control access permissions.</p>
                        <button class="w-full px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all">
                            Manage Users
                        </button>
                    </div>
                </div>

                <!-- Settings Card -->
                <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <div class="h-16 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-bold text-gray-900">System Settings</h3>
                            <Settings class="h-6 w-6 text-yellow-600" />
                        </div>
                        <p class="text-gray-600 text-sm mb-4">Configure system preferences, email settings, and security options.</p>
                        <button class="w-full px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all">
                            Go to Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Status Section -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg p-6">
                <h2 class="text-xl font-bold text-gray-900 mb-6">System Overview</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Status Card 1 -->
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                        <p class="text-sm text-blue-600 font-semibold mb-2">Active Users</p>
                        <p class="text-3xl font-black text-blue-900">0</p>
                    </div>

                    <!-- Status Card 2 -->
                    <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-green-600 font-semibold mb-2">System Status</p>
                        <p class="text-lg font-bold text-green-900">✓ Operational</p>
                    </div>

                    <!-- Status Card 3 -->
                    <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
                        <p class="text-sm text-yellow-600 font-semibold mb-2">Last Updated</p>
                        <p class="text-lg font-bold text-yellow-900">Today</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
