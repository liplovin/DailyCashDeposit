<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Calendar, Download, FileText, File } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const page = usePage();

const selectedDate = ref(new Date().toISOString().split('T')[0]);
const isGenerating = ref(false);
const isGeneratingPDF = ref(false);
const selectedModule = ref('all');

const props = defineProps({
    collaterals: {
        type: Array,
        default: () => []
    },
    timeDeposits: {
        type: Array,
        default: () => []
    },
    governmentSecurities: {
        type: Array,
        default: () => []
    },
    otherInvestments: {
        type: Array,
        default: () => []
    },
    operatingAccounts: {
        type: Array,
        default: () => []
    },
    dollars: {
        type: Array,
        default: () => []
    },
    corporateBonds: {
        type: Array,
        default: () => []
    },
    cashInfusions: {
        type: Array,
        default: () => []
    },
    investments: {
        type: Array,
        default: () => []
    }
});

const modules = [
    { name: 'Collateral', data: props.collaterals, key: 'collateral', accField: 'account_number' },
    { name: 'Time Deposit', data: props.timeDeposits, key: 'time_deposit_name', accField: 'account_number' },
    { name: 'Government Securities', data: props.governmentSecurities, key: 'government_security_name', accField: 'reference_number' },
    { name: 'Other Investment', data: props.otherInvestments, key: 'other_investment_name', accField: 'account_number' },
    { name: 'Operating Accounts', data: props.operatingAccounts, key: 'operating_account_name', accField: 'account_number' },
    { name: 'Dollar', data: props.dollars, key: 'dollar_name', accField: 'account_number' },
    { name: 'Corporate Bonds', data: props.corporateBonds, key: 'corporate_bond_name', accField: 'account_number' },
    { name: 'Cash Infusion', data: props.cashInfusions, key: 'cash_infusion_name', accField: 'account_number' },
    { name: 'Investment', data: props.investments, key: 'investment_name', accField: 'reference_number' }
];

const activeModules = computed(() => {
    return modules.map(module => ({
        ...module,
        data: module.data.filter(item => item.maturity_date !== null)
    }));
});

const displayedModules = computed(() => {
    if (selectedModule.value === 'all') {
        return activeModules.value;
    }
    return activeModules.value.filter(module => module.name === selectedModule.value);
});

const getCollectionAmount = (item) => {
    if (!selectedDate.value) {
        return item.collection || 0;
    }
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === selectedDate.value ? (item.collection || 0) : 0;
};

const getDisbursementAmount = (item) => {
    if (!selectedDate.value) {
        return item.disbursement || 0;
    }
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    return disbursementDate === selectedDate.value ? (item.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (item) => {
    if (!selectedDate.value) {
        return parseFloat(item.beginning_balance || 0);
    }
    
    let balance = parseFloat(item.beginning_balance || 0);
    
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate.value) {
        balance += parseFloat(item.collection || 0);
    }
    
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate.value) {
        balance -= parseFloat(item.disbursement || 0);
    }
    
    return balance;
};

const getEndingBalance = (item) => {
    const beginning = getRollingBeginningBalance(item);
    const collection = parseFloat(getCollectionAmount(item) || 0);
    const disbursement = parseFloat(getDisbursementAmount(item) || 0);
    return beginning + collection - disbursement;
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

const formatMaturityDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    date.setHours(0, 0, 0, 0);
    
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Intl.DateTimeFormat('en-US', options).format(date);
};

const generateReport = async () => {
    try {
        isGenerating.value = true;
        
        const response = await axios.post('/admin/reports/generate', {
            date: selectedDate.value
        }, {
            responseType: 'blob'
        });

        // Create a blob link to download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Daily_Deposit_Report_${selectedDate.value}.csv`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);

        Swal.fire({
            icon: 'success',
            title: 'Report Generated',
            text: `CSV report for ${selectedDate.value} has been downloaded successfully`,
            timer: 2000
        });
    } catch (error) {
        console.error('Error generating report:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate report. Please try again.'
        });
    } finally {
        isGenerating.value = false;
    }
};

