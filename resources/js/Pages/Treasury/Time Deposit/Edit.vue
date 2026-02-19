<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    timeDeposit: {
        type: Object,
        default: null
    },
    existingTimeDeposits: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const form = ref({
    time_deposit_name: '',
    account_number: '',
    beginning_balance: '',
    maturity_date: '',
    customTimeDeposit: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Get unique time deposit names
const uniqueTimeDeposits = computed(() => {
    const seen = new Set();
    return props.existingTimeDeposits.filter(timeDeposit => {
        if (seen.has(timeDeposit.time_deposit_name)) {
            return false;
        }
        seen.add(timeDeposit.time_deposit_name);
        return true;
    });
});

// Watch for time deposit changes and populate form
watch(() => props.timeDeposit, (newTimeDeposit) => {
    if (newTimeDeposit) {
        // Check if time deposit is in the list
        const existsInList = uniqueTimeDeposits.value.some(t => t.time_deposit_name === newTimeDeposit.time_deposit_name);
        
        form.value = {
            time_deposit_name: existsInList ? newTimeDeposit.time_deposit_name : 'other',
            account_number: newTimeDeposit.account_number,
            beginning_balance: formatBalanceDisplay(newTimeDeposit.beginning_balance),
            maturity_date: formatDateDisplay(newTimeDeposit.maturity_date),
            customTimeDeposit: !existsInList ? newTimeDeposit.time_deposit_name : ''
        };
        errors.value = {};
    }
}, { deep: true });

const formatBalanceDisplay = (value) => {
    if (!value) return '';
    const numValue = parseFloat(value);
    return numValue.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDateDisplay = (value) => {
    if (!value) return '';
    const date = new Date(value);
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const year = String(date.getFullYear());
    return `${month}/${day}/${year}`;
};

const handleBalanceInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-digit and non-decimal characters
    value = value.replace(/[^\d.]/g, '');
    
    // Prevent multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    // Limit decimal places to 2
    const decimalIndex = value.indexOf('.');
    if (decimalIndex !== -1) {
        const integerPart = value.substring(0, decimalIndex);
        const decimalPart = value.substring(decimalIndex + 1, decimalIndex + 3);
        value = integerPart + '.' + decimalPart;
    }
    
    // Split into parts for formatting
    const parts2 = value.split('.');
    const integerPart = parts2[0] || '0';
    const decimalPart = parts2[1] || '';
    
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

const handleSubmit = () => {
    errors.value = {};
    
    // Remove commas from beginning_balance for validation and submission
    const balanceValue = parseFloat(form.value.beginning_balance.replace(/,/g, ''));
    
    // Determine the time deposit value to use
    let timeDepositValue = form.value.time_deposit_name;
    if (form.value.time_deposit_name === 'other') {
        timeDepositValue = form.value.customTimeDeposit;
    }
    
    // Validation
    if (!timeDepositValue.trim()) {
        errors.value.time_deposit_name = 'Time deposit name is required';
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
            time_deposit_name: timeDepositValue,
            account_number: form.value.account_number,
            beginning_balance: balanceValue.toFixed(2),
            maturity_date: form.value.maturity_date
        };
        router.put(`/treasury/time-deposit/${props.timeDeposit.id}`, submitData, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Updated!',
                    text: 'Time deposit has been updated successfully.',
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
    form.value = { time_deposit_name: '', account_number: '', beginning_balance: '', maturity_date: '', customTimeDeposit: '' };
    errors.value = {};
    emit('close');
};

const handleDateInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-digit characters
    value = value.replace(/\D/g, '');
    
    // Format as mm/dd/yyyy
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length >= 5) {
        value = value.substring(0, 5) + '/' + value.substring(5, 9);
    }
    
    form.value.maturity_date = value;
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
</script>

<template>
    <!-- Modal Backdrop -->
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <!-- Modal -->
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Edit Time Deposit</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Time Deposit Name Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Time Deposit Name <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.time_deposit_name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.time_deposit_name }"
                        >
                            <option value="">-- Select Time Deposit --</option>
                            <option v-for="timeDeposit in uniqueTimeDeposits" :key="timeDeposit.time_deposit_name" :value="timeDeposit.time_deposit_name">
                                {{ timeDeposit.time_deposit_name }}
                            </option>
                            <option value="other">Other (Please specify)</option>
                        </select>
                        <p v-if="errors.time_deposit_name" class="text-red-500 text-sm mt-1">{{ errors.time_deposit_name }}</p>
                    </div>

                    <!-- Custom Time Deposit Field (appears when "Other" is selected) -->
                    <div v-if="form.time_deposit_name === 'other'">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Custom Time Deposit Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.customTimeDeposit"
                            type="text"
                            placeholder="Enter custom time deposit name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.time_deposit_name }"
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
                            {{ isSubmitting ? 'Updating...' : 'Update' }}
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
