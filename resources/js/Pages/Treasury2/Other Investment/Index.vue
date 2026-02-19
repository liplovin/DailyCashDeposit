<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import { Plus, Search } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import Swal from 'sweetalert2';
import AddCollection from './AddCollection.vue';
import AddDisbursement from './AddDisbursement.vue';
import axios from 'axios';

const props = defineProps({
    otherInvestments: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const filterDate = ref('');
const showAddCollectionModal = ref(false);
const showAddDisbursementModal = ref(false);
const selectedInvestment = ref(null);
const otherInvestments = ref([]);

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

onMounted(() => {
    document.title = 'Other Investment Management - Daily Deposit';
    otherInvestments.value = props.otherInvestments;
    
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

// Get collection amount for selected date
const getCollectionAmount = (investment) => {
    if (!investment.collection_date || !filterDate.value) {
        return parseFloat(investment.collection || 0);
    }
    
    const collectionDate = new Date(investment.collection_date).toISOString().split('T')[0];
    if (collectionDate === filterDate.value) {
        return parseFloat(investment.collection || 0);
    }
    return 0;
};

// Get disbursement amount for selected date
const getDisbursementAmount = (investment) => {
    if (!investment.disbursement_date || !filterDate.value) {
        return parseFloat(investment.disbursement || 0);
    }
    
    const disbursementDate = new Date(investment.disbursement_date).toISOString().split('T')[0];
    if (disbursementDate === filterDate.value) {
        return parseFloat(investment.disbursement || 0);
    }
    return 0;
};

// Calculate rolling beginning balance
const getRollingBeginningBalance = (investment) => {
    if (!filterDate.value) {
        return parseFloat(investment.beginning_balance || 0);
    }
    
    let balance = parseFloat(investment.beginning_balance || 0);
    
    // Add collections before the selected date
    const collectionDate = investment.collection_date 
        ? new Date(investment.collection_date).toISOString().split('T')[0] 
        : null;
    if (collectionDate && collectionDate < filterDate.value) {
        balance += parseFloat(investment.collection || 0);
    }
    
    // Subtract disbursements before the selected date
    const disbursementDate = investment.disbursement_date 
        ? new Date(investment.disbursement_date).toISOString().split('T')[0] 
        : null;
    if (disbursementDate && disbursementDate < filterDate.value) {
        balance -= parseFloat(investment.disbursement || 0);
    }
    
    return balance;
};

const handleCollection = (investment) => {
    selectedInvestment.value = investment;
    showAddCollectionModal.value = true;
};

const handleDisbursement = (investment) => {
    selectedInvestment.value = investment;
    showAddDisbursementModal.value = true;
};

const handleModalSubmit = async () => {
    try {
        // Reload page to refresh data
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    } catch (err) {
        console.error('Error:', err);
    }
};

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
    if (!searchQuery.value.trim()) {
        return otherInvestments.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return otherInvestments.value.filter(investment => 
        investment.other_investment_name.toLowerCase().includes(query) ||
        investment.account_number.toLowerCase().includes(query)
    );
});

const totalBeginningBalance = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + getRollingBeginningBalance(investment);
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
        const beginning = getRollingBeginningBalance(investment);
        const collection = getCollectionAmount(investment);
        const disbursement = getDisbursementAmount(investment);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});
</script>

<template>
    <Treasury2Layout>
        <AddCollection 
            :isOpen="showAddCollectionModal"
            :otherInvestment="selectedInvestment"
            :filterDate="filterDate"
            @close="showAddCollectionModal = false"
            @submit="handleModalSubmit"
        />
        <AddDisbursement 
            :isOpen="showAddDisbursementModal"
            :otherInvestment="selectedInvestment"
            :filterDate="filterDate"
            @close="showAddDisbursementModal = false"
            @submit="handleModalSubmit"
        />

        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Other Investment Management</h1>
                <p class="text-gray-600 text-sm font-medium">Monitor alternative investment accounts with collection and disbursement tracking</p>
            </div>

            <!-- Filter & Search Section -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Other Investment</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by other investment name or account number..."
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

            <!-- Other Investment Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Other Investment Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
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
                                    No other investments found.
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
                                        {{ investment.other_investment_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ investment.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(investment)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(investment)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment) + getCollectionAmount(investment) - getDisbursementAmount(investment)) }}</td>

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
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
