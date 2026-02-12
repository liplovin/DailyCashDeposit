<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    existingInvestments: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const form = ref({
    investment_name: '',
    account_number: '',
    beginning_balance: '',
    maturity_date: '',
    customInvestment: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Get unique investment names
const uniqueInvestments = computed(() => {
    const seen = new Set();
    return props.existingInvestments.filter(investment => {
        if (seen.has(investment.investment_name)) {
            return false;
        }
        seen.add(investment.investment_name);
        return true;
    });
});

const handleSubmit = () => {
    errors.value = {};
    
    // Remove commas from beginning_balance for validation and submission
    const balanceValue = parseFloat(form.value.beginning_balance.replace(/,/g, ''));
    
    // Determine the investment name value to use
    let investmentValue = form.value.investment_name;
    if (form.value.investment_name === 'other') {
        investmentValue = form.value.customInvestment;
    }
    
    // Validation
    if (!investmentValue.trim()) {
        errors.value.investment_name = 'Investment name is required';
    }
    if (!form.value.account_number.trim()) {
        errors.value.account_number = 'Account number is required';
    }
    if (isNaN(balanceValue) || balanceValue < 0) {
        errors.value.beginning_balance = 'Beginning balance must be a valid positive number';
    }
    if (!form.value.maturity_date.trim()) {
        errors.value.maturity_date = 'Maturity date is required';
    }
    
    if (Object.keys(errors.value).length === 0) {
        isSubmitting.value = true;
        // Send the numeric value (without commas) to the server
        const submitData = {
            investment_name: investmentValue,
            account_number: form.value.account_number,
            beginning_balance: balanceValue.toFixed(2),
            maturity_date: form.value.maturity_date
        };
        router.post('/treasury/investment', submitData, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Created!',
                    text: 'Investment has been created successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
                closeModal();
                isSubmitting.value = false;
            },
            onError: (err) => {
                if (err.account_number) {
                    errors.value.account_number = err.account_number;
                }
                if (err.beginning_balance) {
                    errors.value.beginning_balance = err.beginning_balance;
                }
                isSubmitting.value = false;
            }
        });
    }
};

const closeModal = () => {
    form.value = { investment_name: '', account_number: '', beginning_balance: '', maturity_date: '', customInvestment: '' };
    errors.value = {};
    emit('close');
};

const handleBalanceInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-digit and non-decimal characters
    value = value.replace(/[^\d.]/g, '');
    
    // Split by decimal point - only keep first decimal
    const dotIndex = value.indexOf('.');
    if (dotIndex !== -1) {
        // Has a decimal point
        const integerPart = value.substring(0, dotIndex);
        const decimalPart = value.substring(dotIndex + 1, dotIndex + 3); // Only 2 decimal places
        value = integerPart + '.' + decimalPart;
    }
    
    // Split into parts for formatting
    const parts = value.split('.');
    const integerPart = parts[0] || '0';
    const decimalPart = parts[1] || '';
    
    // Add commas to integer part
    const withCommas = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    
    // Set final value
    if (decimalPart) {
        form.value.beginning_balance = withCommas + '.' + decimalPart;
    } else {
        form.value.beginning_balance = withCommas;
    }
};

const handleBalanceKeydown = (event) => {
    const key = event.key;
    const isNumber = /[0-9]/.test(key);
    const isDecimal = key === '.';
    const isBackspace = key === 'Backspace';
    const isDelete = key === 'Delete';
    const isTab = key === 'Tab';
    const isArrow = ['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(key);
    const isCtrlCommand = event.ctrlKey || event.metaKey;
    
    // Allow: numbers, decimal, backspace, delete, tab, arrows, and Ctrl+C/V/X
    if (!((isNumber || isDecimal || isBackspace || isDelete || isTab || isArrow) || 
          (isCtrlCommand && ['c', 'v', 'x', 'a'].includes(key.toLowerCase())))) {
        event.preventDefault();
    }
};

const convertToDateInput = (dateString) => {
    if (!dateString) return '';
    // Convert mm/dd/yyyy to YYYY-MM-DD format for date input
    const parts = dateString.split('/');
    if (parts.length !== 3) return '';
    
    const month = parts[0];
    const day = parts[1];
    const year = parts[2];
    
    return `${year}-${month}-${day}`;
};

const convertFromDateInput = (dateString) => {
    if (!dateString) return '';
    // Convert YYYY-MM-DD to mm/dd/yyyy format
    const parts = dateString.split('-');
    if (parts.length !== 3) return '';
    
    const year = parts[0];
    const month = parts[1];
    const day = parts[2];
    
    return `${month}/${day}/${year}`;
};

const handleNativeDateChange = (event) => {
    form.value.maturity_date = convertFromDateInput(event.target.value);
};

const handleDateInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-digit characters
    value = value.replace(/\D/g, '');
    
    // Format as mm/dd/yyyy
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length >= 4) {
        value = value.substring(0, 5) + '/' + value.substring(5, 9);
    }
    
    form.value.maturity_date = value;
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
                    <h2 class="text-xl font-bold text-gray-900">Create New Investment</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Investment Name Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Investment Name <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.investment_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.investment_name }"
                        >
                            <option value="" disabled>-- Select Investment --</option>
                            <option v-for="investment in uniqueInvestments" :key="investment.id" :value="investment.investment_name">
                                {{ investment.investment_name }}
                            </option>
                            <option value="other">Other (Please specify)</option>
                        </select>
                        <p v-if="errors.investment_name" class="text-red-500 text-sm mt-1">{{ errors.investment_name }}</p>
                    </div>

                    <!-- Custom Investment Input (shown when "Other" is selected) -->
                    <div v-if="form.investment_name === 'other'">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Enter Investment Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.customInvestment"
                            type="text"
                            placeholder="Enter custom investment name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                        />
                    </div>

                    <!-- Account Number Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Account Number <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.account_number"
                            type="text"
                            placeholder="Enter account number"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.account_number }"
                        />
                        <p v-if="errors.account_number" class="text-red-500 text-sm mt-1">{{ errors.account_number }}</p>
                    </div>

                    <!-- Beginning Balance Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Beginning Balance <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-4 top-2 text-gray-500 font-semibold">₱</span>
                            <input
                                :value="form.beginning_balance"
                                @input="handleBalanceInput"
                                @keydown="handleBalanceKeydown"
                                type="text"
                                inputmode="numeric"
                                placeholder="0"
                                class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                                :class="{ 'border-red-500 focus:ring-red-500': errors.beginning_balance }"
                            />
                        </div>
                        <p v-if="errors.beginning_balance" class="text-red-500 text-sm mt-1">{{ errors.beginning_balance }}</p>
                    </div>

                    <!-- Maturity Date Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Maturity Date <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-2">
                            <!-- Date Picker -->
                            <input
                                :value="convertToDateInput(form.maturity_date)"
                                @change="handleNativeDateChange"
                                type="date"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900"
                                :class="{ 'border-red-500 focus:ring-red-500': errors.maturity_date }"
                            />
                            <!-- Text Input -->
                            <input
                                :value="form.maturity_date"
                                @input="handleDateInput"
                                type="text"
                                placeholder="mm/dd/yyyy"
                                maxlength="10"
                                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                                :class="{ 'border-red-500 focus:ring-red-500': errors.maturity_date }"
                            />
                        </div>
                        <p v-if="errors.maturity_date" class="text-red-500 text-sm mt-1">{{ errors.maturity_date }}</p>
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
                            class="flex-1 px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create' }}
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
