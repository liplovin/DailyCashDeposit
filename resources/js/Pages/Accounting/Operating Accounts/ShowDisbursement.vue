<script setup>
import { X, Trash2, Pencil } from 'lucide-vue-next';
import { ref } from 'vue';
import Swal from 'sweetalert2';
import EditDisbursement from './EditDisbursement.vue';

defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    group: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'refresh']);

const showEditModal = ref(false);
const selectedDisbursementForEdit = ref(null);
const isDeleting = ref(false);

const getTotalAmount = (items) => {
    return items.reduce((total, item) => total + parseFloat(item.amount || 0), 0);
};

const formatAmount = (amount) => {
    return parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDateFull = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};

const handleEditDisbursement = (disbursement) => {
    selectedDisbursementForEdit.value = disbursement;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedDisbursementForEdit.value = null;
};

const handleDeleteDisbursement = async (disbursementId, disbursementIndex) => {
    const result = await Swal.fire({
        title: 'Delete Disbursement?',
        text: `Are you sure you want to delete Disbursement ${disbursementIndex + 1}? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        isDeleting.value = true;
        try {
            const response = await window.axios.delete(`/accounting/operating-account-disbursement/${disbursementId}`);
            const data = response.data;

            if (data.message) {
                await Swal.fire({
                    title: 'Deleted!',
                    text: 'Disbursement has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#D4A017',
                    timer: 2000,
                    timerProgressBar: true,
                    didClose: true
                });
                emit('refresh');
                emit('close');
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete disbursement.',
                    icon: 'error',
                    confirmButtonColor: '#D4A017'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to delete disbursement.',
                icon: 'error',
                confirmButtonColor: '#D4A017'
            });
        } finally {
            isDeleting.value = false;
        }
    }
};
</script>

<template>
    <div v-if="isOpen && group" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="$emit('close')">
        <div class="bg-white rounded-xl shadow-2xl w-full mx-4 max-w-3xl max-h-[85vh] overflow-hidden flex flex-col" @click.stop>
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-400 to-blue-500 flex-shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-white">Disbursement Details</h2>
                    <p class="text-blue-50 text-sm mt-1">{{ group?.operatingAccountName }}</p>
                </div>
                <button @click="$emit('close')" class="text-white hover:bg-blue-600 p-1.5 rounded-lg transition-colors">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-6">
                <div class="space-y-4">
                    <div v-for="(disbursement, index) in group.disbursements" :key="disbursement.id" class="border-l-4 border-blue-500 bg-gradient-to-r from-white to-gray-50 p-5 rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Disbursement {{ index + 1 }}</h3>
                            <div class="flex items-center gap-3" v-if="disbursement.status === 'pending'">
                                <button
                                    @click="handleEditDisbursement(disbursement)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                    title="Edit this disbursement"
                                >
                                    <Pencil class="h-5 w-5" />
                                </button>
                                <button
                                    @click="handleDeleteDisbursement(disbursement.id, index)"
                                    :disabled="isDeleting"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Delete this disbursement"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Status</span>
                                <span v-if="disbursement.status === 'pending'" class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-orange-400 to-orange-500 shadow-sm">
                                    Pending
                                </span>
                                <span v-else class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-green-400 to-green-500 shadow-sm">
                                    Processed
                                </span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-semibold text-gray-700">Check Number</span>
                                <span class="font-bold text-blue-700">{{ disbursement.check_number }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-sm font-semibold text-gray-700">Date</span>
                                <span class="font-semibold text-gray-900">{{ new Date(disbursement.date).toLocaleDateString() }}</span>
                            </div>

                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-semibold text-gray-700">Amount</span>
                                <span class="text-2xl font-bold text-blue-600">₱{{ formatAmount(disbursement.amount) }}</span>
                            </div>

                            <!-- Payment Entries -->
                            <div v-if="disbursement.payments && disbursement.payments.length > 0" class="space-y-2">
                                <p class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">Payment Details ({{ disbursement.payments.length }})</p>
                                <div v-for="(payment, paymentIndex) in disbursement.payments" :key="payment.id" class="p-3 bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg border-2 border-purple-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-bold text-purple-700 uppercase">Payment {{ paymentIndex + 1 }}</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-white p-2 rounded border border-purple-100">
                                        <span class="text-xs font-semibold text-gray-600">For:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ payment.payment_for }}</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-white p-2 rounded border border-pink-100">
                                        <span class="text-xs font-semibold text-gray-600">To:</span>
                                        <span class="text-sm font-medium text-gray-900">{{ payment.payable_to }}</span>
                                    </div>
                                    <div class="flex items-center justify-between bg-white p-2 rounded border border-yellow-100">
                                        <span class="text-xs font-semibold text-gray-600">Amount:</span>
                                        <span class="text-sm font-bold text-yellow-600">₱{{ formatAmount(payment.amount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Fallback for old single payment structure -->
                            <div v-else-if="disbursement.payment_for || disbursement.payable_to" class="space-y-2">
                                <div v-if="disbursement.payment_for" class="p-3 bg-purple-50 rounded-lg border border-purple-200">
                                    <p class="text-xs font-semibold text-gray-600 mb-1 uppercase">Payment For</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ disbursement.payment_for }}</p>
                                </div>

                                <div v-if="disbursement.payable_to" class="p-3 bg-pink-50 rounded-lg border border-pink-200">
                                    <p class="text-xs font-semibold text-gray-600 mb-1 uppercase">Payable TO</p>
                                    <p class="text-sm text-gray-900 font-medium">{{ disbursement.payable_to }}</p>
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 mb-1 uppercase">Created</p>
                                <p class="text-sm text-gray-900 font-medium">{{ formatDateFull(disbursement.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t-2 border-blue-300">
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-4 rounded-lg border-2 border-blue-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Total Disbursements</p>
                                <p class="text-2xl font-bold text-blue-600">₱{{ formatAmount(getTotalAmount(group.disbursements)) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-gray-900">{{ group.disbursements.length }}</p>
                                <p class="text-xs font-semibold text-gray-600 uppercase">Item{{ group.disbursements.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end flex-shrink-0">
                <button
                    @click="$emit('close')"
                    class="px-6 py-2.5 rounded-lg bg-blue-500 text-white hover:bg-blue-600 font-bold transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <EditDisbursement
        :isOpen="showEditModal"
        :disbursement="selectedDisbursementForEdit"
        @close="closeEditModal"
        @updated="$emit('refresh')"
    />
</template>
