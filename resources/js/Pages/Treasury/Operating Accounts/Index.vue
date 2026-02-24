<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateOperatingAccountModal from './Create.vue';
import EditOperatingAccountModal from './Edit.vue';
import RenewOperatingAccountModal from './Renew.vue';
import WithdrawOperatingAccountModal from './Withdraw.vue';
import AddBalanceOperatingAccountModal from './AddBalance.vue';
import ViewOperatingAccountModal from './View.vue';
import { Plus, Trash2, Edit2, Search, ChevronDown, Eye, EyeOff } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    operatingAccounts: {
        type: Array,
        default: () => []
    }
});

const getTodayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

onMounted(() => {
    document.title = 'Operating Accounts Management - Daily Deposit';
    
    const params = new URLSearchParams(window.location.search);
    const searchParam = params.get('search');
    if (searchParam) {
        searchQuery.value = searchParam;
    }
});

const searchQuery = ref('');
const filterDate = ref(getTodayDate());
const showWithdrawn = ref(false);
const showModal = ref(false);
const showEditModal = ref(false);
const showRenewModal = ref(false);
const showWithdrawModal = ref(false);
const showAddBalanceModal = ref(false);
const showViewModal = ref(false);
const selectedOperatingAccount = ref(null);
const openDropdownId = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const filteredOperatingAccounts = computed(() => {
    let filtered = props.operatingAccounts;
    
    // Filter by withdrawn status - show ONLY withdrawn when toggled
    if (showWithdrawn.value) {
        filtered = filtered.filter(account => account.maturity_date === null);
    } else {
        filtered = filtered.filter(account => account.maturity_date !== null);
    }
    
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(account => 
            account.operating_account_name.toLowerCase().includes(query) ||
            account.account_number.toLowerCase().includes(query)
        );
    }
    
    return filtered;
});

const getRollingBeginningBalance = (account, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(account.beginning_balance || 0);
    }
    
    let balance = parseFloat(account.beginning_balance || 0);
    
    if (account.collections) {
        account.collections.forEach(col => {
            if (col.status === 'processed') {
                const collectionDate = new Date(col.created_at).toISOString().split('T')[0];
                if (collectionDate < selectedDate) {
                    balance += parseFloat(col.collection_amount || 0);
                }
            }
        });
    }
    
    if (account.disbursements) {
        account.disbursements.forEach(dis => {
            if (dis.status === 'processed') {
                const disbursementDate = new Date(dis.created_at).toISOString().split('T')[0];
                if (disbursementDate < selectedDate) {
                    balance -= parseFloat(dis.amount || 0);
                }
            }
        });
    }
    
    return balance;
};

const getCollectionsByDate = (account) => {
    if (!account.collections) return [];
    
    if (!filterDate.value) return account.collections;
    
    return account.collections.filter(col => {
        const colDate = new Date(col.created_at);
        const selected = new Date(filterDate.value);
        
        return colDate.getFullYear() === selected.getFullYear() &&
               colDate.getMonth() === selected.getMonth() &&
               colDate.getDate() === selected.getDate();
    });
};

const getTotalCollectionByDate = (account) => {
    return getCollectionsByDate(account).reduce((sum, col) => {
        return sum + (col.status === 'processed' ? parseFloat(col.collection_amount || 0) : 0);
    }, 0) || 0;
};

const getDisbursementsByDate = (account) => {
    if (!account.disbursements) return [];
    
    if (!filterDate.value) return account.disbursements;
    
    return account.disbursements.filter(dis => {
        const disDate = new Date(dis.created_at);
        const selected = new Date(filterDate.value);
        
        return disDate.getFullYear() === selected.getFullYear() &&
               disDate.getMonth() === selected.getMonth() &&
               disDate.getDate() === selected.getDate();
    });
};

const getTotalDisbursementByDate = (account) => {
    return getDisbursementsByDate(account).reduce((sum, dis) => {
        return sum + (dis.status === 'processed' ? parseFloat(dis.amount || 0) : 0);
    }, 0) || 0;
};

const hasOperatingAccounts = computed(() => filteredOperatingAccounts.value && filteredOperatingAccounts.value.length > 0);

const openModal = () => {
    selectedOperatingAccount.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedOperatingAccount.value = null;
};

const openEditModal = (account) => {
    selectedOperatingAccount.value = account;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedOperatingAccount.value = null;
};

const openRenewModal = (account) => {
    selectedOperatingAccount.value = account;
    showRenewModal.value = true;
};

const closeRenewModal = () => {
    showRenewModal.value = false;
    selectedOperatingAccount.value = null;
};

const openWithdrawModal = (account) => {
    selectedOperatingAccount.value = account;
    showWithdrawModal.value = true;
};

const closeWithdrawModal = () => {
    showWithdrawModal.value = false;
    selectedOperatingAccount.value = null;
};

const addBalance = (account) => {
    selectedOperatingAccount.value = account;
    showAddBalanceModal.value = true;
};

const closeAddBalanceModal = () => {
    showAddBalanceModal.value = false;
    selectedOperatingAccount.value = null;
};

const viewOperatingAccount = (account) => {
    selectedOperatingAccount.value = account;
    showViewModal.value = true;
};

const closeViewModal = () => {
    showViewModal.value = false;
    selectedOperatingAccount.value = null;
};

const toggleDropdown = (accountId) => {
    openDropdownId.value = openDropdownId.value === accountId ? null : accountId;
};

