<script setup>
import { X, Download, Trash2, Pencil } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import Swal from 'sweetalert2';
import EditCollection from './EditCollection.vue';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    collections: {
        type: Array,
        default: () => []
    },
    accountName: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['close', 'deleted']);

const totalAmount = computed(() => {
    return props.collections.reduce((total, collection) => total + parseFloat(collection.collection_amount || 0), 0);
});

const getTotalAmount = (amount) => {
    return parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const formatDateWithTime = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};

const isDeleting = ref(false);

const handleDeleteCollection = async (collectionId, collectionIndex) => {
    const result = await Swal.fire({
        title: 'Delete Collection?',
        text: `Are you sure you want to delete Collection ${collectionIndex + 1}? This action cannot be undone.`,
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
            const response = await fetch(`/treasury3/collection/${collectionId}`, {
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
                    text: 'Collection has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to delete collection.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to delete collection.',
                icon: 'error',
                confirmButtonColor: '#F59E0B'
            });
        } finally {
            isDeleting.value = false;
        }
    }
};

const showEditModal = ref(false);
const selectedCollectionForEdit = ref(null);

const handleEditCollection = (collection) => {
    selectedCollectionForEdit.value = collection;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedCollectionForEdit.value = null;
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/50"
            @click="emit('close')"
        ></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full mx-4 max-w-2xl max-h-[85vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-6 py-5 flex items-center justify-between flex-shrink-0">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-white">Collection Details</h2>
                    <p class="text-yellow-50 text-sm mt-1">{{ accountName }}</p>
                </div>
                <button
                    @click="emit('close')"
                    class="text-white hover:bg-yellow-600 p-1.5 rounded-lg transition-colors"
                >
                    <X class="h-6 w-6" />
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-6 py-6">
                <div class="space-y-4">
                    <div v-for="(collection, index) in collections" :key="collection.id" class="border-l-4 border-yellow-500 bg-gradient-to-r from-white to-gray-50 p-5 rounded-lg shadow-sm hover:shadow-md transition-all duration-200">
                        <!-- Collection Header -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Collection {{ index + 1 }}</h3>
                            </div>
                            <div class="flex items-center gap-3">
                                <span v-if="collection.status === 'pending'" class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-orange-400 to-orange-500 shadow-sm">
                                    Pending
                                </span>
                                <span v-else class="px-4 py-1.5 rounded-full text-xs font-bold text-white bg-gradient-to-r from-green-400 to-green-500 shadow-sm">
                                    Processed
                                </span>
                                <button
                                    v-if="collection.status === 'pending'"
                                    @click="handleEditCollection(collection)"
                                    class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200"
                                    title="Edit this collection"
                                >
                                    <Pencil class="h-5 w-5" />
                                </button>
                                <button
                                    v-if="collection.status === 'pending'"
                                    @click="handleDeleteCollection(collection.id, index)"
                                    :disabled="isDeleting"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                    title="Delete this collection"
                                >
                                    <Trash2 class="h-5 w-5" />
                                </button>
                            </div>
                        </div>

                        <!-- Collection Details -->
                        <div class="space-y-4">
                            <!-- Amount -->
                            <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <span class="text-sm font-semibold text-gray-700">Collection Amount</span>
                                <span class="text-2xl font-bold text-yellow-600">₱{{ getTotalAmount(collection.collection_amount) }}</span>
                            </div>

                            <!-- Deposit Slip -->
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 mb-2 uppercase">Deposit Slip</p>
                                <a v-if="collection.deposit_slip" :href="`/storage/${collection.deposit_slip}`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                    <Download class="h-4 w-4" />
                                    <span>View</span>
                                </a>
                                <span v-else class="text-gray-400 text-sm">No file attached</span>
                            </div>

                            <!-- Check (Optional) -->
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 mb-2 uppercase">Check <span class="text-gray-400 text-xs">(Optional)</span></p>
                                <a v-if="collection.check" :href="`/storage/${collection.check}`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-sm rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                                    <Download class="h-4 w-4" />
                                    <span>View</span>
                                </a>
                                <span v-else class="text-gray-400 text-sm">No file attached</span>
                            </div>

                            <!-- Created Date -->
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <p class="text-xs font-semibold text-gray-600 mb-1 uppercase">Created</p>
                                <p class="text-sm text-gray-900 font-medium">{{ formatDateWithTime(collection.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Summary -->
                <div class="mt-6 pt-6 border-t-2 border-yellow-300">
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 p-4 rounded-lg border-2 border-yellow-300">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-1">Total Collections</p>
                                <p class="text-2xl font-bold text-yellow-600">₱{{ getTotalAmount(totalAmount) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-3xl font-bold text-gray-900">{{ collections.length }}</p>
                                <p class="text-xs font-semibold text-gray-600 uppercase">Item{{ collections.length !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-end flex-shrink-0">
                <button
                    @click="emit('close')"
                    class="px-6 py-2.5 rounded-lg bg-yellow-500 text-white hover:bg-yellow-600 font-bold transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    Close
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Modal Component -->
    <EditCollection
        :isOpen="showEditModal"
        :collection="selectedCollectionForEdit"
        @close="closeEditModal"
        @updated="closeEditModal"
    />
</template>
