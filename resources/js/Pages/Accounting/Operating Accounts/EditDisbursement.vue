<script setup>
import { X } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
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
    amount: '',
    payment_type: 'CHECK',
    payments: []
});

const amountDisplay = ref('');
const paymentAmountDisplays = ref([]);
const isSubmitting = ref(false);

watch(() => props.disbursement, (newVal) => {
    if (newVal) {
        form.value.check_number = newVal.check_number;
        form.value.date = newVal.date;
        form.value.amount = newVal.amount;
        form.value.payment_type = newVal.payment_type || 'CHECK';
        form.value.payments = newVal.payments && newVal.payments.length > 0 
            ? JSON.parse(JSON.stringify(newVal.payments))
            : [{ payment_for: '', payable_to: '', amount: '' }];
        
        // Initialize payment amount displays
        paymentAmountDisplays.value = form.value.payments.map(p => 
            (parseFloat(p.amount) || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        );
        
        amountDisplay.value = parseFloat(newVal.amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
}, { deep: true });

const calculateTotalPaymentAmount = () => {
    return form.value.payments.reduce((total, payment) => {
        return total + (parseFloat(payment.amount) || 0);
    }, 0);
};

const updateTotalAmount = () => {
    const total = calculateTotalPaymentAmount();
    form.value.amount = total.toString();
    amountDisplay.value = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const handlePaymentAmountInput = (event, paymentIndex) => {
    let value = event.target.value;
    
    value = value.replace(/[^\d.]/g, '');
    
    if (value.split('.').length > 2) {
        const decimalIndex = value.indexOf('.');
        value = value.substring(0, decimalIndex + 1) + value.substring(decimalIndex + 1).replace(/\./g, '');
    }
    
    if (value === '') {
        form.value.payments[paymentIndex].amount = '';
        paymentAmountDisplays.value[paymentIndex] = '';
        updateTotalAmount();
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
    
    paymentAmountDisplays.value[paymentIndex] = formattedValue;
    form.value.payments[paymentIndex].amount = value.replace(/,/g, '');
    
    // Auto-update total amount
    updateTotalAmount();
};

const handleAmountKeyDown = (event) => {
    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', '.', ','];
    
    if ((event.ctrlKey || event.metaKey) && ['a', 'c', 'v', 'x'].includes(event.key.toLowerCase())) {
        return;
    }
    
    if (!/^\d|\./.test(event.key) && !allowedKeys.includes(event.key)) {
        event.preventDefault();
    }
};

const handleCheckNumberInput = (event) => {
    let value = event.target.value;
    form.value.check_number = value;
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

    if (!form.value.payments || form.value.payments.length === 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'At least one payment entry is required.',
            icon: 'warning',
            confirmButtonColor: '#D4A017'
        });
        return;
    }

    const totalPaymentAmount = calculateTotalPaymentAmount();
    
    for (let i = 0; i < form.value.payments.length; i++) {
        const payment = form.value.payments[i];
        if (!payment.payment_for.trim()) {
            Swal.fire({
                title: 'Validation Error',
                text: `Payment ${i + 1}: "Payment For" is required.`,
                icon: 'warning',
                confirmButtonColor: '#D4A017'
            });
            return;
        }
        if (!payment.payable_to.trim()) {
            Swal.fire({
                title: 'Validation Error',
                text: `Payment ${i + 1}: "Payable TO" is required.`,
                icon: 'warning',
                confirmButtonColor: '#D4A017'
            });
            return;
        }
        if (!payment.amount || parseFloat(payment.amount) <= 0) {
            Swal.fire({
                title: 'Validation Error',
                text: `Payment ${i + 1}: Amount must be greater than 0.`,
                icon: 'warning',
                confirmButtonColor: '#D4A017'
            });
            return;
        }
    }
    
    if (totalPaymentAmount <= 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Total payment amount must be greater than 0.',
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
            amount: cleanAmount,
            payments: form.value.payments
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

const addPaymentEntry = () => {
    form.value.payments.push({
        payment_for: '',
        payable_to: '',
        amount: ''
    });
    paymentAmountDisplays.value.push('');
};

const removePaymentEntry = (index) => {
    if (form.value.payments.length > 1) {
        form.value.payments.splice(index, 1);
        paymentAmountDisplays.value.splice(index, 1);
        updateTotalAmount();
    }
};

const handleClose = () => {
    form.value = { check_number: '', date: '', amount: '', payments: [] };
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
                            <div v-if="disbursement.payments && disbursement.payments.length > 0">
                                <span class="text-sm text-yellow-700">Payments:</span>
                                <div v-for="(payment, idx) in disbursement.payments" :key="idx" class="mt-1">
                                    <span class="text-xs font-semibold text-yellow-600">Payment {{ idx + 1 }}:</span>
                                    <span class="text-sm text-yellow-900">{{ payment.payment_for }} → {{ payment.payable_to }}</span>
                                </div>
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
                                placeholder="Enter check number"
                                class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 font-mono text-lg group-hover:border-yellow-400"
                            />
                        </div>
                        <p class="text-xs text-gray-600 mt-2">✓ Numbers, letters, and symbols allowed</p>
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
                        <label class="block text-sm font-bold text-gray-800 mb-3">Amount (Read-Only - Auto-calculated from Payments)</label>
                        <div class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-lg bg-gray-100 text-gray-600 font-mono text-lg font-bold">
                            ₱{{ amountDisplay }}
                        </div>
                        <p class="text-xs text-gray-600 mt-2">ℹ This amount is automatically calculated from the sum of all payment entries</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-bold text-gray-800 mb-0">Payment Entries <span class="text-red-600">*</span></label>
                            <span class="text-xs font-semibold text-gray-600">{{ form.payments.length }} entry/entries</span>
                        </div>
                        
                        <div v-for="(payment, paymentIndex) in form.payments" :key="paymentIndex" class="p-4 bg-gray-50 rounded-lg border-2 border-gray-200 space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                                <span class="text-xs font-bold text-gray-700 uppercase">Payment {{ paymentIndex + 1 }}</span>
                                <button
                                    v-if="form.payments.length > 1"
                                    @click="removePaymentEntry(paymentIndex)"
                                    type="button"
                                    class="px-2 py-1 text-xs bg-red-100 text-red-700 hover:bg-red-200 rounded transition-colors font-semibold"
                                >
                                    Remove
                                </button>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Payment For <span class="text-red-600">*</span></label>
                                <input
                                    v-model="payment.payment_for"
                                    type="text"
                                    placeholder="e.g., Salary, Office Supplies"
                                    class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Payable TO <span class="text-red-600">*</span></label>
                                <input
                                    v-model="payment.payable_to"
                                    type="text"
                                    placeholder="e.g., John Doe, ABC Company"
                                    class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                                />
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-2">Amount <span class="text-red-600">*</span></label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600 font-bold">₱</span>
                                    <input
                                        :value="paymentAmountDisplays[paymentIndex] || ''"
                                        @input="handlePaymentAmountInput($event, paymentIndex)"
                                        type="text"
                                        placeholder="0.00"
                                        class="w-full pl-8 pr-4 py-2.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 font-semibold"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            @click="addPaymentEntry"
                            type="button"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-dashed border-yellow-300 text-yellow-700 hover:bg-yellow-50 transition-colors font-semibold text-sm"
                        >
                            + Add Payment Entry
                        </button>
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
