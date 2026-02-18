<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue';
import AddDisburstmentModal from './AddDisburstment.vue';
import ShowDisbursement from './ShowDisbursement.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Plus, Search, CheckCircle, Eye } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const page = usePage();
const showModal = ref(false);
const showDetailsModal = ref(false);
const selectedDisbursements = ref([]);
const selectedCollateralName = ref('');
const searchQuery = ref('');

const props = defineProps({
    collaterals: {
        type: Array,
        default: () => []
    },
    disbursements: {
        type: Array,
        default: () => []
    }
});

const handleShowDetails = (disbursements, collateralName) => {
    selectedDisbursements.value = disbursements;
    selectedCollateralName.value = collateralName;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedDisbursements.value = [];
};

const groupedDisbursements = computed(() => {
    const groups = {};
    
    props.disbursements.forEach(disbursement => {
        const timestamp = new Date(disbursement.created_at).toLocaleString();
        const collateralId = disbursement.collateral_id;
        const key = `${collateralId}-${timestamp}`;
        
        if (!groups[key]) {
            groups[key] = {
                timestamp,
                createdAt: disbursement.created_at,
                collateralName: disbursement.collateral?.collateral,
                accountNumber: disbursement.collateral?.account_number,
                collateralId: collateralId,
                disbursements: []
            };
        }
        
        groups[key].disbursements.push(disbursement);
    });
    
    return Object.values(groups);
});

const getTotalAmount = (disbursements) => {
    return disbursements.reduce((total, d) => total + parseFloat(d.amount || 0), 0);
};

const filteredDisbursements = computed(() => {
    if (!searchQuery.value.trim()) {
        return groupedDisbursements.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return groupedDisbursements.value.filter(group => 
        group.collateralName.toLowerCase().includes(query) ||
        group.accountNumber.toLowerCase().includes(query) ||
        group.timestamp.toLowerCase().includes(query)
    );
});

const hasDisbursements = computed(() => filteredDisbursements.value && filteredDisbursements.value.length > 0);

const formatDateWithTime = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};

const hasPendingDisbursements = computed(() => {
    return props.disbursements.some(d => d.status === 'pending');
});

const handleProcessDisbursement = async () => {
    // Find all pending disbursements
    const pendingDisbursements = props.disbursements.filter(d => d.status === 'pending');
    let totalAmount = 0;
    
    pendingDisbursements.forEach(d => {
        totalAmount += parseFloat(d.amount || 0);
    });
    
    if (pendingDisbursements.length === 0) {
        Swal.fire({
            title: 'No Pending Disbursements',
            text: 'There are no pending disbursements to process.',
            icon: 'info',
            confirmButtonColor: '#D4A017'
        });
        return;
    }
    
    const result = await Swal.fire({
        title: 'Process Disbursements',
        html: `<div class="text-left"><p class="mb-2">Are you sure you want to process <strong>${pendingDisbursements.length}</strong> pending disbursement(s)?</p><p class="text-gray-600"><strong>Total Amount:</strong> ₱${totalAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</p></div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#22C55E',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, process them',
        cancelButtonText: 'Cancel'
    });
    
    if (result.isConfirmed) {
        try {
            const disbursementIds = pendingDisbursements.map(d => d.id);
            const response = await fetch('/accounting/disbursement/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ disbursement_ids: disbursementIds })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                Swal.fire({
                    title: 'Success!',
                    text: `${pendingDisbursements.length} disbursement(s) marked as processed.`,
                    icon: 'success',
                    confirmButtonColor: '#D4A017'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    title: 'Error',
                    html: data.message || 'Failed to process disbursements.',
                    icon: 'error',
                    confirmButtonColor: '#D4A017'
                });
            }
        } catch (error) {
            Swal.fire({
                title: 'Error',
                text: 'An error occurred while processing disbursements.',
                icon: 'error',
                confirmButtonColor: '#D4A017'
            });
        }
    }
};
</script>

<template>
    <AccountingLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">Pending Disbursements</h1>
                        <p class="text-gray-600 mt-1">Manage and track pending disbursement records</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <Link
                            href="/accounting/processed-disbursement"
                            class="flex items-center space-x-2 px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                        >
                            <Eye class="h-5 w-5" />
                            <span>View Processed</span>
                        </Link>
                        <button
                            v-if="hasPendingDisbursements"
                            @click="handleProcessDisbursement"
                            class="flex items-center space-x-2 px-6 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                        >
                            <CheckCircle class="h-5 w-5" />
                            <span>Process</span>
                        </button>
                        <button 
                            @click="showModal = true"
                            class="inline-flex items-center space-x-2 px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg">
                            <Plus class="h-5 w-5" />
                            <span>Add Disbursement</span>
                        </button>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by collateral, account number, or check number..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasDisbursements && props.disbursements.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Disbursements Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first disbursement record.</p>
                    <button
                        @click="showModal = true"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Add First Disbursement</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredDisbursements.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No disbursements match your search query.</p>
                </div>
            </div>

            <!-- Disbursements Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collateral</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white border-r border-gray-300">Disbursements</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-white border-r border-gray-300">Total Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Created At</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="group in filteredDisbursements" 
                                :key="`${group.collateralId}-${group.timestamp}`"
                                :class="[
                                    'relative transition-all duration-300 ease-out select-none',
                                    'border-b border-gray-300 hover:bg-yellow-100'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                                        {{ group.collateralName }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ group.accountNumber }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-center border-r border-gray-300">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        {{ group.disbursements.length }} item{{ group.disbursements.length !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-bold text-right border-r border-gray-300">
                                    ₱{{ getTotalAmount(group.disbursements).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ formatDateWithTime(group.createdAt) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button
                                        @click="handleShowDetails(group.disbursements, group.collateralName)"
                                        class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 font-semibold text-sm transition-colors"
                                    >
                                        Show Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-yellow-50 border-t-2 border-gray-300">
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900">Total:</td>
                                <td class="px-6 py-4 text-right font-bold text-yellow-700 text-lg">
                                    ₱{{ getTotalAmount(props.disbursements).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </AccountingLayout>

    <!-- Add Disbursement Modal -->
    <AddDisburstmentModal :isOpen="showModal" :collaterals="collaterals" @close="showModal = false" />
    
    <!-- Show Disbursement Details Modal -->
    <ShowDisbursement :isOpen="showDetailsModal" :disbursements="selectedDisbursements" :collateralName="selectedCollateralName" @close="closeDetailsModal" />
</template>
