<script setup>
import Treasury3Layout from '@/Layouts/Treasury3Layout.vue';
import AddCollectionModal from './AddCollection.vue';
import ShowCollection from './ShowCollection.vue';
import { Head } from '@inertiajs/vue3';
import { Plus, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';

const props = defineProps({
    operatingAccounts: {
        type: Array,
        default: () => []
    }
});

const showModal = ref(false);
const showDetailsModal = ref(false);
const selectedAccount = ref(null);
const selectedCollections = ref([]);
const selectedAccountName = ref('');
const searchQuery = ref('');

const handleAddCollection = (account) => {
    selectedAccount.value = account;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedAccount.value = null;
};

const handleShowDetails = (collections, accountName) => {
    selectedCollections.value = collections;
    selectedAccountName.value = accountName;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedCollections.value = [];
};

const groupedCollections = computed(() => {
    const groups = {};
    
    props.operatingAccounts.forEach(account => {
        if (account.collections && account.collections.length > 0) {
            account.collections.forEach(collection => {
                const timestamp = new Date(collection.created_at).toLocaleString();
                const key = `${account.id}-${timestamp}`;
                
                if (!groups[key]) {
                    groups[key] = {
                        timestamp,
                        createdAt: collection.created_at,
                        accountName: account.operating_account_name,
                        accountId: account.id,
                        collections: []
                    };
                }
                
                groups[key].collections.push(collection);
            });
        }
    });
    
    return Object.values(groups);
});

const getTotalAmount = (collections) => {
    return collections.reduce((total, collection) => total + parseFloat(collection.collection_amount || 0), 0);
};

const hasCollections = computed(() => filteredCollections.value && filteredCollections.value.length > 0);

const filteredCollections = computed(() => {
    if (!searchQuery.value.trim()) {
        return groupedCollections.value;
    }
    
    const query = searchQuery.value.toLowerCase();
    return groupedCollections.value.filter(group => 
        group.accountName.toLowerCase().includes(query) ||
        group.timestamp.toLowerCase().includes(query)
    );
});

const formatDateWithTime = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};
</script>

<template>
    <Head title="Operating Accounts - Treasury 3" />
    <Treasury3Layout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">Collection Deposits</h1>
                        <p class="text-gray-600 mt-1">Manage collection deposits for your operating accounts</p>
                    </div>
                    <button
                        @click="handleAddCollection(null)"
                        class="flex items-center space-x-2 px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Add Collection</span>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by account name or date..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasCollections && groupedCollections.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Collections Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first collection deposit.</p>
                    <button
                        @click="handleAddCollection(null)"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Add First Collection</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredCollections.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No collections match your search query.</p>
                </div>
            </div>

            <!-- Collections Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Operating Account</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white border-r border-gray-300">Collections</th>
                                <th class="px-6 py-4 text-right text-sm font-bold text-white border-r border-gray-300">Total Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Created At</th>
                                <th class="px-6 py-4 text-center text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="group in filteredCollections" 
                                :key="`${group.accountId}-${group.timestamp}`"
                                :class="[
                                    'relative transition-all duration-300 ease-out select-none',
                                    'border-b border-gray-300 hover:bg-yellow-100'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                                        {{ group.accountName }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 text-center border-r border-gray-300">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        {{ group.collections.length }} item{{ group.collections.length !== 1 ? 's' : '' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-bold text-right border-r border-gray-300">
                                    ₱ {{ getTotalAmount(group.collections).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ formatDateWithTime(group.createdAt) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center">
                                    <button
                                        @click="handleShowDetails(group.collections, group.accountName)"
                                        class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-500 text-white hover:bg-blue-600 font-semibold text-sm transition-colors"
                                    >
                                        Show Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </Treasury3Layout>

    <!-- Add Collection Modal -->
    <AddCollectionModal :isOpen="showModal" :account="selectedAccount" :operatingAccounts="props.operatingAccounts" @close="closeModal" />
    
    <!-- Show Collection Details Modal -->
    <ShowCollection :isOpen="showDetailsModal" :collections="selectedCollections" :accountName="selectedAccountName" @close="closeDetailsModal" />
</template>

