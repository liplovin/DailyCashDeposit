<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { Calendar, Download, FileText, File, Lock } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const page = usePage();

const selectedDate = ref(new Date().toISOString().split('T')[0]);
const isGenerating = ref(false);
const isGeneratingPDF = ref(false);
const selectedModule = ref('all');
const showPdfDateModal = ref(false);
const pdfReportDate = ref(new Date().toISOString().split('T')[0]);

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
        data: module.data.filter(item => {
            // For Cash Infusion and Other Investment: show if balance > 0 (not fully withdrawn)
            // For others: show if maturity_date exists
            if (module.name === 'Cash Infusion' || module.name === 'Other Investment') {
                return parseFloat(item.beginning_balance || 0) > 0;
            }
            return item.maturity_date !== null;
        })
    }));
});

const displayedModules = computed(() => {
    if (selectedModule.value === 'all') {
        return activeModules.value;
    }
    return activeModules.value.filter(module => module.name === selectedModule.value);
});

const getModuleTotal = (module) => {
    return module.data.reduce((sum, item) => {
        return sum + parseFloat(getEndingBalance(item) || 0);
    }, 0);
};

const getCollectionAmount = (item) => {
    // Check if item has collections relationship array (for Operating Accounts)
    if (item.collections && Array.isArray(item.collections) && item.collections.length > 0) {
        if (!selectedDate.value) {
            return item.collections.reduce((sum, col) => sum + parseFloat(col.collection_amount || 0), 0);
        }
        return item.collections
            .filter(col => {
                const colDate = new Date(col.created_at).toISOString().split('T')[0];
                return colDate === selectedDate.value;
            })
            .reduce((sum, col) => sum + parseFloat(col.collection_amount || 0), 0);
    }
    
    // Check if item has flat collection properties (for other modules)
    if (!selectedDate.value) {
        return item.collection || 0;
    }
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    return collectionDate === selectedDate.value ? (item.collection || 0) : 0;
};

const getDisbursementAmount = (item) => {
    // Check if item has disbursements relationship array (for Operating Accounts)
    if (item.disbursements && Array.isArray(item.disbursements) && item.disbursements.length > 0) {
        if (!selectedDate.value) {
            return item.disbursements.reduce((sum, dis) => sum + parseFloat(dis.amount || 0), 0);
        }
        return item.disbursements
            .filter(dis => {
                const disDate = new Date(dis.created_at).toISOString().split('T')[0];
                return disDate === selectedDate.value;
            })
            .reduce((sum, dis) => sum + parseFloat(dis.amount || 0), 0);
    }
    
    // Check if item has flat disbursement properties (for other modules)
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
    
    // Handle Operating Accounts with relationship arrays
    if (item.collections && Array.isArray(item.collections)) {
        item.collections
            .filter(col => {
                const colDate = new Date(col.created_at).toISOString().split('T')[0];
                return colDate < selectedDate.value;
            })
            .forEach(col => {
                balance += parseFloat(col.collection_amount || 0);
            });
    } else {
        // Handle flat properties for other modules
        const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
        if (collectionDate && collectionDate < selectedDate.value) {
            balance += parseFloat(item.collection || 0);
        }
    }
    
    // Handle disbursements
    if (item.disbursements && Array.isArray(item.disbursements)) {
        item.disbursements
            .filter(dis => {
                const disDate = new Date(dis.created_at).toISOString().split('T')[0];
                return disDate < selectedDate.value;
            })
            .forEach(dis => {
                balance -= parseFloat(dis.amount || 0);
            });
    } else {
        // Handle flat properties for other modules
        const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
        if (disbursementDate && disbursementDate < selectedDate.value) {
            balance -= parseFloat(item.disbursement || 0);
        }
    }
    
    return balance;
};

const getEndingBalance = (item) => {
    const beginning = getRollingBeginningBalance(item);
    const collection = parseFloat(getCollectionAmount(item) || 0);
    const disbursement = parseFloat(getDisbursementAmount(item) || 0);
    return beginning + collection - disbursement;
};

