<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { AlertCircle, Clock, TrendingUp, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    timeDeposits: { type: Array, default: () => [] },
    collaterals: { type: Array, default: () => [] },
    governmentSecurities: { type: Array, default: () => [] },
    otherInvestments: { type: Array, default: () => [] },
    operatingAccounts: { type: Array, default: () => [] },
    dollars: { type: Array, default: () => [] },
    corporateBonds: { type: Array, default: () => [] },
    cashInfusions: { type: Array, default: () => [] },
    investments: { type: Array, default: () => [] }
});

const getDaysRemaining = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    const diffTime = date - today;
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};

const allItems = computed(() => {
    let items = [];
    
    props.timeDeposits.forEach(item => items.push({ ...item, type: 'Time Deposit', maturityDate: item.maturity_date }));
    props.collaterals.forEach(item => items.push({ ...item, type: 'Collateral', maturityDate: item.maturity_date }));
    props.governmentSecurities.forEach(item => items.push({ ...item, type: 'Government Security', maturityDate: item.maturity_date }));
    props.otherInvestments.forEach(item => items.push({ ...item, type: 'Other Investment', maturityDate: item.maturity_date }));
    props.operatingAccounts.forEach(item => items.push({ ...item, type: 'Operating Account', maturityDate: item.maturity_date }));
    props.dollars.forEach(item => items.push({ ...item, type: 'Dollar', maturityDate: item.maturity_date }));
    props.corporateBonds.forEach(item => items.push({ ...item, type: 'Corporate Bond', maturityDate: item.maturity_date }));
    props.cashInfusions.forEach(item => items.push({ ...item, type: 'Cash Infusion', maturityDate: item.maturity_date }));
    props.investments.forEach(item => items.push({ ...item, type: 'Investment', maturityDate: item.maturity_date }));
    
    return items.filter(item => item.maturityDate).sort((a, b) => new Date(a.maturityDate) - new Date(b.maturityDate));
});

const overdueItems = computed(() => allItems.value.filter(item => getDaysRemaining(item.maturityDate) < 0));
const dueTodayItems = computed(() => allItems.value.filter(item => getDaysRemaining(item.maturityDate) === 0));
const dueWithin7Days = computed(() => allItems.value.filter(item => {
    const days = getDaysRemaining(item.maturityDate);
    return days > 0 && days <= 7;
}));
const dueWithin30Days = computed(() => allItems.value.filter(item => {
    const days = getDaysRemaining(item.maturityDate);
    return days > 7 && days <= 30;
}));

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(date);
};

const getItemName = (item) => {
    return item.time_deposit_name || item.collateral || item.government_security_name || item.other_investment_name || item.operating_account_name || item.corporate_bond_name || item.cash_infusion_name || item.investment_name || item.dollar_name || 'N/A';
};

const getItemNumber = (item) => {
    return item.account_number || item.reference_number || '';
};

const navigateToItem = (item) => {
    const routes = {
        'Time Deposit': `/treasury/time-deposit`,
        'Collateral': `/treasury/collateral`,
        'Government Security': `/treasury/government-securities`,
        'Other Investment': `/treasury/other-investment`,
        'Operating Account': `/treasury/operating-accounts`,
        'Dollar': `/treasury/dollar`,
        'Corporate Bond': `/treasury/corporate-bonds`,
        'Cash Infusion': `/treasury/cash-infusion`,
        'Investment': `/treasury/investment`
    };
    const route = routes[item.type];
    const itemName = getItemName(item);
    if (route) router.visit(route + `?search=${encodeURIComponent(itemName)}`);
};
</script>

