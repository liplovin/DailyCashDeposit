<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-96 overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 flex justify-between items-center">
        <h2 class="text-white text-lg font-semibold">Time Deposit History</h2>
        <button
          @click="$emit('close')"
          class="text-white hover:bg-blue-800 rounded-full p-1 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Tabs -->
      <div class="sticky top-14 bg-white border-b border-gray-200 px-6">
        <div class="flex space-x-8">
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
        <!-- Renewal History Tab -->
        <div v-if="activeTab === 'renewals'">
          <div v-if="timeDeposit.renewals && timeDeposit.renewals.length > 0" class="space-y-4">
            <!-- Timeline -->
            <div class="relative">
              <div class="space-y-6">
                <div
                  v-for="(renewal, index) in timeDeposit.renewals"
                  :key="`renewal-${renewal.id}`"
                  class="relative flex items-start"
                >
                  <!-- Timeline dot and line -->
                  <div class="absolute left-0 top-2 w-4 h-4 bg-blue-600 rounded-full border-4 border-white"></div>
                  <div v-if="index < timeDeposit.renewals.length - 1" class="absolute left-2 top-6 w-0.5 h-12 bg-blue-200"></div>

                  <!-- Renewal details -->
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
                    
                    <div class="mb-3">
                      <p class="text-xs text-gray-500 font-semibold">Explanation</p>
                      <p class="text-sm text-gray-700">{{ renewal.explanation }}</p>
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
          <!-- Empty state for renewals -->
          <div v-else class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500">No renewal history yet</p>
          </div>
        </div>

        <!-- Withdrawal History Tab -->
        <div v-if="activeTab === 'withdrawals'">
          <div v-if="timeDeposit.withdrawals && timeDeposit.withdrawals.length > 0" class="space-y-4">
            <!-- Timeline -->
            <div class="relative">
              <div class="space-y-6">
                <div
                  v-for="(withdrawal, index) in timeDeposit.withdrawals"
                  :key="`withdrawal-${withdrawal.id}`"
                  class="relative flex items-start"
                >
                  <!-- Timeline dot and line -->
                  <div class="absolute left-0 top-2 w-4 h-4 bg-orange-600 rounded-full border-4 border-white"></div>
                  <div v-if="index < timeDeposit.withdrawals.length - 1" class="absolute left-2 top-6 w-0.5 h-12 bg-orange-200"></div>

                  <!-- Withdrawal details -->
                  <div class="ml-8 flex-1 bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <div class="grid grid-cols-2 gap-4 mb-3">
                      <div>
                        <p class="text-xs text-gray-500 font-semibold">Withdrawal Amount</p>
                        <p class="text-sm font-medium text-orange-600">₱ {{ formatCurrency(withdrawal.amount) }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 font-semibold">Withdrawn on</p>
                        <p class="text-sm font-medium text-gray-900">{{ formatDateTime(withdrawal.created_at) }}</p>
                      </div>
                    </div>
                    
                    <div>
                      <p class="text-xs text-gray-500 font-semibold">Explanation</p>
                      <p class="text-sm text-gray-700">{{ withdrawal.explanation }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Empty state for withdrawals -->
          <div v-else class="text-center py-8">
            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-500">No withdrawal history yet</p>
          </div>
        </div>

        <!-- Balance History Tab -->
        <div v-if="activeTab === 'balances'">
          <div v-if="timeDeposit.balances && timeDeposit.balances.length > 0" class="space-y-4">
            <!-- Timeline -->
            <div class="relative">
              <div class="space-y-6">
                <div
                  v-for="(balance, index) in timeDeposit.balances"
                  :key="`balance-${balance.id}`"
                  class="relative flex items-start"
                >
                  <!-- Timeline dot and line -->
                  <div class="absolute left-0 top-2 w-4 h-4 bg-green-600 rounded-full border-4 border-white"></div>
                  <div v-if="index < timeDeposit.balances.length - 1" class="absolute left-2 top-6 w-0.5 h-12 bg-green-200"></div>

                  <!-- Balance details -->
                  <div class="ml-8 flex-1 bg-green-50 rounded-lg p-4 border border-green-200">
                    <div class="grid grid-cols-2 gap-4 mb-3">
                      <div>
                        <p class="text-xs text-gray-500 font-semibold">Amount Added</p>
                        <p class="text-sm font-medium text-green-600">₱ {{ formatCurrency(balance.amount) }}</p>
                      </div>
                      <div>
                        <p class="text-xs text-gray-500 font-semibold">Added on</p>
                        <p class="text-sm font-medium text-gray-900">{{ formatDateTime(balance.created_at) }}</p>
                      </div>
                    </div>
                    
                    <div>
                      <p class="text-xs text-gray-500 font-semibold">Reason</p>
                      <p class="text-sm text-gray-700">{{ balance.explanation }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Empty state for balances -->
          <div v-else class="text-center py-8">
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
</template>

<script setup>
import { ref } from 'vue';

const activeTab = ref('renewals');

defineProps({
  timeDeposit: {
    type: Object,
    required: true,
  },
});

defineEmits(['close']);

const formatDate = (date) => {
  if (!date) return '—';
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatDateTime = (datetime) => {
  if (!datetime) return '—';
  return new Date(datetime).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatCurrency = (value) => {
  return parseFloat(value || 0).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
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

<style scoped>
/* Smooth scrollbar styling */
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
