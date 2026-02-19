<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import { Plus, Search } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    investments: {
        type: Array,
        default: () => []
    }
});

onMounted(() => {
    document.title = 'Investment Management - Daily Deposit';
});

const searchQuery = ref('');

const handleCollection = (investment) => {
    Swal.fire({
        title: 'Collection',
        text: `Add collection for ${investment.investment_name}`,
        icon: 'info',
        confirmButtonColor: '#10B981'
    });
};

const handleDisbursement = (investment) => {
    Swal.fire({
        title: 'Disbursement',
        text: `Add disbursement for ${investment.investment_name}`,
        icon: 'info',
        confirmButtonColor: '#EF4444'
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

const filteredInvestments = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.investments;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.investments.filter(investment => 
        investment.investment_name.toLowerCase().includes(query) ||
        investment.reference_number.toLowerCase().includes(query)
    );
});

const totalBeginningBalance = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + parseFloat(investment.beginning_balance || 0);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + parseFloat(investment.collection || 0);
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        return sum + parseFloat(investment.disbursement || 0);
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredInvestments.value.reduce((sum, investment) => {
        const ending = parseFloat(investment.beginning_balance || 0) + parseFloat(investment.collection || 0) - parseFloat(investment.disbursement || 0);
        return sum + ending;
    }, 0);
});
</script>

<template>
    <Treasury2Layout>
        <div class="w-full px-8 py-6">
            <!-- Header - Informative Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Investment Management</h1>
                <p class="text-gray-600 text-sm font-medium">Oversee investment portfolio with real-time balance and maturity tracking</p>
            </div>

            <!-- Search Bar -->
            <div class="mb-6">
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by investment name or reference number..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Investment Table -->
            <div class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Investment Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Reference Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredInvestments.length === 0" class="border-b border-gray-200">
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    No investments found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(investment, index) in filteredInvestments" 
                                :key="investment.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ investment.investment_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-200">{{ investment.reference_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(investment.beginning_balance) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-semibold border-r border-gray-200">{{ formatCurrency(investment.collection) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-semibold border-r border-gray-200">{{ formatCurrency(investment.disbursement) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(parseFloat(investment.beginning_balance || 0) + parseFloat(investment.collection || 0) - parseFloat(investment.disbursement || 0)) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-200">{{ formatDate(investment.maturity_date) }}</td>
                                <td class="px-6 py-4 text-sm space-x-2 flex">
                                    <button
                                        @click="handleCollection(investment)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-green-500 text-white rounded hover:bg-green-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Collection</span>
                                    </button>
                                    <button
                                        @click="handleDisbursement(investment)"
                                        class="flex items-center gap-1 px-3 py-1.5 bg-red-500 text-white rounded hover:bg-red-600 transition-all duration-200 font-semibold text-xs shadow-sm hover:shadow-md"
                                    >
                                        <Plus class="h-4 w-4" />
                                        <span>Disbursement</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredInvestments.length > 0">
                            <tr class="bg-yellow-50 font-bold border-b-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">{{ formatCurrency(totalBeginningBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 border-r border-gray-300">{{ formatCurrency(totalCollection) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 border-r border-gray-300">{{ formatCurrency(totalDisbursement) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 border-r border-gray-300">{{ formatCurrency(totalEndingBalance) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </Treasury2Layout>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
