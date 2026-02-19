<script setup>
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    otherInvestment: {
        type: Object,
        default: null
    },
    filterDate: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['close', 'submit']);

const amount = ref('');
const isSubmitting = ref(false);
const error = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

// Calculate rolling beginning balance for the selected date
const rollingBeginningBalance = computed(() => {
    if (!props.otherInvestment || !props.filterDate) {
        return parseFloat(props.otherInvestment?.beginning_balance || 0);
    }
    
    let balance = parseFloat(props.otherInvestment.beginning_balance || 0);
    
    // Add collections before the selected date
    const collectionDate = props.otherInvestment.collection_date 
        ? new Date(props.otherInvestment.collection_date).toISOString().split('T')[0] 
        : null;
    if (collectionDate && collectionDate < props.filterDate) {
        balance += parseFloat(props.otherInvestment.collection || 0);
    }
    
    // Subtract disbursements before the selected date
    const disbursementDate = props.otherInvestment.disbursement_date 
        ? new Date(props.otherInvestment.disbursement_date).toISOString().split('T')[0] 
        : null;
    if (disbursementDate && disbursementDate < props.filterDate) {
        balance -= parseFloat(props.otherInvestment.disbursement || 0);
    }
    
    return balance;
});

const formattedAmount = computed(() => {
    if (!amount.value) return '₱0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount.value || 0);
});

const handleSubmit = async () => {
    if (!amount.value || parseFloat(amount.value) <= 0) {
        error.value = 'Please enter a valid amount';
        return;
    }

    isSubmitting.value = true;
    error.value = '';
    
    try {
        const response = await axios.post(`/treasury2/other-investment/${props.otherInvestment?.id}/disbursement`, {
            amount: parseFloat(amount.value),
            date: props.filterDate || new Date().toISOString().split('T')[0]
        });

        emit('submit', {
            otherInvestment_id: props.otherInvestment?.id,
            amount: parseFloat(amount.value),
            date: props.filterDate || new Date().toISOString().split('T')[0]
        });
        amount.value = '';
        emit('close');
    } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while adding disbursement';
    } finally {
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    amount.value = '';
    error.value = '';
    emit('close');
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full animation-fade-in overflow-hidden">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-red-400 to-red-600 px-6 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Add Disbursement</h2>
                    <p class="text-red-100 text-sm mt-1">Enter the disbursement amount</p>
                </div>
                <button
                    @click="handleClose"
                    class="p-2 rounded-lg bg-white bg-opacity-20 hover:bg-opacity-30 text-white transition-all duration-200"
                    title="Close modal"
                >
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 space-y-5">
                <!-- Error Message -->
                <div v-if="error" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4">
                    <p class="text-sm text-red-700 font-medium">{{ error }}</p>
                </div>

                <!-- Investment Info Card -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 border-2 border-red-200 rounded-xl p-5">
                    <p class="text-xs text-red-600 font-bold uppercase tracking-widest mb-2">Account Information</p>
                    <p class="text-lg font-bold text-red-900">{{ otherInvestment?.other_investment_name || 'N/A' }}</p>
                    <p class="text-sm text-red-700 mt-2 font-mono">{{ otherInvestment?.account_number }}</p>
                    <div class="mt-4 pt-4 border-t-2 border-red-200">
                        <p class="text-xs text-red-600 font-semibold mb-1">Beginning Balance (Daily)</p>
                        <p class="text-2xl font-bold text-red-900">{{ formatCurrency(rollingBeginningBalance) }}</p>
                    </div>
                </div>

                <!-- Amount Input Section -->
                <div class="space-y-3">
                    <label for="amount" class="block text-sm font-bold text-gray-800">Disbursement Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-4 text-xl text-red-600 font-bold">₱</span>
                        <input
                            id="amount"
                            v-model="amount"
                            type="number"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 text-lg font-semibold text-gray-900 placeholder-gray-400"
                        />
                    </div>
                    <div v-if="amount" class="bg-red-50 rounded-lg p-3 border border-red-200">
                        <p class="text-xs text-red-600 font-semibold uppercase">Total Disbursement</p>
                        <p class="text-xl font-bold text-red-700 mt-1">{{ formattedAmount }}</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center gap-3 px-6 py-4 border-t border-gray-200 bg-gray-50">
                <button
                    @click="handleClose"
                    class="flex-1 px-4 py-3 text-gray-700 font-semibold border-2 border-gray-300 rounded-lg hover:bg-gray-100 hover:border-gray-400 transition-all duration-200"
                >
                    Cancel
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="isSubmitting || !amount || parseFloat(amount) <= 0"
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-red-500 to-red-600 text-white font-bold rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                >
                    {{ isSubmitting ? 'Processing...' : 'Add Disbursement' }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animation-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
