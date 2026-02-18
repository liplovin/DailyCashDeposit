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
    disbursements: {
        type: Array,
        default: () => []
    },
    collateralName: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close']);

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
            const response = await fetch(`/accounting/disbursement/${disbursementId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();

            if (data.success) {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Disbursement has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#D4A017',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
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
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="$emit('close')">
        <div class="bg-white rounded-xl shadow-2xl w-full mx-4 max-w-3xl max-h-[85vh] overflow-hidden flex flex-col" @click.stop>
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-yellow-400 to-yellow-500 flex-shrink-0">
                <div>
                    <h2 class="text-2xl font-bold text-white">Disbursement Details</h2>
                    <p class="text-yellow-50 text-sm mt-1">{{ collateralName }}</p>
                </div>
                <button @click="$emit('close')" class="text-white hover:bg-yellow-600 p-1.5 rounded-lg transition-colors">
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-y-auto px-6 py-6">
                <div class="space-y-4">
                    <div v-for="(disbursement, index) in disbursements" :key="disbursement.id" class="border-l-4 border-yellow-500 bg-gradient-to-r from-white to-gray-50 p-5 rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                        <!-- Disbursement Header -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900">Disbursement {{ index + 1 }}</h3>
                            <div class="flex items-center gap-3">
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

                        <!-- Disbursement Details -->
                        <div class="space-y-3">
                            <!-- Status Badge -->
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-700">Status</span>
                                <span v-if="disbursement.status === 'pending'" class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-orange-400 to-orange-500 shadow-sm">
                                    Pending
                                </span>
                                <span v-else class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-green-400 to-green-500 shadow-sm">
                                    Processed
                                </span>
                            </div>

                            <!-- Check Number -->
                            <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <span class="text-sm font-semibold text-gray-700">Check Number</span>
                                <span class="font-bold text-blue-700">{{ disbursement.check_number }}</span>
                            </div>

                            <!-- Date -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-sm font-semibold text-gray-700">Date</span>
                                <span class="font-semibold text-gray-900">{{ new Date(disbursement.date).toLocaleDateString() }}</span>
                            </div>

                            <!-- Amount -->
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="text-sm font-semibold text-gray-700">Amount</span>
                                <span class="text-2xl font-bold text-yellow-600">₱{{ formatAmount(disbursement.amount) }}</span>
                            </div>

                            <!-- Created -->
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 mb-1 uppercase">Created</p>
                                <p class="text-sm text-gray-900 font-medium">{{ formatDateFull(disbursement.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Summary -->
                <div class="mt-6 pt-6 border-t-2 border-yellow-300">
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 p-4 rounded-lg border-2 border-yellow-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Total Disbursements</p>
                                <p class="text-2xl font-bold text-yellow-600">₱{{ formatAmount(getTotalAmount(disbursements)) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-gray-900">{{ disbursements.length }}</p>
                                <p class="text-xs font-semibold text-gray-600 uppercase">Item{{ disbursements.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end flex-shrink-0">
                <button
                    @click="$emit('close')"
                    class="px-6 py-2.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 font-bold transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Modal Component -->
    <EditDisbursement
        :isOpen="showEditModal"
        :disbursement="selectedDisbursementForEdit"
        @close="closeEditModal"
        @updated="closeEditModal"
    />
</template>

