<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Clock } from 'lucide-vue-next';

const page = usePage();
</script>

<template>
    <AccountingLayout>
        <div class="w-full h-full flex flex-col items-center justify-center py-12 px-4">
        <!-- Coming Soon Header -->
        <div class="text-center max-w-md">
            <div class="mb-6">
                <Clock class="h-16 w-16 text-yellow-600 mx-auto opacity-75 animate-pulse" />
            </div>
            
            <h1 class="text-4xl font-bold text-gray-900 mb-2">
                Coming Soon
            </h1>
            
            <p class="text-lg text-gray-600 mb-8">
                The Investments module is under development and will be available shortly.
            </p>
            
            <div class="inline-block bg-yellow-50 border border-yellow-200 rounded-lg px-6 py-4">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Current Status:</span> In Development
                </p>
                <p class="text-xs text-gray-600 mt-2">
                    Please check back soon for updates.
                </p>
            </div>
        </div>
        </div>
    </AccountingLayout>
</template>
