<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ViewInvestmentModal from '@/Pages/Treasury/Investment/View.vue';
import { Search } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';

const props = defineProps({
    investments: {
        type: Array,
        default: () => []
    }
});

const investmentsData = ref(props.investments);
const showViewModal = ref(false);
const selectedInvestment = ref(null);

onMounted(() => {
    document.title = 'Admin - Investment Management';
    investmentsData.value = props.investments;
    
    // Set today's date by default
    const today = new Date().toISOString().split('T')[0];
    filterDate.value = today;
    updateUrlWithDate(today);
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);

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

const formatDate = (date) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('en-PH', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const filteredItems = computed(() => {
    let filtered = investmentsData.value;
    
    // Filter by search query only
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item.investment_name.toLowerCase().includes(query) ||
            (item.reference_number && item.reference_number.toLowerCase().includes(query))
        );
    }
    
    return filtered;
});

const getCollectionAmount = (item) => {
    if (!filterDate.value) {
        return item.collection || 0;
    }
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (item.collection || 0) : 0;
};

const getDisbursementAmount = (item) => {
    if (!filterDate.value) {
        return item.disbursement || 0;
    }
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (item.disbursement || 0) : 0;
};

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (item, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(item.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(item.beginning_balance || 0);
    
    // Add all collection amounts from dates BEFORE the selected date
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(item.collection || 0);
    }
    
    // Subtract all disbursement amounts from dates BEFORE the selected date
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(item.disbursement || 0);
    }
    
    return balance;
};

const totalBeginningBalance = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + getRollingBeginningBalance(item, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + getCollectionAmount(item);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + getDisbursementAmount(item);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        const beginning = getRollingBeginningBalance(item, filterDate.value);
        const collection = parseFloat(getCollectionAmount(item) || 0);
        const disbursement = parseFloat(getDisbursementAmount(item) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const viewInvestment = (investment) => {
    selectedInvestment.value = investment;
    showViewModal.value = true;
};
</script>

<template>
    <AdminLayout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Investment Management</h1>
                <p class="text-gray-600 text-sm font-medium">View all investment records with real-time balances and transactions</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search</label>
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
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Acquisition Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredItems.length === 0" class="border-b border-gray-200">
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    No investment records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(item, index) in filteredItems" 
                                :key="item.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ item.investment_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ item.reference_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatDate(item.acquisition_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatDate(item.maturity_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(item, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(item)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(item)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(item, filterDate) + parseFloat(getCollectionAmount(item)) - parseFloat(getDisbursementAmount(item))) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button
                                        @click="viewInvestment(item)"
                                        class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-150"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredItems.length > 0">
                            <tr class="bg-yellow-50 font-bold border-b-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
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

            <!-- View Investment Modal -->
            <ViewInvestmentModal 
                :is-open="showViewModal"
                :investment="selectedInvestment"
                @close="showViewModal = false"
            />
        </div>
    </AdminLayout>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