const getModuleTotals = (module) => {
    return {
        beginningBalance: module.data.reduce((sum, item) => sum + parseFloat(getRollingBeginningBalance(item) || 0), 0),
        collection: module.data.reduce((sum, item) => sum + parseFloat(getCollectionAmount(item) || 0), 0),
        disbursement: module.data.reduce((sum, item) => sum + parseFloat(getDisbursementAmount(item) || 0), 0),
        endingBalance: module.data.reduce((sum, item) => sum + parseFloat(getEndingBalance(item) || 0), 0)
    };
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

const showPdfModal = () => {
    showPdfDateModal.value = true;
};

const closePdfModal = () => {
    showPdfDateModal.value = false;
};

const generatePDF = async () => {
    try {
        isGeneratingPDF.value = true;
        
        const response = await axios.post('/admin/reports/generate-pdf', {
            date: pdfReportDate.value
        }, {
            responseType: 'blob'
        });

        // Create a blob link to download
        const url = window.URL.createObjectURL(new Blob([response.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `Daily_Deposit_Report_${pdfReportDate.value}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.parentNode.removeChild(link);
        window.URL.revokeObjectURL(url);

        Swal.fire({
            icon: 'success',
            title: 'Report Generated',
            text: `PDF report for ${pdfReportDate.value} has been downloaded successfully`,
            timer: 2000
        });
        
        closePdfModal();
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
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <!-- Generate Report Buttons -->
                    <div class="flex gap-4 md:col-span-2">
                        <div class="flex-1 relative group">
                            <button
                                disabled
                                class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-gray-300 text-gray-600 font-bold rounded-lg shadow-lg cursor-not-allowed opacity-60 hover:opacity-75 transition-all group-hover:bg-gray-400"
                            >
                                <Lock class="h-5 w-5" />
                                <span>CSV Report</span>
                            </button>
                            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-3 px-4 py-2 bg-gray-900 text-white text-sm font-semibold rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none shadow-lg z-10">
                                Contact us to get this feature
                                <div class="absolute top-full left-1/2 transform -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                            </div>
                        </div>
                        <button
                            @click="showPdfModal"
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
                <div v-for="module in displayedModules" :key="module.name" class="bg-gradient-to-br from-white to-gray-50 rounded-lg border-2 border-yellow-300 p-4 shadow hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 font-semibold">{{ module.name }}</p>
                            <p class="text-2xl font-bold text-gray-900 mb-1">{{ module.data.length }}</p>
                            <p class="text-xs text-gray-500 font-medium">Total: <span class="font-bold text-yellow-600">{{ formatCurrency(getModuleTotal(module)) }}</span></p>
                        </div>
                        <FileText class="h-8 w-8 text-yellow-500 opacity-70" />
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
                                    <th v-if="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts'" class="px-6 py-3 text-left text-sm font-bold text-gray-900">Acquisition Date</th>
                                    <th v-if="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts'" class="px-6 py-3 text-left text-sm font-bold text-gray-900">Maturity Date</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Beginning Balance</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Collection</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Disbursement</th>
                                    <th class="px-6 py-3 text-right text-sm font-bold text-gray-900">Ending Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="module.data.length === 0" class="border-b border-gray-200">
                                    <td :colspan="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts' ? 8 : 6" class="px-6 py-8 text-center text-gray-500">
                                        No records found
                                    </td>
                                </tr>
                                <tr v-for="(item, index) in module.data" :key="item.id" class="border-b border-gray-200 hover:bg-yellow-50" :class="{ 'bg-gray-50': index % 2 === 0 }">
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900">{{ item[module.key] }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-700">{{ item[module.accField] }}</td>
                                    <td v-if="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts'" class="px-6 py-3 text-sm text-gray-700">{{ formatDate(item.acquisition_date) }}</td>
                                    <td v-if="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts'" class="px-6 py-3 text-sm text-gray-700">{{ formatMaturityDate(item.maturity_date) }}</td>
                                    <td class="px-6 py-3 text-sm font-semibold text-gray-900 text-right">{{ formatCurrency(getRollingBeginningBalance(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-green-600 font-semibold text-right">{{ formatCurrency(getCollectionAmount(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-red-600 font-semibold text-right">{{ formatCurrency(getDisbursementAmount(item)) }}</td>
                                    <td class="px-6 py-3 text-sm text-blue-600 font-semibold text-right">{{ formatCurrency(getEndingBalance(item)) }}</td>
                                </tr>
                                <tr v-if="module.data.length > 0" class="bg-gradient-to-r from-yellow-100 to-yellow-50 border-t-2 border-yellow-500 font-bold">
                                    <td :colspan="module.name !== 'Cash Infusion' && module.name !== 'Operating Accounts' ? 4 : 2" class="px-6 py-4 text-right text-gray-900">TOTAL:</td>
                                    <td class="px-6 py-4 text-right text-gray-900">{{ formatCurrency(getModuleTotals(module).beginningBalance) }}</td>
                                    <td class="px-6 py-4 text-right text-green-700">{{ formatCurrency(getModuleTotals(module).collection) }}</td>
                                    <td class="px-6 py-4 text-right text-red-700">{{ formatCurrency(getModuleTotals(module).disbursement) }}</td>
                                    <td class="px-6 py-4 text-right text-blue-700">{{ formatCurrency(getModuleTotals(module).endingBalance) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PDF Date Modal -->
            <div v-if="showPdfDateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl p-8 max-w-md w-full shadow-2xl">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Select Report Date</h3>
                    <p class="text-gray-600 text-sm mb-4">Choose a date to see all collections and disbursements that occurred on that date</p>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Report Date</label>
                        <div class="relative">
                            <Calendar class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                            <input
                                v-model="pdfReportDate"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full pl-12 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200"
                            />
                        </div>
                    </div>
                    
                    <div class="flex gap-3">
                        <button
                            @click="closePdfModal"
                            class="flex-1 px-4 py-3 bg-gray-200 hover:bg-gray-300 text-gray-900 font-bold rounded-lg transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="generatePDF"
                            :disabled="isGeneratingPDF"
                            class="flex-1 px-4 py-3 bg-gradient-to-r from-red-400 to-red-500 hover:from-red-500 hover:to-red-600 text-white font-bold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            {{ isGeneratingPDF ? 'Generating...' : 'Generate PDF' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
