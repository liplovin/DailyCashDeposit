<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import AddCollection from './AddCollection.vue';
import AddDisbursement from './AddDisbursement.vue';
import { Search, Plus } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    collaterals: {
        type: Array,
        default: () => []
    }
});

const collateralsData = ref(props.collaterals);
const highlightId = ref(null);

onMounted(() => {
    document.title = 'Collateral Management - Daily Deposit';
    collateralsData.value = props.collaterals;
    
    // Read date from URL query parameters
    const params = new URLSearchParams(window.location.search);
    const dateFromUrl = params.get('date');
    
    if (dateFromUrl) {
        filterDate.value = dateFromUrl;
    } else {
        // Set today's date in URL
        const today = new Date().toISOString().split('T')[0];
        filterDate.value = today;
        updateUrlWithDate(today);
    }
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const isAddCollectionOpen = ref(false);
const isAddDisbursementOpen = ref(false);
const selectedCollateral = ref(null);

// Function to update URL with date parameter
const updateUrlWithDate = (date) => {
    const params = new URLSearchParams(window.location.search);
    params.set('date', date);
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
};

// Watch for date changes and update URL
watch(filterDate, (newDate) => {
    updateUrlWithDate(newDate);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Intl.DateTimeFormat('en-US', options).format(new Date(dateString));
};

const filteredCollaterals = computed(() => {
    let filtered = collateralsData.value;
    
    // Filter by search query only
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(collateral => 
            collateral.collateral.toLowerCase().includes(query) ||
            collateral.account_number.toLowerCase().includes(query)
        );
    }
    
    return filtered;
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

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (collateral, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(collateral.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(collateral.beginning_balance || 0);
    
    // Add all collection amounts from dates BEFORE the selected date
    const collectionDate = collateral.collection_date ? new Date(collateral.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(collateral.collection || 0);
    }
    
    // Subtract all disbursement amounts from dates BEFORE the selected date
    const disbursementDate = collateral.disbursement_date ? new Date(collateral.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(collateral.disbursement || 0);
    }
    
    return balance;
};

const totalBeginningBalance = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + getRollingBeginningBalance(collateral, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + getCollectionAmount(collateral);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredCollaterals.value.reduce((sum, collateral) => {
        return sum + getDisbursementAmount(collateral);
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

const handleCollection = (collateral) => {
    selectedCollateral.value = collateral;
    isAddCollectionOpen.value = true;
};

const handleCollectionSubmit = async (collectionData) => {
    try {
        // Find and update the collateral in the local data
        const index = collateralsData.value.findIndex(c => c.id === collectionData.collateral_id);
        if (index !== -1) {
            collateralsData.value[index].collection += collectionData.amount;
            collateralsData.value[index].ending_balance = 
                collateralsData.value[index].beginning_balance + 
                collateralsData.value[index].collection - 
                collateralsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Collection of ₱${collectionData.amount.toFixed(2)} added to ${selectedCollateral.value.collateral}`,
            icon: 'success',
            confirmButtonColor: '#10B981'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update collateral data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddCollectionOpen.value = false;
    selectedCollateral.value = null;
};

const handleDisbursementSubmit = async (disbursementData) => {
    try {
        // Find and update the collateral in the local data
        const index = collateralsData.value.findIndex(c => c.id === disbursementData.collateral_id);
        if (index !== -1) {
            collateralsData.value[index].disbursement += disbursementData.amount;
            collateralsData.value[index].ending_balance = 
                collateralsData.value[index].beginning_balance + 
                collateralsData.value[index].collection - 
                collateralsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Disbursement of ₱${disbursementData.amount.toFixed(2)} added to ${selectedCollateral.value.collateral}`,
            icon: 'success',
            confirmButtonColor: '#EF4444'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update collateral data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddDisbursementOpen.value = false;
    selectedCollateral.value = null;
};

const handleDisbursement = (collateral) => {
    selectedCollateral.value = collateral;
    isAddDisbursementOpen.value = true;
};
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Collateral Management</h1>
                <p class="text-gray-600 text-sm font-medium">Monitor and manage all collateral accounts with real-time balance tracking</p>
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

            <!-- Collateral Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collateral Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredCollaterals.length === 0" class="border-b border-gray-200">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No collateral records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(collateral, index) in filteredCollaterals" 
                                :key="collateral.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ collateral.collateral }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ collateral.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(collateral, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(collateral)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(collateral)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(collateral, filterDate) + parseFloat(getCollectionAmount(collateral)) - parseFloat(getDisbursementAmount(collateral))) }}</td>
                                <td class="px-6 py-4 text-sm space-x-2 flex">
                                    <button
                                        @click="handleCollection(collateral)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Collection</span>
                                    </button>
                                    <button
                                        @click="handleDisbursement(collateral)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded hover:bg-red-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Disbursement</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredCollaterals.length > 0">
                            <tr class="bg-yellow-50 font-bold border-b-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">{{ formatCurrency(totalBeginningBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 border-r border-gray-300">{{ formatCurrency(totalCollection) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 border-r border-gray-300">{{ formatCurrency(totalDisbursement) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 border-r border-gray-300">{{ formatCurrency(totalEndingBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </Treasury2Layout>

    <!-- Add Collection Modal -->
    <AddCollection 
        :is-open="isAddCollectionOpen" 
        :collateral="selectedCollateral"
        :filter-date="filterDate"
        @close="isAddCollectionOpen = false"
        @submit="handleCollectionSubmit"
    />

    <!-- Add Disbursement Modal -->
    <AddDisbursement 
        :is-open="isAddDisbursementOpen" 
        :collateral="selectedCollateral"
        :filter-date="filterDate"
        @close="isAddDisbursementOpen = false"
        @submit="handleDisbursementSubmit"
    />
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
