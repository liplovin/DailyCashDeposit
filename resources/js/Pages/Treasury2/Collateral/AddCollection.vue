<script setup>
import { ref, computed } from 'vue';
import { X } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    collateral: {
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
    if (!props.collateral || !props.filterDate) {
        return parseFloat(props.collateral?.beginning_balance || 0);
    }
    
    let balance = parseFloat(props.collateral.beginning_balance || 0);
    
    // Add collections before the selected date
    const collectionDate = props.collateral.collection_date 
        ? new Date(props.collateral.collection_date).toISOString().split('T')[0] 
        : null;
    if (collectionDate && collectionDate < props.filterDate) {
        balance += parseFloat(props.collateral.collection || 0);
    }
    
    // Subtract disbursements before the selected date
    const disbursementDate = props.collateral.disbursement_date 
        ? new Date(props.collateral.disbursement_date).toISOString().split('T')[0] 
        : null;
    if (disbursementDate && disbursementDate < props.filterDate) {
        balance -= parseFloat(props.collateral.disbursement || 0);
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
        const response = await axios.post(`/treasury2/collateral/${props.collateral?.id}/collection`, {
            amount: parseFloat(amount.value),
            date: props.filterDate || new Date().toISOString().split('T')[0]
        });

        emit('submit', {
            collateral_id: props.collateral?.id,
            amount: parseFloat(amount.value),
            date: props.filterDate || new Date().toISOString().split('T')[0]
        });
        amount.value = '';
        emit('close');
    } catch (err) {
        error.value = err.response?.data?.message || 'An error occurred while adding collection';
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
            <div class="bg-gradient-to-r from-green-400 to-green-600 px-6 py-6 flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-white">Add Collection</h2>
                    <p class="text-green-100 text-sm mt-1">Enter the collection amount</p>
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

                <!-- Collateral Info Card -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 border-2 border-green-200 rounded-xl p-5">
                    <p class="text-xs text-green-600 font-bold uppercase tracking-widest mb-2">Account Information</p>
                    <p class="text-lg font-bold text-green-900">{{ collateral?.collateral || 'N/A' }}</p>
                    <p class="text-sm text-green-700 mt-2 font-mono">{{ collateral?.account_number }}</p>
                    <div class="mt-4 pt-4 border-t-2 border-green-200">
                        <p class="text-xs text-green-600 font-semibold mb-1">Beginning Balance (Daily)</p>
                        <p class="text-2xl font-bold text-green-900">{{ formatCurrency(rollingBeginningBalance) }}</p>
                    </div>
                </div>

                <!-- Amount Input Section -->
                <div class="space-y-3">
                    <label for="amount" class="block text-sm font-bold text-gray-800">Collection Amount</label>
                    <div class="relative">
                        <span class="absolute left-4 top-4 text-xl text-green-600 font-bold">₱</span>
                        <input
                            id="amount"
                            v-model="amount"
                            type="number"
                            placeholder="0.00"
                            step="0.01"
                            min="0"
                            class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 text-lg font-semibold text-gray-900 placeholder-gray-400"
                        />
                    </div>
                    <div v-if="amount" class="bg-green-50 rounded-lg p-3 border border-green-200">
                        <p class="text-xs text-green-600 font-semibold uppercase">Total Collection</p>
                        <p class="text-xl font-bold text-green-700 mt-1">{{ formattedAmount }}</p>
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
                    class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white font-bold rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-md hover:shadow-lg"
                >
                    {{ isSubmitting ? 'Processing...' : 'Add Collection' }}
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
