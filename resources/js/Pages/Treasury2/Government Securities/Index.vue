<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import AddCollection from './AddCollection.vue';
import AddDisbursement from './AddDisbursement.vue';
import EditCollection from './EditCollection.vue';
import EditDisbursement from './EditDisbursement.vue';
import { Plus, Search, Pencil, ChevronDown, Eye, EyeOff } from 'lucide-vue-next';
import { ref, computed, onMounted, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
    governmentSecurities: {
        type: Array,
        default: () => []
    }
});

const securitiesData = ref(props.governmentSecurities);
const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showWithdrawn = ref(false);
const isAddCollectionOpen = ref(false);
const isAddDisbursementOpen = ref(false);
const isEditCollectionOpen = ref(false);
const isEditDisbursementOpen = ref(false);
const selectedSecurity = ref(null);
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

onMounted(() => {
    document.title = 'Government Securities Management - Daily Deposit';
    securitiesData.value = props.governmentSecurities;
    
    // Set today's date by default
    const today = new Date().toISOString().split('T')[0];
    filterDate.value = today;
    updateUrlWithDate(today);
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

const getCollectionAmount = (security) => {
    if (!filterDate.value) {
        return security.collection || 0;
    }
    const collectionDate = security.collection_date ? new Date(security.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (security.collection || 0) : 0;
};

const getDisbursementAmount = (security) => {
    if (!filterDate.value) {
        return security.disbursement || 0;
    }
    const disbursementDate = security.disbursement_date ? new Date(security.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (security.disbursement || 0) : 0;
};

// Check if the filtered date is today
const isToday = () => {
    const today = new Date().toISOString().split('T')[0];
    return filterDate.value === today;
};

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (security, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(security.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(security.beginning_balance || 0);
    
    // Add all collection amounts from dates BEFORE the selected date
    const collectionDate = security.collection_date ? new Date(security.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(security.collection || 0);
    }
    
    // Subtract all disbursement amounts from dates BEFORE the selected date
    const disbursementDate = security.disbursement_date ? new Date(security.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(security.disbursement || 0);
    }
    
    return balance;
};

const filteredSecurities = computed(() => {
    let filtered = securitiesData.value;
    
    // Filter by withdrawn status
    if (showWithdrawn.value) {
        filtered = filtered.filter(security => security.maturity_date === null);
    } else {
        filtered = filtered.filter(security => security.maturity_date !== null);
    }
    
    // Filter by search query
    if (!searchQuery.value.trim()) {
        return filtered;
    }
    
    const query = searchQuery.value.toLowerCase();
    return filtered.filter(security => 
        security.government_security_name.toLowerCase().includes(query) ||
        security.reference_number.toLowerCase().includes(query)
    );
});

const totalBeginningBalance = computed(() => {
    return filteredSecurities.value.reduce((sum, security) => {
        return sum + getRollingBeginningBalance(security, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredSecurities.value.reduce((sum, security) => {
        return sum + (parseFloat(getCollectionAmount(security)) || 0);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredSecurities.value.reduce((sum, security) => {
        return sum + (parseFloat(getDisbursementAmount(security)) || 0);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredSecurities.value.reduce((sum, security) => {
        const beginning = getRollingBeginningBalance(security, filterDate.value);
        const collection = parseFloat(getCollectionAmount(security) || 0);
        const disbursement = parseFloat(getDisbursementAmount(security) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const handleCollection = (security) => {
    selectedSecurity.value = security;
    isAddCollectionOpen.value = true;
};

const handleCollectionSubmit = async (collectionData) => {
    try {
        // Find and update the security in the local data
        const index = securitiesData.value.findIndex(s => s.id === collectionData.governmentSecurity_id);
        if (index !== -1) {
            securitiesData.value[index].collection += collectionData.amount;
            securitiesData.value[index].ending_balance = 
                securitiesData.value[index].beginning_balance + 
                securitiesData.value[index].collection - 
                securitiesData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Collection of ₱${collectionData.amount.toFixed(2)} added to ${selectedSecurity.value.government_security_name}`,
            icon: 'success',
            confirmButtonColor: '#10B981'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update security data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddCollectionOpen.value = false;
    selectedSecurity.value = null;
};

const handleDisbursement = (security) => {
    selectedSecurity.value = security;
    isAddDisbursementOpen.value = true;
};

const handleDisbursementSubmit = async (disbursementData) => {
    try {
        // Find and update the security in the local data
        const index = securitiesData.value.findIndex(s => s.id === disbursementData.governmentSecurity_id);
        if (index !== -1) {
            securitiesData.value[index].disbursement += disbursementData.amount;
            securitiesData.value[index].ending_balance = 
                securitiesData.value[index].beginning_balance + 
                securitiesData.value[index].collection - 
                securitiesData.value[index].disbursement;
        }
        
        Swal.fire({
            title: 'Success!',
            text: `Disbursement of ₱${disbursementData.amount.toFixed(2)} added to ${selectedSecurity.value.government_security_name}`,
            icon: 'success',
            confirmButtonColor: '#EF4444'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: 'Failed to update security data',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isAddDisbursementOpen.value = false;
    selectedSecurity.value = null;
};

const handleEditCollection = (security) => {
    selectedSecurity.value = security;
    isEditCollectionOpen.value = true;
};

const handleEditDisbursement = (security) => {
    selectedSecurity.value = security;
    isEditDisbursementOpen.value = true;
};

const toggleActionMenu = (securityId) => {
    openActionMenu.value = openActionMenu.value === securityId ? null : securityId;
};

const handleEditCollectionSubmit = async (collectionData) => {
    try {
        await axios.put(`/treasury2/government-security/${collectionData.government_security_id}/collection`, {
            amount: collectionData.amount,
            date: filterDate.value || new Date().toISOString().split('T')[0]
        });
        const index = securitiesData.value.findIndex(s => s.id === collectionData.government_security_id);
        if (index !== -1) {
            securitiesData.value[index].collection = collectionData.amount;
            securitiesData.value[index].ending_balance = 
                securitiesData.value[index].beginning_balance + 
                securitiesData.value[index].collection - 
                securitiesData.value[index].disbursement;
        }
        Swal.fire({
            title: 'Success!',
            text: `Collection updated to ₱${collectionData.amount.toFixed(2)}`,
            icon: 'success',
            confirmButtonColor: '#3B82F6'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to update collection',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isEditCollectionOpen.value = false;
    selectedSecurity.value = null;
};

const handleEditDisbursementSubmit = async (disbursementData) => {
    try {
        await axios.put(`/treasury2/government-security/${disbursementData.government_security_id}/disbursement`, {
            amount: disbursementData.amount,
            date: filterDate.value || new Date().toISOString().split('T')[0]
        });
        const index = securitiesData.value.findIndex(s => s.id === disbursementData.government_security_id);
        if (index !== -1) {
            securitiesData.value[index].disbursement = disbursementData.amount;
            securitiesData.value[index].ending_balance = 
                securitiesData.value[index].beginning_balance + 
                securitiesData.value[index].collection - 
                securitiesData.value[index].disbursement;
        }
        Swal.fire({
            title: 'Success!',
            text: `Disbursement updated to ₱${disbursementData.amount.toFixed(2)}`,
            icon: 'success',
            confirmButtonColor: '#EF4444'
        }).then(() => {
            window.location.reload();
        });
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || 'Failed to update disbursement',
            icon: 'error',
            confirmButtonColor: '#EF4444'
        });
    }
    isEditDisbursementOpen.value = false;
    selectedSecurity.value = null;
};
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Government Securities Management</h1>
                <p class="text-gray-600 text-sm font-medium">Manage government securities portfolio with daily balance tracking</p>
            </div>

            <!-- Search Bar and Date Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Government Security</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by government security name or reference number..."
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

            <!-- Government Securities Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Government Security Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Reference Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredSecurities.length === 0" class="border-b border-gray-200">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No government securities found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(security, index) in filteredSecurities" 
                                :key="security.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ security.government_security_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ security.reference_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(security, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(getCollectionAmount(security)) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(getDisbursementAmount(security)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(security, filterDate) + parseFloat(getCollectionAmount(security)) - parseFloat(getDisbursementAmount(security))) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div v-if="isToday()" class="relative inline-block">
                                        <button
                                            @click="toggleActionMenu(security.id)"
                                            class="flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-semibold text-sm rounded-lg transition-all duration-200 border border-gray-300"
                                        >
                                            <span>Actions</span>
                                            <ChevronDown :class="['h-4 w-4 transition-transform', openActionMenu === security.id ? 'rotate-180' : '']" />
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div v-if="openActionMenu === security.id" class="absolute right-0 mt-2 w-80 bg-white border-2 border-gray-300 rounded-lg shadow-xl z-50 py-2">
                                            <!-- Collection -->
                                            <button
                                                @click="handleCollection(security); openActionMenu = null;"
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
                                                v-if="getCollectionAmount(security) > 0"
                                                @click="handleEditCollection(security); openActionMenu = null;"
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
                                                @click="handleDisbursement(security); openActionMenu = null;"
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
                                                v-if="getDisbursementAmount(security) > 0"
                                                @click="handleEditDisbursement(security); openActionMenu = null;"
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
                        <tfoot v-if="filteredSecurities.length > 0">
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
        :government-security="selectedSecurity"
        :filter-date="filterDate"
        @close="isAddCollectionOpen = false"
        @submit="handleCollectionSubmit"
    />

    <!-- Add Disbursement Modal -->
    <AddDisbursement 
        :is-open="isAddDisbursementOpen" 
        :government-security="selectedSecurity"
        :filter-date="filterDate"
        @close="isAddDisbursementOpen = false"
        @submit="handleDisbursementSubmit"
    />

    <!-- Edit Collection Modal -->
    <EditCollection 
        :is-open="isEditCollectionOpen" 
        :government-security="selectedSecurity"
        @close="isEditCollectionOpen = false"
        @submit="handleEditCollectionSubmit"
    />

    <!-- Edit Disbursement Modal -->
    <EditDisbursement 
        :is-open="isEditDisbursementOpen" 
        :government-security="selectedSecurity"
        @close="isEditDisbursementOpen = false"
        @submit="handleEditDisbursementSubmit"
    />
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
