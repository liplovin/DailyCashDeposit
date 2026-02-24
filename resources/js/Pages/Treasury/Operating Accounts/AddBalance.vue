<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { X } from 'lucide-vue-next';

const props = defineProps({
    operatingAccount: {
        type: Object,
        default: null
    },
    isOpen: {
        type: Boolean,
        default: false
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

const handleAmountKeyPress = (event) => {
    // Allow only numbers (0-9) and decimal point (.)
    const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    
    if (!allowedKeys.includes(event.key) && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
    }
};

// Reset form when modal opens
watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        form.value = {
            amount: '',
            explanation: ''
        };
        errors.value = {};
    }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

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
        errors.value.amount = 'Please enter a valid amount greater than 0';
    }

    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Reason is required';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    isSubmitting.value = true;

    router.post(
        `/treasury/operating-accounts/${props.operatingAccount.id}/add-balance`,
        {
            amount: parseFloat(form.value.amount),
            explanation: form.value.explanation
        },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Success!',
                    text: `Added ₱${parseFloat(form.value.amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} to beginning balance.`,
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
                        text: 'Failed to add balance. Please try again.',
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
                    <h2 class="text-xl font-bold text-gray-900">Add to Beginning Balance</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Operating Account Info -->
                    <div v-if="operatingAccount" class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Account:</span> {{ operatingAccount.operating_account_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Account Number:</span> {{ operatingAccount.account_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Current Beginning Balance:</span> 
                            <span class="text-blue-600">{{ formatCurrency(operatingAccount.beginning_balance) }}</span>
                        </p>
                    </div>

                    <!-- Amount Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Amount to Add <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500 font-semibold">₱</span>
                            <input
                                type="text"
                                @input="handleAmountInput"
                                @keypress="handleAmountKeyPress"
                                :value="displayAmount"
                                placeholder="0.00"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 text-right"
                                :class="{ 'border-red-500 focus:ring-red-500': errors.amount }"
                            />
                        </div>
                        <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                    </div>

                    <!-- Explanation Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Reason for Adding Balance <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.explanation"
                            placeholder="Enter reason for adding balance..."
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.explanation }"
                        />
                        <p v-if="errors.explanation" class="text-red-500 text-sm mt-1">{{ errors.explanation }}</p>
                    </div>

                    <!-- Form Footer -->
                    <div class="flex gap-2 pt-2">
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
                            class="flex-1 px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 disabled:bg-gray-400 transition-all duration-200"
                        >
                            {{ isSubmitting ? 'Adding...' : 'Add Balance' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
