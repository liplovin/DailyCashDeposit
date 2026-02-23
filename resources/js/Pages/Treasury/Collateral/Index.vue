<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateCollateralModal from './Create.vue';
import EditCollateralModal from './Edit.vue';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

onMounted(() => {
    document.title = 'Collateral Management - Daily Deposit';
    
    // Auto-search from dashboard route parameter
    const params = new URLSearchParams(window.location.search);
    const searchParam = params.get('search');
    if (searchParam) {
        searchQuery.value = searchParam;
    }
});

const props = defineProps({
    collaterals: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showModal = ref(false);
const showEditModal = ref(false);
const selectedCollateral = ref(null);

const hasCollaterals = computed(() => filteredCollaterals.value && filteredCollaterals.value.length > 0);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const filteredCollaterals = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.collaterals;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.collaterals.filter(collateral => 
        collateral.collateral.toLowerCase().includes(query) ||
        collateral.account_number.toLowerCase().includes(query)
    );
});

const getCollectionAmount = (collateral) => {
    if (!filterDate.value) {
        return collateral.collection || 0;
    }
    const collectionDate = collateral.collection_date ? new Date(collateral.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (collateral.collection || 0) : 0;
};

const getDisbursementAmount = (collateral) => {
    if (!filterDate.value) {
        return collateral.disbursement || 0;
    }
    const disbursementDate = collateral.disbursement_date ? new Date(collateral.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (collateral.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (collateral, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(collateral.beginning_balance || 0);
    }
    
    let balance = parseFloat(collateral.beginning_balance || 0);
    
    const collectionDate = collateral.collection_date ? new Date(collateral.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(collateral.collection || 0);
    }
    
    const disbursementDate = collateral.disbursement_date ? new Date(collateral.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(collateral.disbursement || 0);
    }
    
    return balance;
};

const openModal = () => {
    selectedCollateral.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedCollateral.value = null;
};

const openEditModal = (collateral) => {
    selectedCollateral.value = collateral;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedCollateral.value = null;
};

const totalBeginningBalance = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + getRollingBeginningBalance(collateral, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + parseFloat(getCollectionAmount(collateral) || 0);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + parseFloat(getDisbursementAmount(collateral) || 0);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        const beginning = getRollingBeginningBalance(collateral, filterDate.value);
        const collection = parseFloat(getCollectionAmount(collateral) || 0);
        const disbursement = parseFloat(getDisbursementAmount(collateral) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const handleDragStart = (index, event) => {
    draggedIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};

const handleDrop = (targetIndex) => {
    if (draggedIndex.value !== null && draggedIndex.value !== targetIndex) {
        const temp = props.collaterals[draggedIndex.value];
        props.collaterals[draggedIndex.value] = props.collaterals[targetIndex];
        props.collaterals[targetIndex] = temp;
    }
    draggedIndex.value = null;
};

const handleDragEnd = () => {
    draggedIndex.value = null;
};

const deleteCollateral = async (collateral) => {
    const result = await Swal.fire({
        title: 'Delete Collateral?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Collateral:</strong> ${collateral.collateral}</p>
                <p class="mb-3"><strong>Account:</strong> ${collateral.account_number}</p>
                <p class="text-red-600 text-sm"><strong>⚠️ This action cannot be undone.</strong></p>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    if (result.isConfirmed) {
        router.delete(`/treasury/collateral/${collateral.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Collateral has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete collateral. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const getDaysRemaining = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    
    const diffTime = date - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    return diffDays;
};

const isMaturityActionVisible = (dateString) => {
    const daysRemaining = getDaysRemaining(dateString);
    return daysRemaining !== null && daysRemaining <= 30;
};

const isOverdueOrDueToday = (dateString) => {
    const daysRemaining = getDaysRemaining(dateString);
    return daysRemaining !== null && daysRemaining <= 0;
};

const formatMaturityDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    
    const diffTime = date - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    const formattedDate = new Intl.DateTimeFormat('en-US', options).format(date);
    
    if (diffDays < 0) {
        return `${formattedDate} (Overdue by ${Math.abs(diffDays)} days)`;
    } else if (diffDays === 0) {
        return `${formattedDate} (Due Today)`;
    } else if (diffDays <= 30) {
        return `${formattedDate} (${diffDays} days remaining)`;
    }
    
    return formattedDate;
};

const isCreatedToday = (createdAtString) => {
    if (!createdAtString) return false;
    const createdDate = new Date(createdAtString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    createdDate.setHours(0, 0, 0, 0);
    
    return createdDate.getTime() === today.getTime();
};

const renewCollateral = async (collateral) => {
    const result = await Swal.fire({
        title: 'Renew Collateral?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Collateral:</strong> ${collateral.collateral}</p>
                <p class="mb-3"><strong>Account:</strong> ${collateral.account_number}</p>
                <p class="text-green-600 text-sm"><strong>✓ This will renew the collateral account.</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Renew',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    if (result.isConfirmed) {
        router.post(`/treasury/collateral/${collateral.id}/renew`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Collateral has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to renew collateral. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const withdrawCollateral = async (collateral) => {
    const result = await Swal.fire({
        title: 'Withdraw Collateral?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Collateral:</strong> ${collateral.collateral}</p>
                <p class="mb-3"><strong>Account:</strong> ${collateral.account_number}</p>
                <p class="text-orange-600 text-sm"><strong>✓ This will withdraw the collateral amount.</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#F97316',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Withdraw',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    if (result.isConfirmed) {
        router.post(`/treasury/collateral/${collateral.id}/withdraw`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Withdrawn!',
                    text: 'Collateral has been withdrawn successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to withdraw collateral. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};
</script>

<template>
    <TreasuryLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Collateral Accounts</h1>
                        <p class="text-gray-600 text-sm font-medium">View all collateral accounts with real-time balance tracking</p>
                    </div>
                    <button
                        @click="openModal"
                        class="flex items-center space-x-2 px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create New</span>
                    </button>
                </div>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Collateral</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by collateral name or account number..."
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Select Date</label>
                        <input
                            v-model="filterDate"
                            type="date"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasCollaterals && filteredCollaterals.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Collaterals Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first collateral account.</p>
                    <button
                        @click="openModal"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create First Collateral</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredCollaterals.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No collaterals match your search query.</p>
                </div>
            </div>

            <!-- Collaterals Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collateral Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Action</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredCollaterals.length === 0" class="border-b border-gray-200">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No collateral records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(collateral, index) in filteredCollaterals" 
                                :key="collateral.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    isOverdueOrDueToday(collateral.maturity_date) ? 'blink-red' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ collateral.collateral }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ collateral.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">
                                    {{ formatMaturityDate(collateral.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(collateral, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(collateral, filterDate) + parseFloat(getCollectionAmount(collateral)) - parseFloat(getDisbursementAmount(collateral))) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div v-if="isMaturityActionVisible(collateral.maturity_date)" class="flex items-center space-x-2">
                                        <button
                                            @click="renewCollateral(collateral)"
                                            class="px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition-all duration-200"
                                            title="Renew"
                                        >
                                            Renew
                                        </button>
                                        <button
                                            @click="withdrawCollateral(collateral)"
                                            class="px-3 py-1.5 bg-orange-500 text-white text-xs font-semibold rounded-lg hover:bg-orange-600 transition-all duration-200"
                                            title="Withdraw"
                                        >
                                            Withdraw
                                        </button>
                                    </div>
                                    <div v-else class="text-xs text-gray-500">—</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div v-if="isCreatedToday(collateral.created_at)" class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(collateral)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteCollateral(collateral)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-else class="text-xs text-gray-500">—</div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredCollaterals.length > 0">
                            <tr class="bg-yellow-50 font-bold border-t-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">{{ formatCurrency(totalBeginningBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 border-r border-gray-300">{{ formatCurrency(totalEndingBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-300">
                    <p class="text-sm text-gray-600">Total: <span class="font-semibold text-gray-900">{{ filteredCollaterals.length }}</span> collateral(s)</p>
                </div>
            </div>

            <!-- Create Modal -->
            <CreateCollateralModal :isOpen="showModal" :existingCollaterals="props.collaterals" @close="closeModal" />

            <!-- Edit Modal -->
            <EditCollateralModal :isOpen="showEditModal" :collateral="selectedCollateral" :existingCollaterals="props.collaterals" @close="closeEditModal" />
        </div>
    </TreasuryLayout>
</template>

<style scoped>
@keyframes blinkRed {
    0%, 100% {
        background-color: rgb(254, 226, 226);
    }
    50% {
        background-color: rgb(239, 68, 68);
        color: white;
    }
}

.blink-red {
    animation: blinkRed 1s infinite;
}
</style>
