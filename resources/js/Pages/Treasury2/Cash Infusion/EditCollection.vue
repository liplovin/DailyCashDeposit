<script setup>
import { ref, watch } from 'vue';
import { X } from 'lucide-vue-next';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    cashInfusion: { type: Object, default: null }
});

const emit = defineEmits(['close', 'submit']);
const amount = ref(0);
const isSubmitting = ref(false);

watch(() => props.cashInfusion, (newVal) => {
    if (newVal && props.isOpen) {
        amount.value = newVal.collection || 0;
    }
}, { deep: true, immediate: true });

watch(() => props.isOpen, (isOpen) => {
    if (isOpen && props.cashInfusion) {
        amount.value = props.cashInfusion.collection || 0;
    }
});

const handleSubmit = async () => {
    if (!amount.value || amount.value < 0) {
        Swal.fire('Validation Error', 'Please enter a valid amount', 'warning');
        return;
    }
    
    isSubmitting.value = true;
    
    try {
        await axios.put(`/treasury2/cash-infusion/${props.cashInfusion?.id}/collection`, {
            amount: parseFloat(amount.value)
        });
        
        Swal.fire({
            title: 'Success!',
            text: `Collection updated to ₱${parseFloat(amount.value).toFixed(2)}`,
            icon: 'success',
            confirmButtonColor: '#3B82F6'
        }).then(() => window.location.reload());
    } catch (err) {
        Swal.fire('Error!', err.response?.data?.message || 'Failed to update collection', 'error');
    } finally {
        isSubmitting.value = false;
    }
};

const handleClose = () => {
    amount.value = 0;
    emit('close');
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border-2 border-gray-200">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-400 to-blue-500 px-6 py-5 flex justify-between items-center">
                <h2 class="text-xl font-bold text-white">Edit Collection</h2>
                <button
                    @click="handleClose"
                    class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-1 transition-all duration-200"
                >
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Body -->
            <div class="px-6 py-6 space-y-4">
                <!-- Cash Infusion Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Cash Infusion</label>
                    <input
                        type="text"
                        :value="cashInfusion?.cash_infusion_name"
                        disabled
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-medium text-sm cursor-not-allowed"
                    />
                </div>

                <!-- Account Number -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Account Number</label>
                    <input
                        type="text"
                        :value="cashInfusion?.account_number"
                        disabled
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg bg-gray-50 text-gray-700 font-medium text-sm cursor-not-allowed"
                    />
                </div>

                <!-- Collection Amount -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Collection Amount</label>
                    <input
                        v-model.number="amount"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg bg-white text-gray-700 text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-200"
                    />
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t-2 border-gray-200">
                <button
                    @click="handleClose"
                    class="px-4 py-2 border-2 border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-semibold text-sm transition-colors duration-200"
                >
                    Cancel
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                    class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:shadow-lg hover:shadow-blue-500/40 disabled:bg-gray-400 disabled:cursor-not-allowed font-semibold text-sm transition-all duration-200"
                >
                    {{ isSubmitting ? 'Updating...' : 'Update Collection' }}
                </button>
            </div>
        </div>
    </div>
</template>
