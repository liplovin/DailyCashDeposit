<script setup>
import { X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    disbursement: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'updated']);

const form = ref({
    check_number: '',
    date: '',
    amount: ''
});

const amountDisplay = ref('');
const isSubmitting = ref(false);

watch(() => props.disbursement, (newVal) => {
    if (newVal) {
        form.value.check_number = newVal.check_number;
        form.value.date = newVal.date;
        form.value.amount = newVal.amount;
        amountDisplay.value = parseFloat(newVal.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}, { deep: true });

const handleAmountKeyDown = (event) => {
    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', '.', ','];
    
    if ((event.ctrlKey || event.metaKey) && ['a', 'c', 'v', 'x'].includes(event.key.toLowerCase())) {
        return;
    }
    
    if (!/^\d|\./.test(event.key) && !allowedKeys.includes(event.key)) {
        event.preventDefault();
    }
};

const handleAmountInput = (event) => {
    let value = event.target.value.replace(/[^\d.]/g, '');
    
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    if (value === '' || value === '.') {
        form.value.amount = '';
        amountDisplay.value = '';
        return;
    }
    
    const decimalParts = value.split('.');
    if (decimalParts.length > 1 && decimalParts[1].length > 2) {
        value = decimalParts[0] + '.' + decimalParts[1].substring(0, 2);
    }
    
    const [integerStr, decimalStr] = value.split('.');
    const formattedInteger = integerStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    amountDisplay.value = decimalStr !== undefined 
        ? formattedInteger + '.' + decimalStr 
        : formattedInteger;
    form.value.amount = value.replace(/,/g, '');
};

const handleCheckNumberInput = (event) => {
    let value = event.target.value;
    value = value.replace(/[^\d]/g, '');
    form.value.check_number = value;
    event.target.value = value;
};

const handleSubmit = async () => {
    if (!form.value.check_number.trim()) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Check number is required.',
            icon: 'warning',
            confirmButtonColor: '#D4A017'
        });
        return;
    }

    if (!form.value.date) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Date is required.',
            icon: 'warning',
            confirmButtonColor: '#D4A017'
        });
        return;
    }

    const cleanAmount = form.value.amount.toString().replace(/,/g, '');
    
    if (!cleanAmount || cleanAmount === '') {
        Swal.fire({
            title: 'Validation Error',
            text: 'Amount is required.',
            icon: 'warning',
            confirmButtonColor: '#D4A017'
        });
        return;
    }
    
    if (isNaN(cleanAmount) || parseFloat(cleanAmount) <= 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Amount must be a valid positive number.',
            icon: 'warning',
            confirmButtonColor: '#D4A017'
        });
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.put(`/accounting/operating-account-disbursement/${props.disbursement.id}`, {
            check_number: form.value.check_number,
            date: form.value.date,
            amount: cleanAmount
        });

        if (response.data.message) {
            Swal.fire({
                title: 'Success!',
                text: 'Disbursement has been updated successfully.',
                icon: 'success',
                confirmButtonColor: '#D4A017',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                emit('updated');
                handleClose();
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: response.data.message || 'Failed to update disbursement.',
                icon: 'error',
                confirmButtonColor: '#D4A017'
            });
        }
    } catch (error) {
        console.error('Update error:', error);
        let errorMessage = 'Failed to update disbursement. Please try again.';
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        Swal.fire({
            title: 'Error!',
            text: errorMessage,
            icon: 'error',
            confirmButtonColor: '#D4A017'
        });
    } finally {
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    form.value = { check_number: '', date: '', amount: '' };
    amountDisplay.value = '';
    emit('close');
};
</script>

<template>
    <div v-if="isOpen && disbursement" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            @click="handleClose"
        ></div>

        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300">
            <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-8 py-6 flex items-center justify-between flex-shrink-0">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-white">Edit Disbursement</h2>
                    <p class="text-yellow-100 text-sm mt-2">Update check number, date and amount</p>
                </div>
                <button
                    @click="handleClose"
                    class="text-white hover:bg-yellow-700 p-2 rounded-lg transition-all duration-200 hover:scale-110"
                >
                    <X class="h-7 w-7" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-8 py-8 min-h-0">
                <div class="space-y-7">
                    <div class="p-4 bg-yellow-50 border-l-4 border-yellow-500 rounded-lg">
                        <p class="text-xs font-semibold text-yellow-700 uppercase mb-3">Current Values</p>
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-yellow-700">Check #:</span>
                                <span class="text-lg font-bold text-yellow-900">{{ disbursement.check_number }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-yellow-700">Date:</span>
                                <span class="text-lg font-bold text-yellow-900">{{ new Date(disbursement.date).toLocaleDateString() }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-yellow-700">Amount:</span>
                                <span class="text-lg font-bold text-yellow-900">₱{{ parseFloat(disbursement.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Check Number</label>
                        <div class="relative group">
                            <input
                                :value="form.check_number"
                                @input="handleCheckNumberInput"
                                type="text"
                                inputmode="numeric"
                                placeholder="Enter check number"
                                class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 font-mono text-lg group-hover:border-yellow-400"
                            />
                        </div>
                        <p class="text-xs text-gray-600 mt-2">✓ Numbers only</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Date</label>
                        <input
                            v-model="form.date"
                            type="date"
                            class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Amount</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-600 font-bold text-lg">₱</span>
                            <input
                                :value="amountDisplay"
                                @keydown="handleAmountKeyDown"
                                @input="handleAmountInput"
                                type="text"
                                inputmode="decimal"
                                placeholder="0.00"
                                class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 font-mono text-lg group-hover:border-yellow-400"
                            />
                        </div>
                        <p class="text-xs text-gray-600 mt-2">✓ Numbers only • Automatic comma formatting • Max 2 decimals</p>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex justify-end gap-3 flex-shrink-0">
                <button
                    @click="handleClose"
                    class="px-6 py-2.5 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 font-bold transition-all duration-200 shadow-sm hover:shadow-md"
                >
                    Cancel
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                    class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-yellow-500 to-yellow-600 text-white hover:shadow-lg hover:scale-105 font-bold transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 disabled:hover:shadow-md"
                >
                    {{ isSubmitting ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </div>
    </div>
</template>