<template>
    <Head title="Treasury Dashboard" />
    <TreasuryLayout>
        <div class="px-8">
            <!-- Welcome Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Treasury Dashboard</h1>
                <p class="text-gray-600">Monitor all maturity dates and upcoming actions across your treasury accounts</p>
            </div>

            <!-- Alert Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Overdue Card -->
                <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl border-2 border-red-200 p-6 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-red-900">OVERDUE</h3>
                        <AlertTriangle class="h-5 w-5 text-red-600" />
                    </div>
                    <p class="text-3xl font-black text-red-600">{{ overdueItems.length }}</p>
                    <p class="text-xs text-red-700 mt-2">Immediate action required</p>
                </div>

                <!-- Due Today Card -->
                <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-xl border-2 border-orange-200 p-6 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-orange-900">DUE TODAY</h3>
                        <Clock class="h-5 w-5 text-orange-600" />
                    </div>
                    <p class="text-3xl font-black text-orange-600">{{ dueTodayItems.length }}</p>
                    <p class="text-xs text-orange-700 mt-2">Action needed today</p>
                </div>

                <!-- Due Within 7 Days Card -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl border-2 border-yellow-200 p-6 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-yellow-900">DUE WITHIN 7 DAYS</h3>
                        <TrendingUp class="h-5 w-5 text-yellow-600" />
                    </div>
                    <p class="text-3xl font-black text-yellow-600">{{ dueWithin7Days.length }}</p>
                    <p class="text-xs text-yellow-700 mt-2">Upcoming maturity</p>
                </div>

                <!-- Due Within 30 Days Card -->
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl border-2 border-blue-200 p-6 shadow-md">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-blue-900">DUE WITHIN 30 DAYS</h3>
                        <AlertCircle class="h-5 w-5 text-blue-600" />
                    </div>
                    <p class="text-3xl font-black text-blue-600">{{ dueWithin30Days.length }}</p>
                    <p class="text-xs text-blue-700 mt-2">Plan ahead</p>
                </div>
            </div>

            <!-- Alert Sections -->
            <div class="space-y-6">
                <!-- Overdue Section -->
                <div v-if="overdueItems.length > 0" class="bg-white rounded-xl border-2 border-red-300 shadow-lg overflow-hidden">
                    <div class="bg-red-600 text-white px-6 py-4 flex items-center gap-3">
                        <AlertTriangle class="h-6 w-6" />
                        <h2 class="text-lg font-bold">Overdue Items ({{ overdueItems.length }})</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-red-50">
                                <tr class="border-b border-red-200">
                                    <th class="px-6 py-3 text-left text-sm font-bold text-red-900">Account</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-red-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-red-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-red-900">Days Overdue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in overdueItems" :key="`${item.type}-${item.id}`" class="border-b border-red-100 hover:bg-red-50 cursor-pointer" @click="navigateToItem(item)">
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ getItemName(item) }}</div>
                                        <div class="text-xs text-gray-600">{{ getItemNumber(item) }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700"><span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs font-bold">{{ item.type }}</span></td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.maturityDate) }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-red-600">{{ Math.abs(getDaysRemaining(item.maturityDate)) }} days</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Due Today Section -->
                <div v-if="dueTodayItems.length > 0" class="bg-white rounded-xl border-2 border-orange-300 shadow-lg overflow-hidden">
                    <div class="bg-orange-600 text-white px-6 py-4 flex items-center gap-3">
                        <Clock class="h-6 w-6" />
                        <h2 class="text-lg font-bold">Due Today ({{ dueTodayItems.length }})</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-orange-50">
                                <tr class="border-b border-orange-200">
                                    <th class="px-6 py-3 text-left text-sm font-bold text-orange-900">Account</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-orange-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-orange-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-orange-900">Action Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in dueTodayItems" :key="`${item.type}-${item.id}`" class="border-b border-orange-100 hover:bg-orange-50 cursor-pointer" @click="navigateToItem(item)">
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ getItemName(item) }}</div>
                                        <div class="text-xs text-gray-600">{{ getItemNumber(item) }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700"><span class="px-2 py-1 bg-orange-100 text-orange-800 rounded text-xs font-bold">{{ item.type }}</span></td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.maturityDate) }}</td>
                                    <td class="px-6 py-3 text-sm"><span class="px-3 py-1 bg-orange-200 text-orange-900 rounded font-bold text-xs">Renew or Withdraw</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Due Within 7 Days Section -->
                <div v-if="dueWithin7Days.length > 0" class="bg-white rounded-xl border-2 border-yellow-300 shadow-lg overflow-hidden">
                    <div class="bg-yellow-600 text-white px-6 py-4 flex items-center gap-3">
                        <TrendingUp class="h-6 w-6" />
                        <h2 class="text-lg font-bold">Due Within 7 Days ({{ dueWithin7Days.length }})</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-yellow-50">
                                <tr class="border-b border-yellow-200">
                                    <th class="px-6 py-3 text-left text-sm font-bold text-yellow-900">Account</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-yellow-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-yellow-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-yellow-900">Days Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in dueWithin7Days" :key="`${item.type}-${item.id}`" class="border-b border-yellow-100 hover:bg-yellow-50 cursor-pointer" @click="navigateToItem(item)">
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ getItemName(item) }}</div>
                                        <div class="text-xs text-gray-600">{{ getItemNumber(item) }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700"><span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-bold">{{ item.type }}</span></td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.maturityDate) }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-yellow-600">{{ getDaysRemaining(item.maturityDate) }} days</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Due Within 30 Days Section -->
                <div v-if="dueWithin30Days.length > 0" class="bg-white rounded-xl border-2 border-blue-300 shadow-lg overflow-hidden">
                    <div class="bg-blue-600 text-white px-6 py-4 flex items-center gap-3">
                        <AlertCircle class="h-6 w-6" />
                        <h2 class="text-lg font-bold">Due Within 30 Days ({{ dueWithin30Days.length }})</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-blue-50">
                                <tr class="border-b border-blue-200">
                                    <th class="px-6 py-3 text-left text-sm font-bold text-blue-900">Account</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-blue-900">Type</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-blue-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-blue-900">Days Remaining</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in dueWithin30Days" :key="`${item.type}-${item.id}`" class="border-b border-blue-100 hover:bg-blue-50 cursor-pointer" @click="navigateToItem(item)">
                                    <td class="px-6 py-3 text-sm text-gray-900">
                                        <div class="font-semibold">{{ getItemName(item) }}</div>
                                        <div class="text-xs text-gray-600">{{ getItemNumber(item) }}</div>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-700"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold">{{ item.type }}</span></td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.maturityDate) }}</td>
                                    <td class="px-6 py-3 text-sm font-bold text-blue-600">{{ getDaysRemaining(item.maturityDate) }} days</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- No Alerts Section -->
                <div v-if="overdueItems.length === 0 && dueTodayItems.length === 0 && dueWithin7Days.length === 0 && dueWithin30Days.length === 0" class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl border-2 border-green-200 p-12 text-center shadow-lg">
                    <svg class="h-16 w-16 text-green-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-green-900 mb-2">All Clear!</h2>
                    <p class="text-green-700">No maturity alerts. All treasury accounts are in good standing.</p>
                </div>
            </div>
        </div>
    </TreasuryLayout>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
</style>