const deleteOperatingAccount = async (account) => {
    const result = await Swal.fire({
        title: 'Delete Operating Account?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Operating Account:</strong> ${account.operating_account_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${account.account_number}</p>
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
        router.delete(`/treasury/operating-accounts/${account.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Operating Account has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete operating account. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
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

const isCreatedToday = (createdAtString) => {
    if (!createdAtString) return false;
    const createdDate = new Date(createdAtString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    createdDate.setHours(0, 0, 0, 0);
    
    return createdDate.getTime() === today.getTime();
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

const totalBeginningBalance = computed(() => {
    return filteredOperatingAccounts.value.reduce((sum, account) => {
        return sum + getRollingBeginningBalance(account, filterDate.value);
    }, 0);
});

const totalCollectionByDate = computed(() => {
    return filteredOperatingAccounts.value.reduce((sum, account) => {
        return sum + getTotalCollectionByDate(account);
    }, 0);
});

const totalDisbursementByDate = computed(() => {
    return filteredOperatingAccounts.value.reduce((sum, account) => {
        return sum + getTotalDisbursementByDate(account);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredOperatingAccounts.value.reduce((sum, account) => {
        const beginning = getRollingBeginningBalance(account, filterDate.value);
        const collection = getTotalCollectionByDate(account);
        const disbursement = getTotalDisbursementByDate(account);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});
</script>

<template>
    <TreasuryLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-4xl font-black text-gray-900 mb-2">Operating Accounts Management</h1>
                        <p class="text-gray-600 text-sm font-medium">View all operating account records with real-time balances and transactions</p>
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

            <!-- Search Bar and Filter -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Operating Account</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by operating account name or account number..."
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

            <!-- Empty State -->
            <div v-if="props.operatingAccounts.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Operating Accounts Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first operating account.</p>
                    <button
                        @click="openModal"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create First Operating Account</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredOperatingAccounts.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No operating accounts match your search query.</p>
                </div>
            </div>

            <!-- Operating Accounts Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Operating Account Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Acquisition Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Action</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(account, index) in filteredOperatingAccounts" 
                                :key="account.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    isOverdueOrDueToday(account.maturity_date) ? 'blink-red' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ account.operating_account_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ account.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ account.acquisition_date ? new Date(account.acquisition_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' }) : '—' }}</td>
                                <td class="px-6 py-4 text-sm font-mono border-r border-gray-200" :class="account.maturity_date ? 'text-gray-700' : 'text-green-600 font-bold'">
                                    {{ formatMaturityDate(account.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(account, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(account, filterDate) + getTotalCollectionByDate(account) - getTotalDisbursementByDate(account)) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div class="relative">
                                        <button
                                            @click.stop="toggleDropdown(account.id)"
                                            :class="[
                                                'inline-flex items-center space-x-1 px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200',
                                                openDropdownId === account.id 
                                                    ? 'bg-yellow-600 text-white' 
                                                    : 'bg-yellow-500 text-white hover:bg-yellow-600'
                                            ]"
                                        >
                                            <span>Actions</span>
                                            <ChevronDown :class="['h-4 w-4', openDropdownId === account.id ? 'rotate-180' : '']" style="transition: transform 0.2s" />
                                        </button>
                                        <div 
                                            v-if="openDropdownId === account.id"
                                            @click.stop
                                            class="absolute top-full mt-2 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-50 min-w-48 overflow-hidden"
                                        >
                                            <button
                                                v-if="isMaturityActionVisible(account.maturity_date)"
                                                @click="openRenewModal(account); toggleDropdown(null)"
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            >
                                                ✓ Renew
                                            </button>
                                            <button
                                                @click="openWithdrawModal(account); toggleDropdown(null)"
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            >
                                                ↓ Withdraw
                                            </button>
                                            <button
                                                v-if="account.maturity_date !== null"
                                                @click="addBalance(account); toggleDropdown(null)"
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            >
                                                + Add
                                            </button>
                                            <button
                                                @click="viewOperatingAccount(account); toggleDropdown(null)"
                                                class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                                            >
                                                👁 View
                                            </button>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div v-if="isCreatedToday(account.created_at)" class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(account)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteOperatingAccount(account)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                    <div v-else class="text-xs text-gray-500">—</div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredOperatingAccounts.length > 0">
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
                <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-300">
                    <p class="text-sm text-gray-600">Total: <span class="font-semibold text-gray-900">{{ filteredOperatingAccounts.length }}</span> operating account(s)</p>
                </div>
            </div>

            <!-- Create Modal -->
            <CreateOperatingAccountModal :isOpen="showModal" :existingOperatingAccounts="props.operatingAccounts" @close="closeModal" />

            <!-- Edit Modal -->
            <EditOperatingAccountModal :isOpen="showEditModal" :operatingAccount="selectedOperatingAccount" :existingOperatingAccounts="props.operatingAccounts" @close="closeEditModal" />

            <!-- Renew Modal -->
            <RenewOperatingAccountModal :isOpen="showRenewModal" :operatingAccount="selectedOperatingAccount" @close="closeRenewModal" />

            <!-- Withdraw Modal -->
            <WithdrawOperatingAccountModal :isOpen="showWithdrawModal" :operatingAccount="selectedOperatingAccount" @close="closeWithdrawModal" />

            <!-- Add Balance Modal -->
            <AddBalanceOperatingAccountModal :isOpen="showAddBalanceModal" :operatingAccount="selectedOperatingAccount" @close="closeAddBalanceModal" />

            <!-- View History Modal -->
            <ViewOperatingAccountModal 
                :isOpen="showViewModal"
                :operatingAccount="selectedOperatingAccount"
                @close="closeViewModal"
            />
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
