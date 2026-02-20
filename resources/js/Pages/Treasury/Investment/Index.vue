<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateInvestmentModal from './Create.vue';
import EditInvestmentModal from './Edit.vue';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

onMounted(() => {
    document.title = 'Investment Management - Daily Deposit';
});

const props = defineProps({
    investments: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const filterDate = ref(new Date().toISOString().split('T')[0]);
const showModal = ref(false);
const showEditModal = ref(false);
const selectedInvestment = ref(null);

watch(filterDate, (newDate) => {
    const url = new URL(window.location);
    url.searchParams.set('date', newDate);
    window.history.pushState({}, '', url);
});

const formatCurrency = (amount) => {
    return '₱ ' + parseFloat(amount || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const getCollectionAmount = (item) => {
    if (!item.collection_date) return 0;
    const collectionDate = new Date(item.collection_date).toISOString().split('T')[0];
    const selectedDate = filterDate.value;
    return collectionDate === selectedDate ? parseFloat(item.collection || 0) : 0;
};

const getDisbursementAmount = (item) => {
    if (!item.disbursement_date) return 0;
    const disbursementDate = new Date(item.disbursement_date).toISOString().split('T')[0];
    const selectedDate = filterDate.value;
    return disbursementDate === selectedDate ? parseFloat(item.disbursement || 0) : 0;
};

const getRollingBeginningBalance = (item, selectedDate) => {
    let balance = parseFloat(item.beginning_balance || 0);
    
    const collectionDate = item.collection_date ? new Date(item.collection_date).toISOString().split('T')[0] : null;
    if (collectionDate && collectionDate < selectedDate) {
        balance += parseFloat(item.collection || 0);
    }
    
    const disbursementDate = item.disbursement_date ? new Date(item.disbursement_date).toISOString().split('T')[0] : null;
    if (disbursementDate && disbursementDate < selectedDate) {
        balance -= parseFloat(item.disbursement || 0);
    }
    
    return balance;
};

const filteredItems = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.investments;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.investments.filter(investment => 
        investment.investment_name.toLowerCase().includes(query) ||
        investment.reference_number.toLowerCase().includes(query)
    );
});

const openModal = () => {
    selectedInvestment.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedInvestment.value = null;
};

const openEditModal = (investment) => {
    selectedInvestment.value = investment;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedInvestment.value = null;
};

const totalBeginningBalance = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + getRollingBeginningBalance(item, filterDate.value);
    }, 0);
});

const totalCollection = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + parseFloat(getCollectionAmount(item));
    }, 0);
});

const totalDisbursement = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        return sum + parseFloat(getDisbursementAmount(item));
    }, 0);
});

const totalEndingBalance = computed(() => {
    return filteredItems.value.reduce((sum, item) => {
        const beginning = getRollingBeginningBalance(item, filterDate.value);
        const collection = parseFloat(getCollectionAmount(item));
        const disbursement = parseFloat(getDisbursementAmount(item));
        return sum + (beginning + collection - disbursement);
    }, 0);
});

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

const formatMaturityDate = (dateString) => {
    if (!dateString) return 'N/A';
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

const renewInvestment = async (investment) => {
    const result = await Swal.fire({
        title: 'Renew Investment?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Investment:</strong> ${investment.investment_name}</p>
                <p class="mb-3"><strong>Reference:</strong> ${investment.reference_number}</p>
                <p class="text-green-600 text-sm"><strong>✓ This will renew the investment.</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10B981',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Renew',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    if (result.isConfirmed) {
        router.post(`/treasury/investment/${investment.id}/renew`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Investment has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to renew investment. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const withdrawInvestment = async (investment) => {
    const result = await Swal.fire({
        title: 'Withdraw Investment?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Investment:</strong> ${investment.investment_name}</p>
                <p class="mb-3"><strong>Reference:</strong> ${investment.reference_number}</p>
                <p class="text-orange-600 text-sm"><strong>✓ This will withdraw the investment amount.</strong></p>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#F97316',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, Withdraw',
        cancelButtonText: 'Cancel',
        allowOutsideClick: false,
        allowEscapeKey: false
    });

    if (result.isConfirmed) {
        router.post(`/treasury/investment/${investment.id}/withdraw`, {}, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Withdrawn!',
                    text: 'Investment has been withdrawn successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to withdraw investment. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};

const deleteInvestment = async (investment) => {
    const result = await Swal.fire({
        title: 'Delete Investment?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Investment:</strong> ${investment.investment_name}</p>
                <p class="mb-3"><strong>Reference:</strong> ${investment.reference_number}</p>
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
        router.delete(`/treasury/investment/${investment.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Investment has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete investment. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};
</script>

<template>
    <TreasuryLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">Investment Management</h1>
                        <p class="text-gray-600 mt-1">Manage and track your investments</p>
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

            <!-- Filter Section -->
            <div class="bg-yellow-50 rounded-xl border-2 border-yellow-200 shadow-md p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Search Bar -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Search Investment</label>
                        <div class="relative">
                            <Search class="absolute left-4 top-3.5 h-5 w-5 text-gray-400" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by investment name or reference number..."
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
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Ending Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Action</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredItems.length === 0" class="border-b border-gray-200">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No investment records found.
                                </td>
                            </tr>
                            <tr 
                                v-for="(item, index) in filteredItems" 
                                :key="item.id"
                                :class="[
                                    'border-b border-gray-200 hover:bg-yellow-50 transition-colors duration-150',
                                    isOverdueOrDueToday(item.maturity_date) ? 'blink-red' : (index % 2 === 0 ? 'bg-white' : 'bg-gray-50')
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                                        {{ item.investment_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ item.reference_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-200">
                                    {{ item.maturity_date ? new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(item.maturity_date)) : 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(item, filterDate)) }}</td>
                                <td class="px-6 py-4 text-sm text-blue-600 font-semibold border-r border-gray-200">{{ formatCurrency(getRollingBeginningBalance(item, filterDate) + parseFloat(getCollectionAmount(item)) - parseFloat(getDisbursementAmount(item))) }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-200">
                                    <div v-if="isMaturityActionVisible(item.maturity_date)" class="flex items-center space-x-2">
                                        <button
                                            @click="renewInvestment(item)"
                                            class="px-3 py-1.5 bg-green-500 text-white text-xs font-semibold rounded-lg hover:bg-green-600 transition-all duration-200"
                                            title="Renew"
                                        >
                                            Renew
                                        </button>
                                        <button
                                            @click="withdrawInvestment(item)"
                                            class="px-3 py-1.5 bg-orange-500 text-white text-xs font-semibold rounded-lg hover:bg-orange-600 transition-all duration-200"
                                            title="Withdraw"
                                        >
                                            Withdraw
                                        </button>
                                    </div>
                                    <div v-else class="text-xs text-gray-500">—</div>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(item)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteInvestment(item)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="filteredItems.length > 0">
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
            </div>

            <!-- Create Modal -->
            <CreateInvestmentModal :isOpen="showModal" :existingInvestments="props.investments" @close="closeModal" />

            <!-- Edit Modal -->
            <EditInvestmentModal :isOpen="showEditModal" :investment="selectedInvestment" :existingInvestments="props.investments" @close="closeEditModal" />
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
