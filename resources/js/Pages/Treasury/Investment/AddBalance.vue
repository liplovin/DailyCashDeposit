<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';

const handleAmountKeyPress = (event) => {
    const allowedKeys = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '.', 'Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab'];
    if (!allowedKeys.includes(event.key) && !event.ctrlKey && !event.metaKey) {
        event.preventDefault();
    }
};

const handleAmountInput = (event) => {
    let value = event.target.value;
    const cleanedValue = value.replace(/[^\d.]/g, '');
    const parts = cleanedValue.split('.');
    let finalValue = parts[0];
    if (parts.length > 1) {
        finalValue += '.' + parts[1].substring(0, 2);
    }
    form.value.amount = finalValue;
};

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    investment: { type: Object, default: null }
});

const emit = defineEmits(['close']);

const form = ref({ amount: '', explanation: '' });
const errors = ref({});
const isSubmitting = ref(false);

const displayAmount = computed(() => {
    if (!form.value.amount) return '';
    const numValue = form.value.amount.toString().replace(/,/g, '');
    return numValue ? parseFloat(numValue).toLocaleString('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) : '';
});

const newTotalBalance = computed(() => {
    const currentBalance = parseFloat(props.investment?.beginning_balance || 0);
    const addAmount = parseFloat(form.value.amount.replace(/,/g, '') || 0);
    return currentBalance + addAmount;
});

const formatCurrency = (value) => {
    return parseFloat(value || 0).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

watch(() => props.investment, (newInvestment) => {
    if (newInvestment && props.isOpen) {
        form.value = { amount: '', explanation: '' };
        errors.value = {};
    }
}, { deep: true });

const closeModal = () => {
    form.value = { amount: '', explanation: '' };
    errors.value = {};
    emit('close');
};

const handleSubmit = () => {
    errors.value = {};

    if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
        errors.value.amount = 'Amount to add is required and must be greater than 0';
    }

    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Explanation is required';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    isSubmitting.value = true;

    router.post(
        `/treasury/investment/${props.investment.id}/add-balance`,
        { amount: form.value.amount, explanation: form.value.explanation },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Added!',
                    text: 'Investment balance has been added successfully.',
                    icon: 'success',
                    confirmButtonColor: '#10B981',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => { window.location.reload(); });
                closeModal();
            },
            onError: (errors) => {
                if (errors.message) {
                    Swal.fire({ title: 'Error!', text: errors.message, icon: 'error', confirmButtonColor: '#10B981' });
                } else {
                    Swal.fire({ title: 'Error!', text: 'Failed to add balance. Please try again.', icon: 'error', confirmButtonColor: '#10B981' });
                }
            },
            onFinish: () => { isSubmitting.value = false; }
        }
    );
};
</script>

<template>
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Add Balance to Investment</h2>
                    <button @click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <div v-if="investment" class="bg-green-50 rounded-lg p-3 border border-green-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Investment:</span> {{ investment.investment_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Reference:</span> {{ investment.reference_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Current Balance:</span> 
                            ₱ {{ parseFloat(investment.beginning_balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </p>
                        <p class="text-green-600 text-sm font-semibold mt-2">
                            ✓ This action will add the specified amount to the investment balance.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Amount to Add <span class="text-red-500">*</span>
                        </label>
                        <input
                            :value="displayAmount"
                            type="text"
                            placeholder="Enter amount to add"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.amount }"
                            @input="handleAmountInput"
                            @keypress="handleAmountKeyPress"
                        />
                        <p v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount }}</p>
                        
                        <div v-if="form.amount" class="mt-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold">Current Balance</p>
                                    <p class="text-sm font-bold text-gray-900">₱ {{ formatCurrency(investment?.beginning_balance || 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 font-semibold">New Total Balance</p>
                                    <p class="text-sm font-bold text-green-600">
                                        ₱ {{ formatCurrency(newTotalBalance) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Explanation / Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.explanation"
                            placeholder="Enter reason or notes for adding balance..."
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.explanation }"
                        />
                        <p v-if="errors.explanation" class="text-red-500 text-sm mt-1">{{ errors.explanation }}</p>
                    </div>

                    <div class="flex space-x-3 pt-4 border-t border-gray-200">
                        <button @click="closeModal" type="button" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            {{ isSubmitting ? 'Adding...' : 'Add Balance' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
