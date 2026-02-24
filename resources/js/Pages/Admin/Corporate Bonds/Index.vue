<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ViewCorporateBondModal from '@/Pages/Treasury/Corporate Bonds/View.vue';
import { Search, Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    corporateBonds: {
        type: Array,
        default: () => []
    }
});

const corporateBodsData = ref(props.corporateBonds);
const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showWithdrawn = ref(false);
const showViewModal = ref(false);
const selectedBond = ref(null);

onMounted(() => {
    document.title = 'Corporate Bonds - Daily Deposit';
    corporateBodsData.value = props.corporateBonds;
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

const formatMaturityDate = (dateString) => {
    if (!dateString) return '✓ Withdrawn';
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

const filteredCorporateBonds = computed(() => {
    let bonds = corporateBodsData.value;
    
    // Filter by withdrawn status - show ONLY withdrawn when toggled
    if (showWithdrawn.value) {
        bonds = bonds.filter(bond => bond.maturity_date === null);
    } else {
        bonds = bonds.filter(bond => bond.maturity_date !== null);
    }
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        bonds = bonds.filter(bond => 
            bond.corporate_bond_name.toLowerCase().includes(query) ||
            bond.account_number.toLowerCase().includes(query)
        );
    }
    
    return bonds;
});

const getCollectionAmount = (bond) => {
    if (!filterDate.value) {
        return bond.collection || 0;
    }
    const collectionDate = bond.collection_date ? new Date(bond.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (bond.collection || 0) : 0;
};

const getDisbursementAmount = (bond) => {
    if (!filterDate.value) {
        return bond.disbursement || 0;
    }
    const disbursementDate = bond.disbursement_date ? new Date(bond.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (bond.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (bond, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(bond.beginning_balance || 0);
    }
    
    let balance = parseFloat(bond.beginning_balance || 0);
    
    const collectionDate = bond.collection_date ? new Date(bond.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(bond.collection || 0);
    }
    
    const disbursementDate = bond.disbursement_date ? new Date(bond.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(bond.disbursement || 0);
    }
    
    return balance;
};

const totalBeginningBalance = computed(() => {
    return filteredCorporateBonds.value.reduce((sum, bond) => {
        return sum + getRollingBeginningBalance(bond, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredCorporateBonds.value.reduce((sum, bond) => {
        return sum + getCollectionAmount(bond);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredCorporateBonds.value.reduce((sum, bond) => {
        return sum + getDisbursementAmount(bond);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredCorporateBonds.value.reduce((sum, bond) => {
        const beginning = getRollingBeginningBalance(bond, filterDate.value);
        const collection = parseFloat(getCollectionAmount(bond) || 0);
        const disbursement = parseFloat(getDisbursementAmount(bond) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const viewCorporateBond = (bond) => {
    selectedBond.value = bond;
    showViewModal.value = true;
};
</script>

<template>
    <AdminLayout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Corporate Bonds</h1>
                <p class="text-gray-600 text-sm font-medium">View all corporate bond investments with real-time balance tracking</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Corporate Bonds</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by bond name or account number..."
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

            <!-- Corporate Bonds Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Bond Name</th>
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
                            <tr v-if="filteredCorporateBonds.length === 0" class="border-b border-gray-200">
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                    No corporate bond records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(bond, index) in filteredCorporateBonds" 
                                :key="bond.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ bond.corporate_bond_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ bond.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatDate(bond.acquisition_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ formatMaturityDate(bond.maturity_date) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(bond, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(bond)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(bond)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(bond, filterDate) + parseFloat(getCollectionAmount(bond)) - parseFloat(getDisbursementAmount(bond))) }}</td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button
                                        @click="viewCorporateBond(bond)"
                                        class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-150"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredCorporateBonds.length > 0">
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

            <!-- View Corporate Bond Modal -->
            <ViewCorporateBondModal 
                :is-open="showViewModal"
                :corporate-bond="selectedBond"
                @close="showViewModal = false"
            />
        </div>
    </AdminLayout>
</template>
