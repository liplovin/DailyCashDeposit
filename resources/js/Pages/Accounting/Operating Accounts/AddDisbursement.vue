<script setup>
import { ref, computed } from 'vue';
import { X, Plus } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    operatingAccounts: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'refresh']);

const selectedOperatingAccount = ref('');

const form = ref({
    disbursements: [
        {
            check_number: '',
            date: '',
            amount: ''
        }
    ]
});

const amountDisplays = ref(['']);

const selectedOperatingAccountData = computed(() => {
    if (!selectedOperatingAccount.value) return null;
    return props.operatingAccounts.find(o => String(o.id) === String(selectedOperatingAccount.value));
});

const activeAccounts = computed(() => {
    return props.operatingAccounts.filter(account => account.maturity_date !== null);
});

const formValid = computed(() => {
    return selectedOperatingAccount.value && form.value.disbursements.length > 0 && form.value.disbursements.every(d => d.check_number.trim() && d.date && d.amount);
});

const handleAmountInput = (event, index) => {
    let value = event.target.value;
    
    value = value.replace(/[^\d.]/g, '');
    
    if (value.split('.').length > 2) {
        const decimalIndex = value.indexOf('.');
        value = value.substring(0, decimalIndex + 1) + value.substring(decimalIndex + 1).replace(/\./g, '');
    }
    
    if (value === '') {
        form.value.disbursements[index].amount = '';
        amountDisplays.value[index] = '';
        return;
    }
    
    const parts = value.split('.');
    let integerPart = parts[0] || '';
    let decimalPart = parts[1];
    
    if (integerPart) {
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    let formattedValue;
    if (value.includes('.')) {
        formattedValue = `${integerPart}.${decimalPart !== undefined ? decimalPart : ''}`;
    } else {
        formattedValue = integerPart;
    }
    
    amountDisplays.value[index] = formattedValue;
    form.value.disbursements[index].amount = value.replace(/,/g, '');
};

const validateDuplicateCheckNumbers = async () => {
    const checkNumbers = form.value.disbursements.map(d => d.check_number);
    const uniqueCheckNumbers = new Set(checkNumbers);
    
    if (uniqueCheckNumbers.size !== checkNumbers.length) {
        const duplicates = checkNumbers.filter((item, index) => checkNumbers.indexOf(item) !== index);
        return {
            valid: false,
            message: `Duplicate check number found: "${duplicates[0]}". Each check number must be unique.`
        };
    }
    
    try {
        const response = await axios.post('/accounting/validate-operating-account-disbursement', {
            operating_account_id: selectedOperatingAccount.value,
            disbursements: form.value.disbursements
        });
        
        if (!response.data.valid) {
            return {
                valid: false,
                message: response.data.message || 'Duplicate check number found in database.'
            };
        }
    } catch (error) {
        console.error('Validation error:', error);
        return {
            valid: false,
            message: 'Error validating disbursements. Please try again.'
        };
    }
    
    return { valid: true };
};

const handleSubmit = async () => {
    if (formValid.value) {
        const validation = await validateDuplicateCheckNumbers();
        
        if (!validation.valid) {
            Swal.fire({
                title: 'Validation Error',
                text: validation.message,
                icon: 'warning',
                confirmButtonColor: '#D4A017'
            });
            return;
        }
        
        axios.post('/accounting/operating-account-disbursement', {
            operating_account_id: selectedOperatingAccount.value,
            disbursements: form.value.disbursements.map(d => ({
                ...d,
                operating_account_id: selectedOperatingAccount.value
            }))
        })
        .then(() => {
            Swal.fire({
                title: 'Success!',
                text: `${form.value.disbursements.length} disbursement(s) created successfully.`,
                icon: 'success',
                confirmButtonColor: '#D4A017'
            }).then(() => {
                handleClose();
                router.visit('/accounting/operating-accounts');
            });
        })
        .catch((error) => {
            console.error('Error saving disbursement:', error);
            const errorMessage = error.response?.data?.message || 'Failed to create disbursement. Please try again.';
            Swal.fire({
                title: 'Error',
                text: errorMessage,
                icon: 'error',
                confirmButtonColor: '#D4A017'
            });
        });
    }
};

const addDisbursement = () => {
    form.value.disbursements.push({
        check_number: '',
        date: '',
        amount: ''
    });
    amountDisplays.value.push('');
};

const removeDisbursement = (index) => {
    form.value.disbursements.splice(index, 1);
    amountDisplays.value.splice(index, 1);
};

const handleCheckNumberInput = (event, index) => {
    let value = event.target.value;
    value = value.replace(/[^\d]/g, '');
    form.value.disbursements[index].check_number = value;
    event.target.value = value;
};

const getTotalAmount = () => {
    const total = form.value.disbursements.reduce((sum, d) => {
        const amount = parseFloat(d.amount) || 0;
        return sum + amount;
    }, 0);
    return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const handleClose = () => {
    selectedOperatingAccount.value = '';
    form.value = {
        disbursements: [
            {
                check_number: '',
                date: '',
                amount: ''
            }
        ]
    };
    amountDisplays.value = [''];
    emit('close');
};

const handleKeyDown = (e) => {
    if (e.key === 'Escape') {
        handleClose();
    }
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center" @keydown="handleKeyDown">
        <div class="absolute inset-0 bg-black/50 transition-opacity duration-300" @click="handleClose"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full mx-3 md:mx-4 transform transition-all duration-300 max-h-[calc(100vh-2rem)] md:max-h-[90vh] flex flex-col max-w-2xl md:max-w-5xl lg:max-w-6xl overflow-hidden">
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-200 bg-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Add Disbursement</h2>
                    <button
                        @click="handleClose"
                        class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Operating Account</label>
                    <select
                        v-model="selectedOperatingAccount"
                        class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition-all bg-white text-gray-900 font-medium"
                    >
                        <option value="">-- Choose Operating Account --</option>
                        <option v-for="account in activeAccounts" :key="account.id" :value="account.id">
                            {{ account.operating_account_name }}
                        </option>
                    </select>
                </div>
            </div>

            <div v-if="!selectedOperatingAccountData" class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="text-center">
                    <div class="text-6xl mb-4">📋</div>
                    <p class="text-lg text-gray-600">Please select an operating account to get started</p>
                </div>
            </div>

            <div v-else class="flex flex-col lg:flex-row gap-0 flex-1 min-h-0 overflow-hidden">
                <div class="w-full lg:w-5/12 space-y-4 px-5 py-5 overflow-y-auto bg-gray-50 flex-1 min-h-0">
                    <div class="p-4 bg-yellow-50 rounded-lg border border-yellow-200 flex-shrink-0">
                        <p class="text-xs text-gray-600 font-semibold mb-1">Selected Operating Account</p>
                        <p class="text-sm font-bold text-gray-900">{{ selectedOperatingAccountData?.operating_account_name }}</p>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(disbursement, index) in form.disbursements" :key="index" class="p-4 bg-white rounded-lg border border-gray-200 hover:border-yellow-400 transition-colors shadow-sm">
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                <h4 class="text-sm font-bold text-gray-900">Disbursement {{ index + 1 }}</h4>
                                <button
                                    v-if="form.disbursements.length > 1"
                                    @click="removeDisbursement(index)"
                                    class="px-2.5 py-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200 text-xs font-semibold transition-colors"
                                >
                                    Remove
                                </button>
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Check Number</label>
                                <input
                                    :value="disbursement.check_number"
                                    @input="handleCheckNumberInput($event, index)"
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="Enter check number"
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all text-sm text-gray-900"
                                />
                            </div>

                            <div class="mb-3">
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Date</label>
                                <input
                                    v-model="disbursement.date"
                                    type="date"
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all text-sm text-gray-900"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Amount</label>
                                <input
                                    :value="amountDisplays[index]"
                                    @input="handleAmountInput($event, index)"
                                    type="text"
                                    placeholder="0.00"
                                    class="w-full px-3 py-2 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 transition-all text-sm text-gray-900"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        @click="addDisbursement"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-dashed border-yellow-300 text-yellow-700 hover:bg-yellow-50 transition-colors font-semibold text-sm flex items-center justify-center gap-2"
                    >
                        <Plus class="h-4 w-4" />
                        Add Another Disbursement
                    </button>
                </div>

                <div class="w-full lg:w-7/12 bg-gradient-to-b from-amber-50 to-yellow-50 px-5 py-5 flex flex-col border-t lg:border-t-0 border-gray-200 overflow-y-auto hidden lg:flex">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase tracking-wide">Summary</h3>
                    
                    <div class="flex-1 space-y-3 mb-4 min-h-0 overflow-y-auto">
                        <div v-if="form.disbursements.length === 0" class="text-sm text-gray-500 text-center py-16 flex items-center justify-center h-full">
                            No disbursements added yet
                        </div>
                        <div v-for="(disbursement, index) in form.disbursements" :key="index" class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                            <div class="text-sm font-bold text-gray-900 mb-3 pb-2 border-b border-gray-100">Disbursement {{ index + 1 }}</div>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Check #:</span>
                                    <span class="font-bold text-gray-900">{{ disbursement.check_number || '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Date:</span>
                                    <span class="font-bold text-gray-900">{{ disbursement.date || '-' }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-gray-700">Amount:</span>
                                    <span class="font-bold text-lg text-yellow-600">₱{{ amountDisplays[index] || '0.00' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t-2 border-yellow-300 my-3"></div>

                    <div class="bg-gradient-to-br from-yellow-100 to-amber-100 rounded-lg p-4 border-2 border-yellow-400 shadow-md">
                        <p class="text-xs text-gray-700 mb-1 font-semibold uppercase tracking-wide">Total Disburse Amount</p>
                        <p class="text-4xl font-black text-yellow-700 mb-2">
                            ₱{{ getTotalAmount() }}
                        </p>
                        <p class="text-sm text-yellow-800 font-semibold">{{ form.disbursements.length }} disbursement(s)</p>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 px-6 py-4 border-t border-gray-200 bg-white flex-shrink-0">
                <button
                    @click="handleClose"
                    class="flex-1 px-4 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    Cancel
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="!formValid"
                    class="flex-1 px-4 py-3 rounded-lg bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-semibold hover:from-yellow-600 hover:to-amber-600 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 shadow-lg"
                >
                    Submit Disbursements
                </button>
            </div>
        </div>
    </div>
</template>
