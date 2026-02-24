<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import AddCollection from './AddCollection.vue';
import AddDisbursement from './AddDisbursement.vue';
import EditCollection from './EditCollection.vue';
import EditDisbursement from './EditDisbursement.vue';
import { Search, Plus, Pencil, ChevronDown, Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    cashInfusions: {
        type: Array,
        default: () => []
    }
});

const cashInfusionsData = ref(props.cashInfusions);
const highlightId = ref(null);

onMounted(() => {
    document.title = 'Cash Infusion Management - Daily Deposit';
    cashInfusionsData.value = props.cashInfusions;
    
    // Set today's date by default
    const today = new Date().toISOString().split('T')[0];
    filterDate.value = today;
    updateUrlWithDate(today);
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showWithdrawn = ref(false);
const isAddCollectionOpen = ref(false);
const isAddDisbursementOpen = ref(false);
const isEditCollectionOpen = ref(false);
const isEditDisbursementOpen = ref(false);
const selectedCashInfusion = ref(null);
const openActionMenu = ref(null);

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

const filteredCashInfusions = computed(() => {
    let filtered = cashInfusionsData.value;
    
    // Filter by withdrawn status
    if (showWithdrawn.value) {
        filtered = filtered.filter(infusion => infusion.maturity_date === null);
    } else {
        filtered = filtered.filter(infusion => infusion.maturity_date !== null);
    }
    
    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(infusion => 
            infusion.cash_infusion_name.toLowerCase().includes(query) ||
            infusion.account_number.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

const getCollectionAmount = (infusion) => {
    if (!filterDate.value) {
        return infusion.collection || 0;
    }
    const collectionDate = infusion.collection_date ? new Date(infusion.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (infusion.collection || 0) : 0;
};

const getDisbursementAmount = (infusion) => {
    if (!filterDate.value) {
        return infusion.disbursement || 0;
    }
    const disbursementDate = infusion.disbursement_date ? new Date(infusion.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (infusion.disbursement || 0) : 0;
};

// Check if the filtered date is today
const isToday = () => {
    const today = new Date().toISOString().split('T')[0];
    return filterDate.value === today;
};

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (infusion, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(infusion.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(infusion.beginning_balance || 0);
    
    // Add all collection amounts from dates BEFORE the selected date
    const collectionDate = infusion.collection_date ? new Date(infusion.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(infusion.collection || 0);
    }
    
    // Subtract all disbursement amounts from dates BEFORE the selected date
    const disbursementDate = infusion.disbursement_date ? new Date(infusion.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(infusion.disbursement || 0);
    }
    
    return balance;
};

const totalBeginningBalance = computed(() => {
    return filteredCashInfusions.value.reduce((sum, infusion) => {
        return sum + getRollingBeginningBalance(infusion, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredCashInfusions.value.reduce((sum, infusion) => {
        return sum + (parseFloat(getCollectionAmount(infusion)) || 0);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredCashInfusions.value.reduce((sum, infusion) => {
        return sum + (parseFloat(getDisbursementAmount(infusion)) || 0);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredCashInfusions.value.reduce((sum, infusion) => {
        const beginning = getRollingBeginningBalance(infusion, filterDate.value);
        const collection = parseFloat(getCollectionAmount(infusion) || 0);
        const disbursement = parseFloat(getDisbursementAmount(infusion) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const handleCollection = (infusion) => {
    selectedCashInfusion.value = infusion;
    isAddCollectionOpen.value = true;
};

const handleCollectionSubmit = async (collectionData) => {
    try {
        // Find and update the infusion in the local data
        const index = cashInfusionsData.value.findIndex(i => i.id === collectionData.cashInfusion_id);
        if (index !== -1) {
            cashInfusionsData.value[index].collection += collectionData.amount;
            cashInfusionsData.value[index].ending_balance = 
                cashInfusionsData.value[index].beginning_balance + 
                cashInfusionsData.value[index].collection - 
                cashInfusionsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Collection of ₱${collectionData.amount.toFixed(2)} added to ${selectedCashInfusion.value.cash_infusion_name}`,
            icon: 'success',
            confirmButtonColor: '#10B981'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update cash infusion data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddCollectionOpen.value = false;
    selectedCashInfusion.value = null;
};

const handleDisbursementSubmit = async (disbursementData) => {
    try {
        // Find and update the infusion in the local data
        const index = cashInfusionsData.value.findIndex(i => i.id === disbursementData.cashInfusion_id);
        if (index !== -1) {
            cashInfusionsData.value[index].disbursement += disbursementData.amount;
            cashInfusionsData.value[index].ending_balance = 
                cashInfusionsData.value[index].beginning_balance + 
                cashInfusionsData.value[index].collection - 
                cashInfusionsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Disbursement of ₱${disbursementData.amount.toFixed(2)} added to ${selectedCashInfusion.value.cash_infusion_name}`,
            icon: 'success',
            confirmButtonColor: '#EF4444'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update cash infusion data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddDisbursementOpen.value = false;
    selectedCashInfusion.value = null;
};

const handleDisbursement = (infusion) => {
    selectedCashInfusion.value = infusion;
    isAddDisbursementOpen.value = true;
};

const handleEditCollection = (infusion) => {
    selectedCashInfusion.value = infusion;
    isEditCollectionOpen.value = true;
};

const handleEditDisbursement = (infusion) => {
    selectedCashInfusion.value = infusion;
    isEditDisbursementOpen.value = true;
};

const toggleActionMenu = (infusionId) => {
    openActionMenu.value = openActionMenu.value === infusionId ? null : infusionId;
};

const handleEditCollectionSubmit = async (collectionData) => {
    try {
        await axios.put(`/treasury2/cash-infusion/${collectionData.module_id}/collection`, {
            amount: collectionData.amount
        });
        isEditCollectionOpen.value = false;
        window.location.reload();
    } catch (err) {
        Swal.fire('Error', 'Failed to update collection', 'error');
    }
};

const handleEditDisbursementSubmit = async (disbursementData) => {
    try {
        await axios.put(`/treasury2/cash-infusion/${disbursementData.module_id}/disbursement`, {
            amount: disbursementData.amount
        });
        isEditDisbursementOpen.value = false;
        window.location.reload();
    } catch (err) {
        Swal.fire('Error', 'Failed to update disbursement', 'error');
    }
};
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Cash Infusion Management</h1>
                <p class="text-gray-600 text-sm font-medium">Monitor and manage all cash infusion investments with real-time balance tracking</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Cash Infusions</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by infusion name or account number..."
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

                    <!-- Show Withdrawn Filter -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Filter</label>
                        <button
                            @click="showWithdrawn = !showWithdrawn"
                            :class="[
                                'w-full flex items-center justify-center space-x-2 px-4 py-3 rounded-lg font-semibold transition-all duration-200 border-2',
                                showWithdrawn
                                    ? 'bg-green-100 border-green-400 text-green-700 shadow-md hover:bg-green-200'
                                    : 'bg-white border-gray-300 text-gray-700 hover:border-yellow-400 hover:bg-yellow-50'
                            ]"
                        >
                            <component :is="showWithdrawn ? Eye : EyeOff" class="h-5 w-5" />
                            <span>{{ showWithdrawn ? 'Withdrawn Only' : 'Active' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cash Infusions Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Infusion Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredCashInfusions.length === 0" class="border-b border-gray-200">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No cash infusion records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(infusion, index) in filteredCashInfusions" 
                                :key="infusion.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ infusion.cash_infusion_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ infusion.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(infusion, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(infusion)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(infusion)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(infusion, filterDate) + parseFloat(getCollectionAmount(infusion)) - parseFloat(getDisbursementAmount(infusion))) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div v-if="isToday()" class="relative inline-block">
                                        <button
                                            @click="toggleActionMenu(infusion.id)"
                                            class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-sm rounded-lg transition-all duration-200 border border-gray-300"
                                        >
                                            <span>Actions</span>
                                            <ChevronDown :class="['h-4 w-4 transition-transform', openActionMenu === infusion.id ? 'rotate-180' : '']" />
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div v-if="openActionMenu === infusion.id" class="absolute right-0 mt-2 w-80 bg-white border-2 border-gray-300 rounded-lg shadow-xl z-50 py-2">
                                            <!-- Collection -->
                                            <button
                                                @click="handleCollection(infusion); openActionMenu = null;"
                                                class="w-full px-4 py-3 text-left flex items-center gap-3 hover:bg-green-50 transition-colors duration-150 border-b border-gray-200"
                                            >
                                                <Plus class="h-5 w-5 text-green-600" />
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">Add Collection</p>
                                                    <p class="text-xs text-gray-600">Record new collection</p>
                                                </div>
                                            </button>

                                            <!-- Edit Collection -->
                                            <button
                                                v-if="getCollectionAmount(infusion) > 0"
                                                @click="handleEditCollection(infusion); openActionMenu = null;"
                                                class="w-full px-4 py-3 text-left flex items-center gap-3 hover:bg-blue-50 transition-colors duration-150 border-b border-gray-200"
                                            >
                                                <Pencil class="h-5 w-5 text-blue-600" />
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">Edit Collection</p>
                                                    <p class="text-xs text-gray-600">Modify existing collection</p>
                                                </div>
                                            </button>

                                            <!-- Disbursement -->
                                            <button
                                                @click="handleDisbursement(infusion); openActionMenu = null;"
                                                class="w-full px-4 py-3 text-left flex items-center gap-3 hover:bg-red-50 transition-colors duration-150 border-b border-gray-200"
                                            >
                                                <Plus class="h-5 w-5 text-red-600" />
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">Add Disbursement</p>
                                                    <p class="text-xs text-gray-600">Record new disbursement</p>
                                                </div>
                                            </button>

                                            <!-- Edit Disbursement -->
                                            <button
                                                v-if="getDisbursementAmount(infusion) > 0"
                                                @click="handleEditDisbursement(infusion); openActionMenu = null;"
                                                class="w-full px-4 py-3 text-left flex items-center gap-3 hover:bg-orange-50 transition-colors duration-150"
                                            >
                                                <Pencil class="h-5 w-5 text-orange-600" />
                                                <div>
                                                    <p class="font-semibold text-gray-900 text-sm">Edit Disbursement</p>
                                                    <p class="text-xs text-gray-600">Modify existing disbursement</p>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredCashInfusions.length > 0">
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
        :cash-infusion="selectedCashInfusion"
        :filter-date="filterDate"
        @close="isAddCollectionOpen = false"
        @submit="handleCollectionSubmit"
    />

    <!-- Add Disbursement Modal -->
    <AddDisbursement 
        :is-open="isAddDisbursementOpen" 
        :cash-infusion="selectedCashInfusion"
        :filter-date="filterDate"
        @close="isAddDisbursementOpen = false"
        @submit="handleDisbursementSubmit"
    />

    <!-- Edit Collection Modal -->
    <EditCollection 
        :is-open="isEditCollectionOpen" 
        :cash-infusion="selectedCashInfusion"
        @close="isEditCollectionOpen = false"
        @submit="handleEditCollectionSubmit"
    />

    <!-- Edit Disbursement Modal -->
    <EditDisbursement 
        :is-open="isEditDisbursementOpen" 
        :cash-infusion="selectedCashInfusion"
        @close="isEditDisbursementOpen = false"
        @submit="handleEditDisbursementSubmit"
    />
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
