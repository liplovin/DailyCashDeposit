<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import Swal from 'sweetalert2';

const handleAmountKeyPress = (event) => {
    const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (!allowedKeys.includes(event.key) && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
    }
};

const handleAmountInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-numeric characters except decimal point
    const cleanedValue = value.replace(/[^\d.]/g, '');
    
    // Prevent multiple decimal points
    const parts = cleanedValue.split('.');
    let finalValue = parts[0];
    if (parts.length > 1) {
        finalValue += '.' + parts[1].substring(0, 2);
    }
    
    form.value.amount = finalValue;
};

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    dollar: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = ref({
    amount: '',
    explanation: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Format amount with commas as user types
const displayAmount = computed(() => {
    if (!form.value.amount) return '';
    const numValue = form.value.amount.toString().replace(/,/g, '');
    return numValue ? parseFloat(numValue).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : '';
});

// Calculate remaining balance after withdrawal
const remainingBalance = computed(() => {
    const currentBalance = parseFloat(props.dollar?.beginning_balance || 0);
    const withdrawAmount = parseFloat(form.value.amount.replace(/,/g, '') || 0);
    return currentBalance - withdrawAmount;
});

const isOverLimit = computed(() => {
    return remainingBalance.value < 0;
});

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

// Watch for dollar changes and reset form
watch(() => props.dollar, (newDollar) => {
    if (newDollar && props.isOpen) {
        form.value = {
            amount: '',
            explanation: ''
        };
        errors.value = {};
    }
}, { deep: true });

const closeModal = () => {
    form.value = {
        amount: '',
        explanation: ''
    };
    errors.value = {};
    emit('close');
};

const handleSubmit = () => {
    errors.value = {};

    // Validation
    if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
        errors.value.amount = 'Withdrawal amount is required and must be greater than 0';
    }

    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Explanation is required';
    }

    // Check if withdrawal amount exceeds available balance
    const withdrawAmount = parseFloat(form.value.amount.replace(/,/g, '') || 0);
    const currentBalance = parseFloat(props.dollar?.beginning_balance || 0);
    
    if (withdrawAmount > currentBalance) {
        errors.value.amount = 'Withdrawal amount cannot exceed available balance';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    isSubmitting.value = true;

    router.post(
        `/treasury/dollar/${props.dollar.id}/withdraw`,
        {
            amount: form.value.amount,
            explanation: form.value.explanation
        },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Withdrawn!',
                    text: 'Dollar withdrawal has been recorded successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
                closeModal();
            },
            onError: (errors) => {
                if (errors.message) {
                    Swal.fire({
                        title: 'Error!',
                        text: errors.message,
                        icon: 'error',
                        confirmButtonColor: '#F59E0B'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to record withdrawal. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#F59E0B'
                    });
                }
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        }
    );
};
</script>

<template>
    <!-- Modal Backdrop -->
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <!-- Modal -->
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Withdraw Dollar</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Investment Info -->
                    <div v-if="dollar" class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Account Number:</span> {{ dollar.account_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Amount:</span> 
                            $ {{ parseFloat(dollar.beginning_balance || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </p>
                        <p class="text-orange-600 text-sm font-semibold mt-2">
                            ⚠️ This action will withdraw the specified amount from the investment.
                        </p>
                    </div>

                    <!-- Withdrawal Amount Field -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-semibold text-gray-900">
                                Withdrawal Amount <span class="text-red-500">*</span>
                            </label>
                            <button
                                type="button"
                                @click="form.amount = (dollar?.beginning_balance || 0).toString()"
                                class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded transition-colors"
                            >
                                Withdraw All
                            </button>
                        </div>
                        <input
                            :value="displayAmount"
                            type="text"
                            placeholder="Enter withdrawal amount"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.amount }"
                            @input="handleAmountInput"
                            @keypress="handleAmountKeyPress"
                        />
                        <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                        
                        <!-- Remaining Balance Preview -->
                        <div v-if="form.amount" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold">Current Balance</p>
                                    <p class="text-sm font-bold text-gray-900">$ {{ formatCurrency(dollar?.beginning_balance || 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold">Remaining After Withdrawal</p>
                                    <p class="text-sm font-bold" :class="isOverLimit ? 'text-red-600' : 'text-blue-600'">
                                        $ {{ formatCurrency(remainingBalance) }}
                                    </p>
                                </div>
                            </div>
                            <p v-if="isOverLimit" class="text-xs text-red-600 font-semibold mt-2">
                                ⚠️ Withdrawal amount exceeds available balance!
                            </p>
                        </div>
                    </div>

                    <!-- Explanation Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Explanation / Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.explanation"
                            placeholder="Enter reason or notes for withdrawal..."
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.explanation }"
                        />
                        <p v-if="errors.explanation" class="text-red-500 text-sm mt-1">{{ errors.explanation }}</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Withdrawing...' : 'Withdraw' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
