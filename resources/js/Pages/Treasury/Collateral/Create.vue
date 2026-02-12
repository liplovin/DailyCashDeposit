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
    existingCollaterals: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const form = ref({
    collateral: '',
    account_number: '',
    beginning_balance: '',
    maturity_date: '',
    customCollateral: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Get unique collateral names
const uniqueCollaterals = computed(() => {
    const seen = new Set();
    return props.existingCollaterals.filter(collateral => {
        if (seen.has(collateral.collateral)) {
            return false;
        }
        seen.add(collateral.collateral);
        return true;
    });
});

const handleSubmit = () => {
    errors.value = {};
    
    // Remove commas from beginning_balance for validation and submission
    const balanceValue = parseFloat(form.value.beginning_balance.replace(/,/g, ''));
    
    // Determine the collateral value to use
    let collateralValue = form.value.collateral;
    if (form.value.collateral === 'other') {
        collateralValue = form.value.customCollateral;
    }
    
    // Validation
    if (!collateralValue.trim()) {
        errors.value.collateral = 'Collateral name is required';
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
            collateral: collateralValue,
            account_number: form.value.account_number,
            beginning_balance: balanceValue.toFixed(2),
            maturity_date: form.value.maturity_date
        };
        router.post('/treasury/collateral', submitData, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Created!',
                    text: 'Collateral has been created successfully.',
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
    form.value = { collateral: '', account_number: '', beginning_balance: '', maturity_date: '', customCollateral: '' };
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

const handleDateInput = (event) => {
    let value = event.target.value;
    
    // Remove all non-digit characters
    value = value.replace(/\D/g, '');
    
    // Format as mm/dd/yy
    if (value.length >= 2) {
        value = value.substring(0, 2) + '/' + value.substring(2);
    }
    if (value.length >= 5) {
        value = value.substring(0, 5) + '/' + value.substring(5, 7);
    }
    
    form.value.maturity_date = value;
};

const convertToDateInput = (dateString) => {
    if (!dateString) return '';
    // Convert mm/dd/yy to YYYY-MM-DD format for date input
    const parts = dateString.split('/');
    if (parts.length !== 3) return '';
    
    let month = parts[0];
    let day = parts[1];
    let year = parts[2];
    
    // Convert 2-digit year to 4-digit
    if (year.length === 2) {
        const numYear = parseInt(year);
        year = (numYear > 30) ? '19' + year : '20' + year;
    }
    
    return `${year}-${month}-${day}`;
};

const convertFromDateInput = (dateString) => {
    if (!dateString) return '';
    // Convert YYYY-MM-DD to mm/dd/yy format
    const parts = dateString.split('-');
    if (parts.length !== 3) return '';
    
    const year = parts[0].slice(-2);
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
                    <h2 class="text-xl font-bold text-gray-900">Create New Collateral</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Collateral Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Collateral Name <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.collateral"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.collateral }"
                        >
                            <option value="" disabled>-- Select Collateral --</option>
                            <option v-for="collateral in uniqueCollaterals" :key="collateral.id" :value="collateral.collateral">
                                {{ collateral.collateral }}
                            </option>
                            <option value="other">Other (Please specify)</option>
                        </select>
                        <p v-if="errors.collateral" class="text-red-500 text-sm mt-1">{{ errors.collateral }}</p>
                    </div>

                    <!-- Custom Collateral Input (shown when "Other" is selected) -->
                    <div v-if="form.collateral === 'other'">
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Enter Collateral Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.customCollateral"
                            type="text"
                            placeholder="Enter custom collateral name"
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
                                type="text"
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
                                placeholder="mm/dd/yy"
                                maxlength="8"
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
