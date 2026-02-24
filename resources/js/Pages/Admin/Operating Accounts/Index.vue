<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ViewOperatingAccountModal from '@/Pages/Treasury/Operating Accounts/View.vue';
import { Head, usePage, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Search, Calendar, Eye } from 'lucide-vue-next';

const page = usePage();
const operatingAccounts = computed(() => page.props.operatingAccounts || []);
const searchQuery = ref('');
const showViewModal = ref(false);
const selectedAccount = ref(null);

// Set default date to today
const getTodayDate = () => {
    const today = new Date();
    const year = today.getFullYear();
    const month = String(today.getMonth() + 1).padStart(2, '0');
    const day = String(today.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const selectedDate = ref(getTodayDate());

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value || 0);
};

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    }).format(new Date(date));
};

// Calculate total collection for an account
const getTotalCollection = (account) => {
    return account.collections?.reduce((sum, col) => {
        return sum + (col.status === 'processed' ? parseFloat(col.collection_amount || 0) : 0);
    }, 0) || 0;
};

// Calculate total disbursement for an account
const getTotalDisbursement = (account) => {
    return account.disbursements?.reduce((sum, dis) => {
        return sum + (dis.status === 'processed' ? parseFloat(dis.amount || 0) : 0);
    }, 0) || 0;
};

