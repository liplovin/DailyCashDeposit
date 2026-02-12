<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { FileText, CheckCircle, BarChart3 } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Accounting Dashboard" />
    <AccountingLayout>
        <!-- Welcome Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Welcome, {{ user.name }}!
            </h1>
            <p class="text-gray-600">
                Manage accounting records, verify transactions, and generate financial statements. Ensure all records are accurate and compliant.
            </p>
        </div>

        <!-- Accounting Operations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Account Records Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="h-12 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Account Records</h3>
                        <FileText class="h-5 w-5 text-yellow-600 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-gray-600 text-sm">View and manage detailed account records and ledgers.</p>
                </div>
            </div>

            <!-- Verify Deposits Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="h-12 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Verify Deposits</h3>
                        <CheckCircle class="h-5 w-5 text-yellow-600 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-gray-600 text-sm">Confirm and verify all deposit records for accuracy.</p>
                </div>
            </div>

            <!-- Financial Statements Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-all duration-200 cursor-pointer group">
                <div class="h-12 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">Financial Statements</h3>
                        <BarChart3 class="h-5 w-5 text-yellow-600 group-hover:scale-110 transition-transform" />
                    </div>
                    <p class="text-gray-600 text-sm">Generate and review comprehensive financial statements.</p>
                </div>
            </div>
        </div>
    </AccountingLayout>
</template>
