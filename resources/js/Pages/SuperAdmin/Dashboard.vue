<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Clock, Rocket, Bell } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Super Admin Dashboard" />
    <SuperadminLayout>
        <div class="min-h-full flex flex-col items-center justify-center py-16">
            <!-- Coming Soon Card -->
            <div class="w-full max-w-2xl">
                <!-- Top accent bar -->
                <div class="h-2 bg-gradient-to-r from-yellow-400 via-yellow-500 to-amber-500 rounded-t-2xl"></div>

                <div class="bg-white rounded-b-2xl shadow-xl border border-gray-100 px-10 py-14 text-center">
                    <!-- Icon -->
                    <div class="flex justify-center mb-8">
                        <div class="relative">
                            <div class="w-24 h-24 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                                <Rocket class="h-12 w-12 text-white" />
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-gradient-to-br from-slate-600 to-slate-800 rounded-full flex items-center justify-center shadow">
                                <Clock class="h-4 w-4 text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Title -->
                    <h1 class="text-5xl font-black text-gray-900 tracking-tight mb-3">Coming Soon</h1>
                    <div class="w-16 h-1.5 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full mx-auto mb-6"></div>

                    <!-- Subtitle -->
                    <p class="text-xl font-semibold text-gray-700 mb-3">
                        Super Admin Panel
                    </p>
                    <p class="text-gray-500 text-base leading-relaxed max-w-md mx-auto mb-10">
                        We're building something powerful for you, <span class="font-semibold text-gray-700">{{ user.name }}</span>.
                        The Super Admin dashboard is currently under development and will be available soon.
                    </p>

                    <!-- Status pills -->
                    <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm font-semibold rounded-full">
                            <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                            In Development
                        </span>
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-full">
                            <Bell class="h-3.5 w-3.5" />
                            You'll be notified
                        </span>
                    </div>

                    <!-- Feature preview list -->
                    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 text-left">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">What's coming</p>
                        <ul class="space-y-3">
                            <li v-for="feature in [
                                'Full system oversight and control',
                                'Advanced user & role management',
                                'System-wide audit logs',
                                'Configuration & settings panel',
                                'Analytics and reporting suite',
                            ]" :key="feature" class="flex items-center gap-3">
                                <div class="w-5 h-5 rounded-full bg-yellow-100 border-2 border-yellow-300 flex items-center justify-center flex-shrink-0">
                                    <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></div>
                                </div>
                                <span class="text-sm text-gray-600 font-medium">{{ feature }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </SuperadminLayout>
</template>