// Get rolling beginning balance for the selected date
// This is the previous day's ending balance (or original if first day)
const getRollingBeginningBalance = (account, selectedDate) => {
    if (!selectedDate) {
        return parseFloat(account.beginning_balance || 0);
    }
    
    // Start with the original beginning balance
    let balance = parseFloat(account.beginning_balance || 0);
    
    // Add all processed collection amounts from dates BEFORE the selected date
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
    
    // Subtract all processed disbursement amounts from dates BEFORE the selected date
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

// Calculate ending balance
const getEndingBalance = (account) => {
    const collection = getTotalCollection(account);
    return parseFloat(account.beginning_balance || 0) + collection;
};

// Filter accounts by search and date
const filteredAccounts = computed(() => {
    return operatingAccounts.value.filter(account => {
        const matchesSearch = !searchQuery.value.trim() || 
            account.operating_account_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            account.account_number.toLowerCase().includes(searchQuery.value.toLowerCase());
        
        return matchesSearch;
    });
});

// Get collections filtered by date
const getCollectionsByDate = (account) => {
    if (!account.collections) return [];
    
    if (!selectedDate.value) return account.collections;
    
    return account.collections.filter(col => {
        const colDate = new Date(col.created_at);
        const selected = new Date(selectedDate.value);
        
        return colDate.getFullYear() === selected.getFullYear() &&
               colDate.getMonth() === selected.getMonth() &&
               colDate.getDate() === selected.getDate();
    });
};

// Get total collection by date
const getTotalCollectionByDate = (account) => {
    return getCollectionsByDate(account).reduce((sum, col) => {
        return sum + (col.status === 'processed' ? parseFloat(col.collection_amount || 0) : 0);
    }, 0) || 0;
};

// Get disbursements filtered by date
const getDisbursementsByDate = (account) => {
    if (!account.disbursements) return [];
    
    if (!selectedDate.value) return account.disbursements;
    
    return account.disbursements.filter(dis => {
        const disDate = new Date(dis.created_at);
        const selected = new Date(selectedDate.value);
        
        return disDate.getFullYear() === selected.getFullYear() &&
               disDate.getMonth() === selected.getMonth() &&
               disDate.getDate() === selected.getDate();
    });
};

// Get total disbursement by date
const getTotalDisbursementByDate = (account) => {
    return getDisbursementsByDate(account).reduce((sum, dis) => {
        return sum + (dis.status === 'processed' ? parseFloat(dis.amount || 0) : 0);
    }, 0) || 0;
};

// Calculate totals for all accounts
const totalBeginningBalance = computed(() => {
    return filteredAccounts.value.reduce((sum, account) => {
        return sum + getRollingBeginningBalance(account, selectedDate.value);
    }, 0);
});

const totalCollectionByDate = computed(() => {
    return filteredAccounts.value.reduce((sum, account) => {
        return sum + getTotalCollectionByDate(account);
    }, 0);
});

const totalDisbursementByDate = computed(() => {
    return filteredAccounts.value.reduce((sum, account) => {
        return sum + getTotalDisbursementByDate(account);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredAccounts.value.reduce((sum, account) => {
        const beginning = getRollingBeginningBalance(account, selectedDate.value);
        const collection = getTotalCollectionByDate(account);
        const disbursement = getTotalDisbursementByDate(account);
        const ending = beginning + collection - disbursement;
        return sum + ending;
    }, 0);
});

const viewOperatingAccount = (account) => {
    selectedAccount.value = account;
    showViewModal.value = true;
};
</script>

<template>
    <Head title="Operating Accounts" />
    <AdminLayout>
        <div class="space-y-8">
            <!-- Header -->
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Operating Accounts</h1>
                    <p class="text-gray-600 mt-2">View and manage your operating accounts and transactions.</p>
                </div>
                <div class="flex gap-3">
                    <Link href="/admin/operating-accounts/view-collection" class="flex items-center gap-2 px-6 py-3 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all shadow-lg">
                        <Eye class="h-5 w-5" />
                        View Collection
                    </Link>
                    <Link href="/admin/operating-accounts/view-disbursement" class="flex items-center gap-2 px-6 py-3 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition-all shadow-lg">
                        <Eye class="h-5 w-5" />
                        View Disbursement
                    </Link>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-yellow-50 rounded-lg border border-yellow-200 p-6">
                <div class="flex gap-4 items-end">
                    <!-- Search Bar -->
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Search Account</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by account name or number..."
                                class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            />
                        </div>
                    </div>

                    <!-- Date Filter -->
                    <div class="w-48">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date</label>
                        <div class="relative">
                            <Calendar class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                            <input
                                v-model="selectedDate"
                                type="date"
                                class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Operating Account Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Acquisition Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border border-yellow-600">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredAccounts.length === 0">
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500 border border-gray-200">
                                    No operating accounts found.
                                </td>
                            </tr>
                            <tr v-for="(account, index) in filteredAccounts" :key="account.id" :class="[
                                'border border-gray-200 hover:bg-yellow-50 transition-colors',
                                index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                            ]">
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ account.operating_account_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border border-gray-200">
                                    {{ account.account_number }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border border-gray-200">
                                    {{ formatDate(account.acquisition_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border border-gray-200">
                                    {{ formatDate(account.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border border-gray-200">
                                    {{ formatCurrency(getRollingBeginningBalance(account, selectedDate)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border border-gray-200">
                                    {{ formatCurrency(getTotalCollectionByDate(account)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border border-gray-200">
                                    {{ formatCurrency(getTotalDisbursementByDate(account)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border border-gray-200">
                                    {{ formatCurrency(getRollingBeginningBalance(account, selectedDate) + getTotalCollectionByDate(account) - getTotalDisbursementByDate(account)) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center border border-gray-200">
                                    <button
                                        @click="viewOperatingAccount(account)"
                                        class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-150"
                                    >
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-yellow-50 font-bold border border-gray-200">
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200">
                                    {{ formatCurrency(totalBeginningBalance) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-green-600 border border-gray-200">
                                    {{ formatCurrency(totalCollectionByDate) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600 border border-gray-200">
                                    {{ formatCurrency(totalDisbursementByDate) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-blue-600 border border-gray-200">
                                    {{ formatCurrency(totalEndingBalance) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 border border-gray-200"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- View Operating Account Modal -->
            <ViewOperatingAccountModal 
                :is-open="showViewModal"
                :operating-account="selectedAccount"
                @close="showViewModal = false"
            />
        </div>
    </AdminLayout>
</template>
