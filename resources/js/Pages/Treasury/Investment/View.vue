<script setup>
import { X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    investment: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const activeTab = ref('details');

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        activeTab.value = 'details';
    }
});

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const formatDateTime = (datetime) => {
    if (!datetime) return '—';
    return new Date(datetime).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
};

const differenceInDays = (date1, date2) => {
    const d1 = new Date(date1);
    const d2 = new Date(date2);
    const diffTime = Math.abs(d2 - d1);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};
</script>

<template>
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[85vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex items-center justify-between z-10">
                    <h2 class="text-white text-lg font-semibold">Investment History</h2>
                    <button
                        @click="$emit('close')"
                        class="text-white hover:bg-blue-800 rounded-full p-1 transition-colors"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <!-- Tabs -->
                <div class="sticky top-16 bg-white border-b border-gray-200 px-6 z-10">
                    <div class="flex space-x-8">
                        <button
                            @click="activeTab = 'details'"
                            :class="[
                                'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'details'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Details
                        </button>
                        <button
                            @click="activeTab = 'renewals'"
                            :class="[
                                'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'renewals'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Renewal History
                        </button>
                        <button
                            @click="activeTab = 'withdrawals'"
                            :class="[
                                'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'withdrawals'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Withdrawal History
                        </button>
                        <button
                            @click="activeTab = 'balances'"
                            :class="[
                                'py-3 px-1 border-b-2 font-medium text-sm transition-colors',
                                activeTab === 'balances'
                                    ? 'border-blue-600 text-blue-600'
                                    : 'border-transparent text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            Balance History
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <!-- Details Tab -->
                    <div v-if="activeTab === 'details'" class="space-y-6">
                        <div class="grid grid-cols-2 gap-6">
                            <!-- Investment Name -->
                            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                                <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Investment Name</p>
                                <p class="text-lg font-bold text-gray-900">{{ investment.investment_name || '—' }}</p>
                            </div>

                            <!-- Reference Number -->
                            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                                <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Reference Number</p>
                                <p class="text-lg font-bold text-gray-900">{{ investment.reference_number || '—' }}</p>
                            </div>

                            <!-- Acquisition Date -->
                            <div class="bg-green-50 rounded-lg p-6 border border-green-200">
                                <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Acquisition Date</p>
                                <p class="text-lg font-bold text-green-700">{{ formatDate(investment.acquisition_date) }}</p>
                            </div>

                            <!-- Maturity Date -->
                            <div class="bg-blue-100 rounded-lg p-6 border border-blue-300">
                                <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Maturity Date</p>
                                <p class="text-lg font-bold text-blue-700">{{ formatDate(investment.maturity_date) }}</p>
                            </div>
                        </div>

                        <!-- Explanation -->
                        <div class="bg-white rounded-lg p-6 border border-gray-200">
                            <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-3">Explanation</p>
                            <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ investment.explanation?.trim() || '—' }}</p>
                        </div>
                    </div>

                    <!-- Renewal History Tab -->
                    <div v-if="activeTab === 'renewals'">
                        <div v-if="investment?.renewals && investment.renewals.length > 0" class="space-y-4">
                            <div class="relative">
                                <div class="space-y-6">
                                    <div
                                        v-for="(renewal, index) in investment.renewals"
                                        :key="`renewal-${renewal.id}`"
                                        class="relative flex items-start"
                                    >
                                        <div class="absolute left-0 top-2 w-4 h-4 bg-blue-600 rounded-full border-4 border-white"></div>
                                        <div v-if="index < investment.renewals.length - 1" class="absolute left-2 top-6 w-0.5 h-12 bg-blue-200"></div>

                                        <div class="ml-8 flex-1 bg-gray-50 rounded-lg p-4 border border-gray-200">
                                            <div class="grid grid-cols-2 gap-4 mb-3">
                                                <div>
                                                    <p class="text-xs text-gray-500 font-semibold">Previous Maturity Date</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ formatDate(renewal.previous_maturity_date) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-semibold">New Maturity Date</p>
                                                    <p class="text-sm font-medium text-blue-600">{{ formatDate(renewal.new_maturity_date) }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="bg-white rounded-lg p-4 border border-blue-100 mb-3">
                                                <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Explanation</p>
                                                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ renewal.explanation?.trim() || '—' }}</p>
                                            </div>

                                            <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                                                <p class="text-xs text-gray-400">
                                                    Renewed on {{ formatDateTime(renewal.created_at) }}
                                                </p>
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full">
                                                    +{{ differenceInDays(renewal.previous_maturity_date, renewal.new_maturity_date) }} days
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500">No renewal history yet</p>
                        </div>
                    </div>

                    <!-- Withdrawal History Tab -->
                    <div v-if="activeTab === 'withdrawals'">
                        <div v-if="investment?.withdrawals && investment.withdrawals.length > 0" class="space-y-4">
                            <div
                                v-for="withdrawal in investment.withdrawals"
                                :key="`withdrawal-${withdrawal.id}`"
                                class="bg-orange-50 rounded-lg p-5 border border-orange-200 hover:shadow-md transition-shadow"
                            >
                                <div class="mb-5 pb-4 border-b-2 border-orange-300">
                                    <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Withdrawal Transaction</p>
                                    <p class="text-4xl font-bold text-orange-700">₱ {{ formatCurrency(withdrawal.amount) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Withdrawn on {{ formatDateTime(withdrawal.created_at) }}</p>
                                </div>

                                <div class="grid grid-cols-3 gap-3 mb-5 pb-5 border-b border-orange-200">
                                    <div class="bg-white rounded-lg p-4 border border-orange-100">
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-2">Previous Balance</p>
                                        <p class="text-xl font-bold text-gray-800">₱ {{ formatCurrency(investment.beginning_balance + withdrawal.amount) }}</p>
                                    </div>
                                    <div class="bg-orange-100 rounded-lg p-4 border border-orange-300 flex items-center justify-center">
                                        <div class="text-center">
                                            <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Withdrawn</p>
                                            <p class="text-xl font-bold text-orange-700">₱ {{ formatCurrency(withdrawal.amount) }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-4 border border-green-300">
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-2">New Balance</p>
                                        <p class="text-xl font-bold text-green-700">₱ {{ formatCurrency(investment.beginning_balance) }}</p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg p-4 border border-orange-100">
                                    <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Reason / Explanation</p>
                                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ withdrawal.explanation?.trim() || '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500">No withdrawal history yet</p>
                        </div>
                    </div>

                    <!-- Balance History Tab -->
                    <div v-if="activeTab === 'balances'">
                        <div v-if="investment?.balances && investment.balances.length > 0" class="space-y-4">
                            <div
                                v-for="balance in investment.balances"
                                :key="`balance-${balance.id}`"
                                class="bg-green-50 rounded-lg p-5 border border-green-200 hover:shadow-md transition-shadow"
                            >
                                <div class="mb-5 pb-4 border-b-2 border-green-300">
                                    <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Balance Addition</p>
                                    <p class="text-4xl font-bold text-green-700">₱ {{ formatCurrency(balance.amount) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Added on {{ formatDateTime(balance.created_at) }}</p>
                                </div>

                                <div class="grid grid-cols-3 gap-3 mb-5 pb-5 border-b border-green-200">
                                    <div class="bg-white rounded-lg p-4 border border-green-100">
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-2">Previous Balance</p>
                                        <p class="text-xl font-bold text-gray-800">₱ {{ formatCurrency(investment.beginning_balance - balance.amount) }}</p>
                                    </div>
                                    <div class="bg-green-100 rounded-lg p-4 border border-green-300 flex items-center justify-center">
                                        <div class="text-center">
                                            <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Added</p>
                                            <p class="text-xl font-bold text-green-700">₱ {{ formatCurrency(balance.amount) }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-300">
                                        <p class="text-xs text-gray-500 font-bold uppercase tracking-widest mb-2">New Balance</p>
                                        <p class="text-xl font-bold text-blue-700">₱ {{ formatCurrency(investment.beginning_balance) }}</p>
                                    </div>
                                </div>

                                <div class="bg-white rounded-lg p-4 border border-green-100">
                                    <p class="text-xs text-gray-600 font-bold uppercase tracking-widest mb-2">Reason</p>
                                    <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ balance.explanation?.trim() || '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500">No balance addition history yet</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-gray-100 border-t border-gray-200 px-6 py-4 flex justify-end">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

div::-webkit-scrollbar {
    width: 6px;
}

div::-webkit-scrollbar-track {
    background: #f1f1f1;
}

div::-webkit-scrollbar-thumb {
    background: #cbd5e0;
    border-radius: 3px;
}

div::-webkit-scrollbar-thumb:hover {
    background: #a0aec0;
}
</style>
