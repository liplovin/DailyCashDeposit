<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { TrendingUp, TrendingDown, Calendar, Users, FileText, BarChart3 } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Props for all Treasury2 modules data
const props = defineProps({
    collaterals: { type: Array, default: () => [] },
    otherInvestments: { type: Array, default: () => [] },
    dollars: { type: Array, default: () => [] },
    governmentSecurities: { type: Array, default: () => [] },
    timeDeposits: { type: Array, default: () => [] },
    cashInfusions: { type: Array, default: () => [] },
    corporateBonds: { type: Array, default: () => [] },
    investments: { type: Array, default: () => [] },
    operatingAccounts: { type: Array, default: () => [] }
});

const filterDate = ref(new Date().toISOString().split('T')[0]);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PHP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const formatCurrencyByModule = (value, moduleKey) => {
    const currency = moduleKey === 'dollar' ? 'USD' : 'PHP';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const formatDate = (dateString) => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    return new Intl.DateTimeFormat('en-US', options).format(new Date(dateString));
};

const calculateModuleData = (data, dateFilter) => {
    let totalCollection = 0;
    let totalDisbursement = 0;

    if (!Array.isArray(data)) {
        return { collection: 0, disbursement: 0 };
    }

    data.forEach(item => {
        if (!item) return;

        const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
        const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
        
        const collectionAmount = item.collection ? parseFloat(item.collection) : 0;
        const disbursementAmount = item.disbursement ? parseFloat(item.disbursement) : 0;

        if (collectionDate === dateFilter && collectionAmount > 0) {
            totalCollection += collectionAmount;
        }
        if (disbursementDate === dateFilter && disbursementAmount > 0) {
            totalDisbursement += disbursementAmount;
        }
    });

    return { 
        collection: isNaN(totalCollection) ? 0 : totalCollection, 
        disbursement: isNaN(totalDisbursement) ? 0 : totalDisbursement 
    };
};

const getDateString = (dateValue) => {
    if (!dateValue) return null;
    // If it's already a date string (YYYY-MM-DD), return as-is
    if (typeof dateValue === 'string' && dateValue.match(/^\d{4}-\d{2}-\d{2}/)) {
        return dateValue.split('T')[0];
    }
    // Otherwise parse as Date object
    const date = new Date(dateValue);
    return date.toISOString().split('T')[0];
};

const calculateOperatingAccountData = (accounts, dateFilter) => {
    let totalCollection = 0;
    let totalDisbursement = 0;

    if (!Array.isArray(accounts)) {
        return { collection: 0, disbursement: 0 };
    }

    accounts.forEach(account => {
        if (!account) return;

        // Sum collections for this date
        if (account.collections && Array.isArray(account.collections)) {
            account.collections.forEach(collection => {
                const collectionDate = getDateString(collection.created_at);
                const collectionAmount = collection.collection_amount ? parseFloat(collection.collection_amount) : 0;
                
                if (collectionDate === dateFilter && collectionAmount > 0) {
                    totalCollection += collectionAmount;
                }
            });
        }

        // Sum disbursements for this date (by created_at, not the disbursement date)
        if (account.disbursements && Array.isArray(account.disbursements)) {
            account.disbursements.forEach(disbursement => {
                const disbursementDate = getDateString(disbursement.created_at);
                const disbursementAmount = disbursement.amount ? parseFloat(disbursement.amount) : 0;
                
                if (disbursementDate === dateFilter && disbursementAmount > 0) {
                    totalDisbursement += disbursementAmount;
                }
            });
        }
    });

    return { 
        collection: isNaN(totalCollection) ? 0 : totalCollection, 
        disbursement: isNaN(totalDisbursement) ? 0 : totalDisbursement 
    };
};

const dashboardData = computed(() => {
    return {
        collateral: calculateModuleData(props.collaterals, filterDate.value),
        other_investment: calculateModuleData(props.otherInvestments, filterDate.value),
        dollar: calculateModuleData(props.dollars, filterDate.value),
        government_securities: calculateModuleData(props.governmentSecurities, filterDate.value),
        time_deposit: calculateModuleData(props.timeDeposits, filterDate.value),
        cash_infusion: calculateModuleData(props.cashInfusions, filterDate.value),
        corporate_bonds: calculateModuleData(props.corporateBonds, filterDate.value),
        investment: calculateModuleData(props.investments, filterDate.value),
        operating_account: calculateOperatingAccountData(props.operatingAccounts, filterDate.value)
    };
});

