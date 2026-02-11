<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import { Clock } from 'lucide-vue-next';
</script>

<template>
    <TreasuryLayout>
        <div class="max-w-2xl mx-auto">
            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 shadow-lg">
                <!-- Icon -->
                <div class="flex justify-center mb-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                        <Clock class="h-10 w-10 text-white" />
                    </div>
                </div>

                <!-- Content -->
                <div class="text-center">
                    <h1 class="text-4xl font-black text-gray-900 mb-3">Coming Soon</h1>
                    <p class="text-lg text-gray-600 mb-2">Other Investment Module</p>
                    <p class="text-gray-500 mb-8 leading-relaxed">
                        We're preparing an exciting new module for managing other investments. Stay tuned for updates!
                    </p>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 gap-4 mt-8 pt-8 border-t-2 border-yellow-200">
                        <div class="text-center">
                            <p class="text-2xl font-black text-yellow-600">0</p>
                            <p class="text-xs text-gray-600 mt-1">Assets</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-yellow-600">$0</p>
                            <p class="text-xs text-gray-600 mt-1">Total Value</p>
                        </div>
                        <div class="text-center">
                            <p class="text-2xl font-black text-yellow-600">0</p>
                            <p class="text-xs text-gray-600 mt-1">Transactions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-8 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">What to expect:</h2>
                <ul class="space-y-2 text-gray-600">
                    <li class="flex items-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-3 flex-shrink-0"></span>
                        <span>Manage alternative investment portfolios</span>
                    </li>
                    <li class="flex items-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-3 flex-shrink-0"></span>
                        <span>Track performance and returns</span>
                    </li>
                    <li class="flex items-start">
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-500 mt-2 mr-3 flex-shrink-0"></span>
                        <span>Generate detailed reports and analytics</span>
                    </li>
                </ul>
            </div>
        </div>
    </TreasuryLayout>
</template>
