<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateOtherInvestmentModal from './Create.vue';
import EditOtherInvestmentModal from './Edit.vue';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    otherInvestments: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showModal = ref(false);
const showEditModal = ref(false);
const selectedOtherInvestment = ref(null);

onMounted(() => {
    document.title = 'Other Investments Management - Daily Deposit';
});

const updateUrlWithDate = (date) => {
    const params = new URLSearchParams(window.location.search);
    params.set('date', date);
    window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);
};

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

const filteredOtherInvestments = computed(() => {
    let filtered = props.otherInvestments;
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item.other_investment_name.toLowerCase().includes(query) ||
            item.account_number.toLowerCase().includes(query)
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

const getRollingBeginningBalance = (item, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(item.beginning_balance || 0);
    }
    
    let balance = parseFloat(item.beginning_balance || 0);
    
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(item.collection || 0);
    }
    
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(item.disbursement || 0);
    }
    
    return balance;
};

const hasOtherInvestments = computed(() => filteredOtherInvestments.value && filteredOtherInvestments.value.length > 0);

const totalBeginningBalance = computed(() => {
    return filteredOtherInvestments.value.reduce((sum, item) => {
        return sum + getRollingBeginningBalance(item, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredOtherInvestments.value.reduce((sum, item) => {
        return sum + getCollectionAmount(item);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredOtherInvestments.value.reduce((sum, item) => {
        return sum + getDisbursementAmount(item);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredOtherInvestments.value.reduce((sum, item) => {
        const beginning = getRollingBeginningBalance(item, filterDate.value);
        const collection = parseFloat(getCollectionAmount(item) || 0);
        const disbursement = parseFloat(getDisbursementAmount(item) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const openModal = () => {
    selectedOtherInvestment.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedOtherInvestment.value = null;
};

const openEditModal = (investment) => {
    selectedOtherInvestment.value = investment;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedOtherInvestment.value = null;
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

const deleteOtherInvestment = async (investment) => {
    const result = await Swal.fire({
        title: 'Delete Other Investment?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Other Investment:</strong> ${investment.other_investment_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${investment.account_number}</p>
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
        router.delete(`/treasury/other-investment/${investment.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Other Investment has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete other investment. Please try again.',
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
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Other Investments Management</h1>
                        <p class="text-gray-600 text-sm font-medium">View all other investment records with real-time balances and transactions</p>
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
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search</label>
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

            <!-- Empty State -->
            <div v-if="!hasOtherInvestments && filteredOtherInvestments.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Other Investments Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first other investment.</p>
                    <button
                        @click="openModal"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create First Other Investment</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredOtherInvestments.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No other investments match your search query.</p>
                </div>
            </div>

            <!-- Other Investments Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Other Investment Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(investment, index) in filteredOtherInvestments" 
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
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ investment.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-200">
                                    {{ formatMaturityDate(investment.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(investment, filterDate) + parseFloat(getCollectionAmount(investment)) - parseFloat(getDisbursementAmount(investment))) }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(investment)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteOtherInvestment(investment)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredOtherInvestments.length > 0">
                            <tr class="bg-yellow-50 font-bold border-b-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">{{ formatCurrency(totalBeginningBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 border-r border-gray-300">{{ formatCurrency(totalEndingBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Create Modal -->
            <CreateOtherInvestmentModal :isOpen="showModal" :existingOtherInvestments="props.otherInvestments" @close="closeModal" />

            <!-- Edit Modal -->
            <EditOtherInvestmentModal :isOpen="showEditModal" :otherInvestment="selectedOtherInvestment" :existingOtherInvestments="props.otherInvestments" @close="closeEditModal" />
        </div>
    </TreasuryLayout>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