const totalCollection = computed(() => {
    const total = Object.values(dashboardData.value).reduce((sum, module) => sum + (module.collection || 0), 0);
    return isNaN(total) ? 0 : total;
});

const totalDisbursement = computed(() => {
    const total = Object.values(dashboardData.value).reduce((sum, module) => sum + (module.disbursement || 0), 0);
    return isNaN(total) ? 0 : total;
});

const dollarCollection = computed(() => {
    return dashboardData.value.dollar?.collection || 0;
});

const dollarDisbursement = computed(() => {
    return dashboardData.value.dollar?.disbursement || 0;
});

const phpCollection = computed(() => {
    return totalCollection.value - dollarCollection.value;
});

const phpDisbursement = computed(() => {
    return totalDisbursement.value - dollarDisbursement.value;
});

const netFlow = computed(() => {
    const flow = totalCollection.value - totalDisbursement.value;
    return isNaN(flow) ? 0 : flow;
});

const modules = [
    { key: 'collateral', label: 'Collateral', color: 'from-blue-400 to-blue-600' },
    { key: 'other_investment', label: 'Other Investment', color: 'from-purple-400 to-purple-600' },
    { key: 'dollar', label: 'Dollar', color: 'from-green-400 to-green-600' },
    { key: 'government_securities', label: 'Government Securities', color: 'from-yellow-400 to-yellow-600' },
    { key: 'time_deposit', label: 'Time Deposit', color: 'from-pink-400 to-pink-600' },
    { key: 'cash_infusion', label: 'Cash Infusion', color: 'from-red-400 to-red-600' },
    { key: 'corporate_bonds', label: 'Corporate Bonds', color: 'from-indigo-400 to-indigo-600' },
    { key: 'investment', label: 'Investment', color: 'from-teal-400 to-teal-600' },
    { key: 'operating_account', label: 'Operating Account', color: 'from-cyan-400 to-cyan-600' }
];

