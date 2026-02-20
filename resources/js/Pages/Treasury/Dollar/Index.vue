<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateDollarModal from './Create.vue';
import EditDollarModal from './Edit.vue';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    dollars: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showModal = ref(false);
const showEditModal = ref(false);
const selectedDollar = ref(null);

onMounted(() => {
    document.title = 'Dollar Management - Daily Deposit';
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
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const filteredDollars = computed(() => {
    let filtered = props.dollars;
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item.dollar_name.toLowerCase().includes(query) ||
            item.account_number.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

const getCollectionAmount = (dollar) => {
    if (!filterDate.value) {
        return dollar.collection || 0;
    }
    const collectionDate = dollar.collection_date ? new Date(dollar.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === filterDate.value ? (dollar.collection || 0) : 0;
};

const getDisbursementAmount = (dollar) => {
    if (!filterDate.value) {
        return dollar.disbursement || 0;
    }
    const disbursementDate = dollar.disbursement_date ? new Date(dollar.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === filterDate.value ? (dollar.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (dollar, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(dollar.beginning_balance || 0);
    }
    
    let balance = parseFloat(dollar.beginning_balance || 0);
    
    const collectionDate = dollar.collection_date ? new Date(dollar.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(dollar.collection || 0);
    }
    
    const disbursementDate = dollar.disbursement_date ? new Date(dollar.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(dollar.disbursement || 0);
    }
    
    return balance;
};

const hasDollars = computed(() => filteredDollars.value && filteredDollars.value.length > 0);

const totalBeginningBalance = computed(() => {
    return filteredDollars.value.reduce((sum, dollar) => {
        return sum + getRollingBeginningBalance(dollar, filterDate.value);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredDollars.value.reduce((sum, dollar) => {
        const beginning = getRollingBeginningBalance(dollar, filterDate.value);
        const collection = parseFloat(getCollectionAmount(dollar) || 0);
        const disbursement = parseFloat(getDisbursementAmount(dollar) || 0);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const openModal = () => {
    selectedDollar.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedDollar.value = null;
};

const openEditModal = (dollar) => {
    selectedDollar.value = dollar;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedDollar.value = null;
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

const renewDollar = async (dollar) => {
    const result = await Swal.fire({
        title: 'Renew Dollar?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Dollar:</strong> ${dollar.dollar_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${dollar.account_number}</p>
                <p class="text-green-600 text-sm"><strong>✓ This will renew the dollar account.</strong></p>
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
        router.post(`/treasury/dollar/${dollar.id}/renew`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Dollar has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to renew dollar. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const withdrawDollar = async (dollar) => {
    const result = await Swal.fire({
        title: 'Withdraw Dollar?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Dollar:</strong> ${dollar.dollar_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${dollar.account_number}</p>
                <p class="text-orange-600 text-sm"><strong>✓ This will withdraw the dollar amount.</strong></p>
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
        router.post(`/treasury/dollar/${dollar.id}/withdraw`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Withdrawn!',
                    text: 'Dollar has been withdrawn successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to withdraw dollar. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const deleteDollar = async (dollar) => {
    const result = await Swal.fire({
        title: 'Delete Dollar?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Dollar:</strong> ${dollar.dollar_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${dollar.account_number}</p>
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
        router.delete(`/treasury/dollar/${dollar.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Dollar has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete dollar. Please try again.',
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
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Dollar Management</h1>
                        <p class="text-gray-600 text-sm font-medium">View all dollar records with real-time balances and transactions</p>
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
                                placeholder="Search by dollar name or account number..."
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
            <div v-if="!hasDollars && filteredDollars.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Dollars Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first dollar.</p>
                    <button
                        @click="openModal"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create First Dollar</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredDollars.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No dollars match your search query.</p>
                </div>
            </div>

            <!-- Dollars Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Dollar Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Action</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(dollar, index) in filteredDollars" 
                                :key="dollar.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    isOverdueOrDueToday(dollar.maturity_date) ? 'blink-red' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ dollar.dollar_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ dollar.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-200">
                                    {{ formatMaturityDate(dollar.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(dollar, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(dollar, filterDate) + parseFloat(getCollectionAmount(dollar)) - parseFloat(getDisbursementAmount(dollar))) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div v-if="isMaturityActionVisible(dollar.maturity_date)" class="flex items-center space-x-2">
                                        <button
                                            @click="renewDollar(dollar)"
                                            class="px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition-all duration-200"
                                            title="Renew"
                                        >
                                            Renew
                                        </button>
                                        <button
                                            @click="withdrawDollar(dollar)"
                                            class="px-3 py-1.5 bg-orange-500 text-white text-xs font-semibold rounded-lg hover:bg-orange-600 transition-all duration-200"
                                            title="Withdraw"
                                        >
                                            Withdraw
                                        </button>
                                    </div>
                                    <div v-else class="text-xs text-gray-500">—</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(dollar)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteDollar(dollar)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredDollars.length > 0">
                            <tr class="bg-yellow-50 font-bold border-b-2 border-gray-300">
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
            </div>

            <!-- Create Modal -->
            <CreateDollarModal :isOpen="showModal" :existingDollars="props.dollars" @close="closeModal" />

            <!-- Edit Modal -->
            <EditDollarModal :isOpen="showEditModal" :dollar="selectedDollar" :existingDollars="props.dollars" @close="closeEditModal" />
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

table {
    border-collapse: collapse;
}
</style>
