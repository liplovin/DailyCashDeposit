<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Clock } from 'lucide-vue-next';

const page = usePage();
</script>

<template>
    <AdminLayout>
        <div class="w-full h-full flex flex-col items-center justify-center py-12 px-4">
        <!-- Coming Soon Header -->
        <div class="text-center max-w-md">
            <div class="mb-6">
                <Clock class="h-16 w-16 text-red-600 mx-auto opacity-75 animate-pulse" />
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                Reports Module
            </h1>
            
            <p class="text-lg text-gray-600 mb-8">
                The Reports module is under development and will be available shortly.
            </p>
            
            <div class="inline-block bg-red-50 border border-red-200 rounded-lg px-6 py-4">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold text-red-600">⚠️ Important Module:</span> In Development
                </p>
                <p class="text-xs text-gray-600 mt-2">
                    This module will provide comprehensive financial reports and analytics.
                </p>
            </div>
        </div>
        </div>
    </AdminLayout>
</template>