const stats = [
    { label: 'Total Modules', value: modules.length, icon: BarChart3, color: 'text-blue-600' },
    { label: 'Active Collections', value: Object.values(dashboardData.value).filter(m => m.collection > 0).length, icon: TrendingUp, color: 'text-green-600' },
    { label: 'Active Disbursements', value: Object.values(dashboardData.value).filter(m => m.disbursement > 0).length, icon: TrendingDown, color: 'text-red-600' }
];
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div class="space-y-6">
            <!-- Top Section: Welcome and Date Filter -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2">
                    <div class="bg-gradient-to-r from-slate-700 to-slate-900 rounded-xl shadow-lg p-8 text-white">
                        <h1 class="text-4xl font-bold mb-2">Welcome, {{ user.name }}!</h1>
                        <p class="text-slate-200 text-lg">System Administration & Financial Overview</p>
                        <p class="text-slate-300 text-sm mt-2">Monitor all Treasury2 modules and financial activities</p>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-lg p-6 border-2 border-gray-200">
                    <label class="block text-sm font-bold text-gray-800 mb-3 flex items-center gap-2">
                        <Calendar class="h-5 w-5 text-slate-600" />
                        Select Date
                    </label>
                    <input
                        v-model="filterDate"
                        type="date"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-slate-500 transition-all duration-200 font-semibold"
                    />
                    <p class="text-xs text-gray-600 mt-3">{{ formatDate(filterDate) }}</p>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- PHP Total Collection Card -->
                <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl shadow-lg p-6 text-white border-2 border-green-400">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold">PHP Collections</h3>
                        <TrendingUp class="h-6 w-6 text-green-100" />
                    </div>
                    <p class="text-3xl font-bold">{{ formatCurrency(phpCollection) }}</p>
                    <p class="text-green-100 text-xs mt-2">PHP modules only</p>
                </div>

                <!-- Dollar Total Collection Card -->
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-xl shadow-lg p-6 text-white border-2 border-yellow-400">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold">USD Collections</h3>
                        <TrendingUp class="h-6 w-6 text-yellow-100" />
                    </div>
                    <p class="text-3xl font-bold">{{ formatCurrencyByModule(dollarCollection, 'dollar') }}</p>
                    <p class="text-yellow-100 text-xs mt-2">Dollar module only</p>
                </div>

                <!-- PHP Total Disbursement Card -->
                <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-xl shadow-lg p-6 text-white border-2 border-red-400">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold">PHP Disbursements</h3>
                        <TrendingDown class="h-6 w-6 text-red-100" />
                    </div>
                    <p class="text-3xl font-bold">{{ formatCurrency(phpDisbursement) }}</p>
                    <p class="text-red-100 text-xs mt-2">PHP modules only</p>
                </div>

                <!-- Dollar Total Disbursement Card -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl shadow-lg p-6 text-white border-2 border-orange-400">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold">USD Disbursements</h3>
                        <TrendingDown class="h-6 w-6 text-orange-100" />
                    </div>
                    <p class="text-3xl font-bold">{{ formatCurrencyByModule(dollarDisbursement, 'dollar') }}</p>
                    <p class="text-orange-100 text-xs mt-2">Dollar module only</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="stat in stats" :key="stat.label" class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-slate-600">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm font-semibold">{{ stat.label }}</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2">{{ stat.value }}</p>
                        </div>
                        <component
                            :is="stat.icon"
                            :class="['h-12 w-12 opacity-20', stat.color]"
                        />
                    </div>
                </div>
            </div>

            <!-- Module Breakdown -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-gray-300 p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Module Breakdown</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div v-for="module in modules" :key="module.key" :class="`bg-gradient-to-br ${module.color} rounded-xl p-6 text-white shadow-lg border-2 border-opacity-20 border-white hover:shadow-xl transition-shadow duration-200`">
                        <h3 class="font-bold text-lg mb-4">{{ module.label }}</h3>
                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-white text-opacity-90 mb-1">Collection</p>
                                <p class="text-2xl font-bold">{{ formatCurrencyByModule(dashboardData[module.key].collection, module.key) }}</p>
                            </div>
                            <div class="border-t border-white border-opacity-30 pt-3">
                                <p class="text-sm text-white text-opacity-90 mb-1">Disbursement</p>
                                <p class="text-2xl font-bold">{{ formatCurrencyByModule(dashboardData[module.key].disbursement, module.key) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Table -->
            <div class="bg-white rounded-xl shadow-lg border-2 border-gray-300 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-slate-700 to-slate-900">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-600">Module</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-600">Collection</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-600">Disbursement</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="module in modules" :key="module.key" class="border-b-2 border-gray-200 hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 border-r border-gray-200">{{ module.label }}</td>
                                <td class="px-6 py-4 text-sm text-green-600 font-bold border-r border-gray-200">{{ formatCurrencyByModule(dashboardData[module.key].collection, module.key) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 font-bold border-r border-gray-200">{{ formatCurrencyByModule(dashboardData[module.key].disbursement, module.key) }}</td>
                                <td class="px-6 py-4 text-sm font-bold" :class="(dashboardData[module.key].collection - dashboardData[module.key].disbursement) >= 0 ? 'text-blue-600' : 'text-orange-600'">
                                    {{ formatCurrencyByModule(dashboardData[module.key].collection - dashboardData[module.key].disbursement, module.key) }}
                                </td>
                            </tr>
                            <tr class="bg-slate-100 font-bold border-b-2 border-gray-300">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-200">TOTAL</td>
                                <td class="px-6 py-4 text-sm text-green-600 border-r border-gray-200">{{ formatCurrency(totalCollection) }}</td>
                                <td class="px-6 py-4 text-sm text-red-600 border-r border-gray-200">{{ formatCurrency(totalDisbursement) }}</td>
                                <td :class="['px-6 py-4 text-sm', netFlow >= 0 ? 'text-blue-600' : 'text-orange-600']">{{ formatCurrency(netFlow) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
