<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import { Plus, Search } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    timeDeposits: {
        type: Array,
        default: () => []
    }
});

onMounted(() => {
    document.title = 'Time Deposit Management - Daily Deposit';
});

const searchQuery = ref('');

const handleAddCollection = () => {
    Swal.fire({
        title: 'Add Collection',
        text: 'Features coming soon.',
        icon: 'info',
        confirmButtonColor: '#F59E0B'
    });
};

const handleAddDisbursement = () => {
    Swal.fire({
        title: 'Add Disbursement',
        text: 'Features coming soon.',
        icon: 'info',
        confirmButtonColor: '#F59E0B'
    });
};

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

const filteredDeposits = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.timeDeposits;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.timeDeposits.filter(deposit => 
        deposit.time_deposit_name.toLowerCase().includes(query) ||
        deposit.account_number.toLowerCase().includes(query)
    );
});

const totalBeginningBalance = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + parseFloat(deposit.beginning_balance || 0);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + parseFloat(deposit.collection || 0);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        return sum + parseFloat(deposit.disbursement || 0);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredDeposits.value.reduce((sum, deposit) => {
        const ending = parseFloat(deposit.beginning_balance || 0) + parseFloat(deposit.collection || 0) - parseFloat(deposit.disbursement || 0);
        return sum + ending;
    }, 0);
});
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-12">
            <!-- Header with Buttons -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-3xl font-black text-gray-900">Time Deposit Management</h1>
                    <div class="flex items-center gap-3">
                        <button
                            @click="handleAddCollection"
                            class="flex items-center space-x-2 px-6 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                        >
                            <Plus class="h-5 w-5" />
                            <span>Add Collection</span>
                        </button>
                        <button
                            @click="handleAddDisbursement"
                            class="flex items-center space-x-2 px-6 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                        >
                            <Plus class="h-5 w-5" />
                            <span>Add Disbursement</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-6">
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by time deposit name or account number..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
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
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Maturity Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredDeposits.length === 0" class="border-b border-gray-200">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
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
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(deposit.beginning_balance) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(deposit.collection) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(deposit.disbursement) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(parseFloat(deposit.beginning_balance || 0) + parseFloat(deposit.collection || 0) - parseFloat(deposit.disbursement || 0)) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ formatDate(deposit.maturity_date) }}</td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredDeposits.length > 0">
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
</template>
