<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue';
import ShowDisbursement from './ShowDisbursement.vue';
import { usePage, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Search, Calendar, ArrowLeft } from 'lucide-vue-next';

const page = usePage();
const showDetailsModal = ref(false);
const selectedGroup = ref(null);
const searchQuery = ref('');
const today = new Date().toISOString().split('T')[0];
const filterDate = ref(today);

const props = defineProps({
    operatingAccounts: {
        type: Array,
        default: () => []
    },
    disbursements: {
        type: Array,
        default: () => []
    }
});

const handleShowDetails = (group) => {
    selectedGroup.value = group;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedGroup.value = null;
};

const groupedDisbursements = computed(() => {
    const groups = {};
    
    props.disbursements.forEach(disbursement => {
        const timestamp = new Date(disbursement.created_at).toLocaleString();
        const operatingAccountId = disbursement.operating_account_id;
        const key = `${operatingAccountId}-${timestamp}`;
        
        if (!groups[key]) {
            groups[key] = {
                timestamp,
                createdAt: disbursement.created_at,
                operatingAccountName: disbursement.operating_account?.operating_account_name,
                operatingAccountId: operatingAccountId,
                disbursements: []
            };
        }
        
        groups[key].disbursements.push(disbursement);
    });
    
    return Object.values(groups);
});

const getTotalAmount = (disbursements) => {
    return disbursements.reduce((total, d) => total + parseFloat(d.amount || 0), 0);
};

const filteredDisbursements = computed(() => {
    let filtered = groupedDisbursements.value;
    
    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(group => 
            group.operatingAccountName.toLowerCase().includes(query) ||
            group.timestamp.toLowerCase().includes(query)
        );
    }
    
    // Filter by specific date
    if (filterDate.value) {
        const selectedDate = new Date(filterDate.value).toDateString();
        filtered = filtered.filter(group => {
            const groupDate = new Date(group.createdAt).toDateString();
            return groupDate === selectedDate;
        });
    }
    
    return filtered;
});

const hasDisbursements = computed(() => filteredDisbursements.value && filteredDisbursements.value.length > 0);

const totalFilteredAmount = computed(() => {
    return filteredDisbursements.value.reduce((total, group) => {
        return total + getTotalAmount(group.disbursements);
    }, 0);
});

const groupedByOperatingAccount = computed(() => {
    const grouped = {};
    
    filteredDisbursements.value.forEach(group => {
        const accountId = group.operatingAccountId;
        const accountName = group.operatingAccountName;
        
        if (!grouped[accountId]) {
            grouped[accountId] = {
                accountName,
                groups: [],
                total: 0
            };
        }
        
        grouped[accountId].groups.push(group);
        grouped[accountId].total += getTotalAmount(group.disbursements);
    });
    
    return grouped;
});

const formatDateWithTime = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};
</script>

<template>
    <AccountingLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">Processed Disbursements</h1>
                        <p class="text-gray-600 mt-1">View all processed disbursement records</p>
                    </div>
                    <Link
                        href="/accounting/operating-accounts"
                        class="flex items-center space-x-2 px-6 py-2.5 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        <span>Back to Pending</span>
                    </Link>
                </div>

                <!-- Search Bar -->
                <div class="flex gap-4 flex-col md:flex-row">
                    <div class="relative flex-1">
                        <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by operating account name or date..."
                            class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                        />
                    </div>
                    <div class="relative w-full md:w-48">
                        <Calendar class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                        <input
                            v-model="filterDate"
                            type="date"
                            placeholder="Filter by date"
                            class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900"
                        />
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasDisbursements && groupedDisbursements.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Processed Disbursements Yet</h2>
                    <p class="text-gray-600">Once you process disbursements, they will appear here.</p>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="(searchQuery || filterDate) && filteredDisbursements.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No processed disbursements match your search criteria.</p>
                </div>
            </div>

            <!-- Disbursements Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-blue-400 to-blue-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Operating Account</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white border-r border-gray-300">Disbursements</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-white border-r border-gray-300">Total Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Created At</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="(accountData, accountId) in groupedByOperatingAccount" :key="accountId">
                                <tr 
                                    v-for="group in accountData.groups" 
                                    :key="`${group.operatingAccountId}-${group.timestamp}`"
                                    :class="[
                                        'relative transition-all duration-300 ease-out select-none',
                                        'border-b border-gray-300 hover:bg-blue-100'
                                    ]"
                                >
                                    <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-3"></div>
                                            {{ group.operatingAccountName }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 text-center border-r border-gray-300">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                            {{ group.disbursements.length }} item{{ group.disbursements.length !== 1 ? 's' : '' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900 font-bold text-right border-r border-gray-300">
                                        ₱ {{ getTotalAmount(group.disbursements).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                        {{ formatDateWithTime(group.createdAt) }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-center">
                                        <button
                                            @click="handleShowDetails(group)"
                                            class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 font-semibold text-sm transition-colors"
                                        >
                                            Show Details
                                        </button>
                                    </td>
                                </tr>
                                <!-- Subtotal row for operating account -->
                                <tr class="bg-blue-50 border-b-2 border-blue-300">
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 border-r border-gray-300">
                                        {{ accountData.accountName }} Subtotal
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900 border-r border-gray-300"></td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-700 text-right border-r border-gray-300">
                                        ₱ {{ accountData.total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                    </td>
                                    <td colspan="2" class="px-6 py-4"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="bg-blue-100 border-t-2 border-gray-300">
                            <tr>
                                <td colspan="1" class="px-6 py-4 text-sm font-bold text-gray-900 border-r border-gray-300">
                                    Total ({{ filteredDisbursements.length }} group{{ filteredDisbursements.length !== 1 ? 's' : '' }})
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right border-r border-gray-300">
                                    ₱ {{ totalFilteredAmount.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td colspan="2" class="px-6 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Show Disbursement Details Modal -->
        <ShowDisbursement 
            :isOpen="showDetailsModal"
            :group="selectedGroup"
            @close="closeDetailsModal"
            @refresh="$emit('refresh')"
        />
    </AccountingLayout>
</template>