const generatePDF = async () => {
    try {
        isGeneratingPDF.value = true;
        
        const response = await axios.post('/admin/reports/generate-pdf', {
            date: selectedDate.value
        }, {
            responseType: 'blob'
        });

        // Create a blob link to download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Daily_Deposit_Report_${selectedDate.value}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);

        Swal.fire({
            icon: 'success',
            title: 'Report Generated',
            text: `PDF report for ${selectedDate.value} has been downloaded successfully`,
            timer: 2000
        });
    } catch (error) {
        console.error('Error generating PDF:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to generate PDF. Please try again.'
        });
    } finally {
        isGeneratingPDF.value = false;
    }
};
</script>

<template>
    <AdminLayout>
        <div class="w-full px-8 py-6">
            <!-- Header Section -->
            <div class="mb-8">
                <h1 class="text-4xl font-black text-gray-900 mb-2">Financial Reports</h1>
                <p class="text-gray-600 text-sm font-medium">Generate comprehensive reports for all treasury modules</p>
            </div>

            <!-- Date Selector and Generate Report Button -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
                    <!-- Module Selector -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Select Module</label>
                        <select
                            v-model="selectedModule"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 bg-white text-gray-900 font-medium"
                        >
                            <option value="all">All Modules</option>
                            <option v-for="module in activeModules" :key="module.name" :value="module.name">
                                {{ module.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Date Picker -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Select Report Date</label>
                        <div class="relative">
                            <Calendar class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                            <input
                                v-model="selectedDate"
                                type="date"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Generate Report Buttons -->
                    <div class="flex gap-4 md:col-span-2">
                        <button
                            @click="generateReport"
                            :disabled="isGenerating || isGeneratingPDF"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-gray-900 font-bold rounded-lg shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <Download class="h-5 w-5" />
                            <span>{{ isGenerating ? 'Generating...' : 'CSV Report' }}</span>
                        </button>
                        <button
                            @click="generatePDF"
                            :disabled="isGeneratingPDF || isGenerating"
                            class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-400 to-red-500 hover:from-red-500 hover:to-red-600 text-white font-bold rounded-lg shadow-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <File class="h-5 w-5" />
                            <span>{{ isGeneratingPDF ? 'Generating...' : 'PDF Report' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Module Summaries -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div v-for="module in displayedModules" :key="module.name" class="bg-white rounded-lg border-2 border-gray-200 p-4 shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">{{ module.name }}</p>
                            <p class="text-2xl font-bold text-gray-900">{{ module.data.length }}</p>
                        </div>
                        <FileText class="h-8 w-8 text-yellow-500 opacity-50" />
                    </div>
                </div>
            </div>

            <!-- Module Tables -->
            <div class="space-y-8">
                <div v-for="module in displayedModules" :key="module.name" class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-6 py-4">
                        <h2 class="text-lg font-bold text-gray-900">{{ module.name }}</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead class="bg-gray-100">
                                <tr class="border-b-2 border-gray-300">
                                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">{{ module.name }} Name</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">{{ module.accField === 'reference_number' ? 'Reference Number' : 'Account Number' }}</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Acquisition Date</th>
                                    <th class="px-6 py-3 text-left text-sm font-bold text-gray-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Beginning Balance</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Collection</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Disbursement</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Ending Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="module.data.length === 0" class="border-b border-gray-200">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        No records found
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in module.data" :key="item.id" class="border-b border-gray-200 hover:bg-yellow-50" :class="{ 'bg-gray-50': index % 2 === 0 }">
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ item[module.key] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ item[module.accField] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.acquisition_date) }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ formatMaturityDate(item.maturity_date) }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ formatCurrency(getRollingBeginningBalance(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-green-600 font-semibold text-right">{{ formatCurrency(getCollectionAmount(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-red-600 font-semibold text-right">{{ formatCurrency(getDisbursementAmount(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-blue-600 font-semibold text-right">{{ formatCurrency(getEndingBalance(item)) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
