<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import AddCollection from './AddCollection.vue';
import AddDisbursement from './AddDisbursement.vue';
import { Search, Plus } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    investments: {
        type: Array,
        default: () => []
    }
});

const investmentsData = ref(props.investments);
const highlightId = ref(null);

onMounted(() => {
    document.title = 'Investment Management - Daily Deposit';
    investmentsData.value = props.investments;
    
    // Set today's date by default
    const today = new Date().toISOString().split('T')[0];
    filterDate.value = today;
    updateUrlWithDate(today);
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const isAddCollectionOpen = ref(false);
const isAddDisbursementOpen = ref(false);
const selectedInvestment = ref(null);

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

const filteredInvestments = computed(() => {
    let filtered = investmentsData.value;
    
    // Filter by search query only
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(investment => 
            investment.investment_name.toLowerCase().includes(query) ||
            investment.reference_number.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

const getCollectionAmount = (investment) => {
    if (!filterDate.value) {
        return investment.collection || 0;
    }
    const collectionDate = investment.collection_date ? new Date(investment.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (investment.collection || 0) : 0;
};

const getDisbursementAmount = (investment) => {
    if (!filterDate.value) {
        return investment.disbursement || 0;
    }
    const disbursementDate = investment.disbursement_date ? new Date(investment.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (investment.disbursement || 0) : 0;
};

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (investment, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(investment.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(investment.beginning_balance || 0);
    
    // Add all collection amounts from dates BEFORE the selected date
    const collectionDate = investment.collection_date ? new Date(investment.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(investment.collection || 0);
    }
    
    // Subtract all disbursement amounts from dates BEFORE the selected date
    const disbursementDate = investment.disbursement_date ? new Date(investment.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(investment.disbursement || 0);
    }
    
    return balance;
};

const totalBeginningBalance = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + getRollingBeginningBalance(investment, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + getCollectionAmount(investment);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + getDisbursementAmount(investment);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        const beginning = getRollingBeginningBalance(investment, filterDate.value);
        const collection = parseFloat(getCollectionAmount(investment) || 0);
        const disbursement = parseFloat(getDisbursementAmount(investment) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const handleCollection = (investment) => {
    selectedInvestment.value = investment;
    isAddCollectionOpen.value = true;
};

const handleCollectionSubmit = async (collectionData) => {
    try {
        // Find and update the investment in the local data
        const index = investmentsData.value.findIndex(i => i.id === collectionData.investment_id);
        if (index !== -1) {
            investmentsData.value[index].collection += collectionData.amount;
            investmentsData.value[index].ending_balance = 
                investmentsData.value[index].beginning_balance + 
                investmentsData.value[index].collection - 
                investmentsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Collection of ₱${collectionData.amount.toFixed(2)} added to ${selectedInvestment.value.investment_name}`,
            icon: 'success',
            confirmButtonColor: '#10B981'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update investment data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddCollectionOpen.value = false;
    selectedInvestment.value = null;
};

const handleDisbursementSubmit = async (disbursementData) => {
    try {
        // Find and update the investment in the local data
        const index = investmentsData.value.findIndex(i => i.id === disbursementData.investment_id);
        if (index !== -1) {
            investmentsData.value[index].disbursement += disbursementData.amount;
            investmentsData.value[index].ending_balance = 
                investmentsData.value[index].beginning_balance + 
                investmentsData.value[index].collection - 
                investmentsData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Disbursement of ₱${disbursementData.amount.toFixed(2)} added to ${selectedInvestment.value.investment_name}`,
            icon: 'success',
            confirmButtonColor: '#EF4444'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update investment data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddDisbursementOpen.value = false;
    selectedInvestment.value = null;
};

const handleDisbursement = (investment) => {
    selectedInvestment.value = investment;
    isAddDisbursementOpen.value = true;
};
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Investment Management</h1>
                <p class="text-gray-600 text-sm font-medium">Oversee investment portfolio with real-time balance and maturity tracking</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Investments</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by investment name or reference number..."
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

            <!-- Investment Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Investment Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Reference Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredInvestments.length === 0" class="border-b border-gray-200">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    No investment records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(investment, index) in filteredInvestments" 
                                :key="investment.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ investment.investment_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ investment.reference_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(investment)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(investment)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment, filterDate) + parseFloat(getCollectionAmount(investment)) - parseFloat(getDisbursementAmount(investment))) }}</td>
                                <td class="px-6 py-4 text-sm space-x-2 flex">
                                    <button
                                        @click="handleCollection(investment)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Collection</span>
                                    </button>
                                    <button
                                        @click="handleDisbursement(investment)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded hover:bg-red-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Disbursement</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredInvestments.length > 0">
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
        :investment="selectedInvestment"
        :filter-date="filterDate"
        @close="isAddCollectionOpen = false"
        @submit="handleCollectionSubmit"
    />

    <!-- Add Disbursement Modal -->
    <AddDisbursement 
        :is-open="isAddDisbursementOpen" 
        :investment="selectedInvestment"
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
