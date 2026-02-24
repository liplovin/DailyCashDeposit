<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ViewTimeDepositModal from '@/Pages/Treasury/Time Deposit/View.vue';
import { Search } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    timeDeposits: {
        type: Array,
        default: () => []
    }
});

const timeDepositsData = ref(props.timeDeposits);
const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showViewModal = ref(false);
const selectedTimeDeposit = ref(null);

onMounted(() => {
    document.title = 'Time Deposit - Daily Deposit';
    timeDepositsData.value = props.timeDeposits;
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

const getCollectionAmount = (deposit) => {
    if (!filterDate.value) {
        return deposit.collection || 0;
    }
    const collectionDate = deposit.collection_date ? new Date(deposit.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (deposit.collection || 0) : 0;
};

const getDisbursementAmount = (deposit) => {
    if (!filterDate.value) {
        return deposit.disbursement || 0;
    }
    const disbursementDate = deposit.disbursement_date ? new Date(deposit.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (deposit.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (deposit, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(deposit.beginning_balance || 0);
    }
    
    let balance = parseFloat(deposit.beginning_balance || 0);
    
    const collectionDate = deposit.collection_date ? new Date(deposit.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(deposit.collection || 0);
    }
    
    const disbursementDate = deposit.disbursement_date ? new Date(deposit.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(deposit.disbursement || 0);
    }
    
    return balance;
};

const filteredDeposits = computed(() => {
    if (!searchQuery.value.trim()) {
        return timeDepositsData.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return timeDepositsData.value.filter(deposit => 
        deposit.time_deposit_name.toLowerCase().includes(query) ||
        deposit.account_number.toLowerCase().includes(query)
    );
});

const totalBeginningBalance = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + getRollingBeginningBalance(deposit, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + getCollectionAmount(deposit);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + getDisbursementAmount(deposit);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        const beginning = getRollingBeginningBalance(deposit, filterDate.value);
        const collection = parseFloat(getCollectionAmount(deposit) || 0);
        const disbursement = parseFloat(getDisbursementAmount(deposit) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const viewTimeDeposit = (deposit) => {
    selectedTimeDeposit.value = deposit;
    showViewModal.value = true;
};
</script>

<template>
    <AdminLayout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Time Deposit Accounts</h1>
                <p class="text-gray-600 text-sm font-medium">View all time deposit accounts with daily balance tracking</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Time Deposit</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by time deposit name or account number..."
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

            <!-- Time Deposits Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Time Deposit Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
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
                            <tr v-if="filteredDeposits.length === 0" class="border-b border-gray-200">
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    No time deposits found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(deposit, index) in filteredDeposits" 
                                :key="deposit.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ deposit.time_deposit_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ deposit.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatDate(deposit.acquisition_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatDate(deposit.maturity_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(deposit, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(deposit)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(deposit)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(deposit, filterDate) + parseFloat(getCollectionAmount(deposit)) - parseFloat(getDisbursementAmount(deposit))) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button
                                        @click="viewTimeDeposit(deposit)"
                                        class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-150"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredDeposits.length > 0">
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

            <!-- View Time Deposit Modal -->
            <ViewTimeDepositModal 
                v-if="showViewModal && selectedTimeDeposit"
                :time-deposit="selectedTimeDeposit"
                @close="showViewModal = false"
            />
        </div>
    </AdminLayout>
</template>
