<script setup>
import SuperadminLayout from '@/Layouts/SuperadminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Calendar, CreditCard, X, ChevronLeft, ChevronRight, Trash2, Eye } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

const props = defineProps({
    operatingAccounts: { type: Array, default: () => [] }
});

const searchQuery  = ref('');
const filterDate   = ref('');
const activeTab    = ref('all');
const currentPage  = ref(1);
const perPage      = ref(10);
const showModal    = ref(false);
const selectedRow  = ref(null);

// ── flatten disbursements ──────────────────────────────────────────────────────
const allDisbursements = computed(() => {
    const rows = [];
    props.operatingAccounts.forEach(account => {
        (account.disbursements || []).forEach(d => {
            rows.push({
                ...d,
                accountName:   account.operating_account_name,
                accountNumber: account.account_number,
                accountId:     account.id,
            });
        });
    });
    return rows.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
});

const tabCounts = computed(() => ({
    all:       allDisbursements.value.length,
    pending:   allDisbursements.value.filter(d => d.status === 'pending').length,
    processed: allDisbursements.value.filter(d => d.status === 'processed').length,
}));

watch([searchQuery, filterDate, activeTab, perPage], () => { currentPage.value = 1; });

const filteredDisbursements = computed(() => {
    let result = allDisbursements.value;

    if (activeTab.value !== 'all')
        result = result.filter(d => d.status === activeTab.value);

    if (filterDate.value) {
        const sel = new Date(filterDate.value).toDateString();
        result = result.filter(d => new Date(d.created_at).toDateString() === sel);
    }

    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(d =>
            (d.accountName    || '').toLowerCase().includes(q) ||
            (d.accountNumber  || '').toLowerCase().includes(q) ||
            (d.check_number   || '').toLowerCase().includes(q) ||
            (d.payment_type   || '').toLowerCase().includes(q) ||
            (d.payment_for    || '').toLowerCase().includes(q) ||
            (d.payable_to     || '').toLowerCase().includes(q)
        );
    }
    return result;
});

const filteredTotal = computed(() =>
    filteredDisbursements.value.reduce((s, d) => s + parseFloat(d.amount || 0), 0)
);

// ── pagination ─────────────────────────────────────────────────────────────────
const totalPages = computed(() => Math.max(1, Math.ceil(filteredDisbursements.value.length / perPage.value)));
const pagedRows  = computed(() => {
    const start = (currentPage.value - 1) * perPage.value;
    return filteredDisbursements.value.slice(start, start + perPage.value);
});

const pageNumbers = computed(() => {
    const total = totalPages.value;
    const cur   = currentPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = new Set([1, total, cur]);
    if (cur > 1) pages.add(cur - 1);
    if (cur < total) pages.add(cur + 1);
    return Array.from(pages).sort((a, b) => a - b);
});

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };
const rowIndex = (i) => (currentPage.value - 1) * perPage.value + i + 1;

// ── helpers ────────────────────────────────────────────────────────────────────
const fmt = (v) => parseFloat(v || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const fmtDate = (s) => new Intl.DateTimeFormat('en-US', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit', hour12: true
}).format(new Date(s));

const fmtDateOnly = (s) => new Intl.DateTimeFormat('en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
}).format(new Date(s));

// ── actions ────────────────────────────────────────────────────────────────────
const openDetails = (row) => { selectedRow.value = row; showModal.value = true; };

const deleteDisbursement = (disbursement) => {
    Swal.fire({
        title: 'Delete Disbursement?',
        html: `<p class="text-gray-600 text-sm">You are about to delete the disbursement for <strong>${disbursement.accountName}</strong>.<br>This action cannot be undone.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/superadmin/disbursements/${disbursement.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'The disbursement has been deleted.',
                        icon: 'success',
                        confirmButtonColor: '#2563EB',
                        timer: 1800,
                        showConfirmButton: false,
                    });
                },
                onError: () => {
                    Swal.fire({
                        title: 'Error',
                        text: 'Something went wrong. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#2563EB',
                    });
                },
            });
        }
    });
};
</script>

<template>
    <Head title="Ops Disbursement" />
    <SuperadminLayout>
        <div class="space-y-6">

            <!-- ── Page Header ─────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-black text-gray-900">Ops Disbursement</h1>
                    <p class="text-gray-400 mt-1 text-sm font-medium">All operating account disbursements</p>
                </div>
                <div class="text-right bg-white border border-gray-200 shadow-sm rounded-xl px-6 py-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-0.5">Grand Total</p>
                    <p class="text-2xl font-black text-purple-600">₱{{ fmt(filteredTotal) }}</p>
                </div>
            </div>

            <!-- ── Filter Card ─────────────────────────────────────────── -->
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

                <!-- Tabs -->
                <div class="flex items-center gap-1 px-5 pt-4 pb-0">
                    <button
                        v-for="tab in [
                            { key: 'all',       label: 'All',       dot: 'bg-purple-500' },
                            { key: 'pending',   label: 'Pending',   dot: 'bg-orange-500' },
                            { key: 'processed', label: 'Processed', dot: 'bg-green-500'  },
                        ]"
                        :key="tab.key"
                        @click="activeTab = tab.key"
                        :class="[
                            'relative flex items-center gap-2 px-4 py-2.5 text-sm font-semibold rounded-t-lg transition-all duration-150 border-b-2',
                            activeTab === tab.key
                                ? 'bg-gray-50 text-gray-900 border-purple-600'
                                : 'text-gray-400 border-transparent hover:text-gray-600 hover:bg-gray-50'
                        ]"
                    >
                        <span :class="['w-2 h-2 rounded-full flex-shrink-0', tab.dot]"></span>
                        {{ tab.label }}
                        <span :class="[
                            'text-xs font-bold px-2 py-0.5 rounded-full',
                            activeTab === tab.key ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-400'
                        ]">{{ tabCounts[tab.key] }}</span>
                    </button>
                </div>

                <div class="border-t border-gray-200"></div>

                <!-- Search + Date + Per-page -->
                <div class="flex items-stretch gap-3 px-5 py-4">
                    <div class="flex flex-1 items-center gap-2 border border-gray-300 rounded-lg px-3 py-2.5 focus-within:ring-2 focus-within:ring-purple-500 focus-within:border-transparent transition-all bg-white">
                        <Search class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by account, check number, payment type, payment for, payable to…"
                            class="flex-1 text-sm text-gray-700 placeholder-gray-400 border-0 outline-none focus:ring-0 bg-transparent min-w-0"
                        />
                        <button v-if="searchQuery" @click="searchQuery = ''" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="flex items-center gap-2 border border-gray-300 rounded-lg px-3 py-2.5 focus-within:ring-2 focus-within:ring-purple-500 focus-within:border-transparent transition-all bg-white flex-shrink-0">
                        <Calendar class="h-4 w-4 text-gray-400 flex-shrink-0" />
                        <input
                            v-model="filterDate"
                            type="date"
                            class="text-sm text-gray-700 border-0 outline-none focus:ring-0 bg-transparent w-36"
                        />
                        <button v-if="filterDate" @click="filterDate = ''" class="text-gray-400 hover:text-gray-600 flex-shrink-0">
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <select
                        v-model="perPage"
                        class="flex-shrink-0 border border-gray-300 rounded-lg px-3 py-2.5 text-sm font-medium text-gray-700 bg-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all w-32"
                    >
                        <option :value="10">10 / page</option>
                        <option :value="25">25 / page</option>
                        <option :value="50">50 / page</option>
                        <option :value="100">100 / page</option>
                    </select>
                </div>
            </div>

            <!-- ── Empty: no data ──────────────────────────────────────── -->
            <div v-if="allDisbursements.length === 0"
                class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl border-2 border-purple-100 py-20 text-center">
                <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <CreditCard class="h-8 w-8 text-purple-500" />
                </div>
                <h2 class="text-xl font-bold text-gray-800 mb-1">No Disbursements Yet</h2>
                <p class="text-gray-500 text-sm">No operating account disbursements have been recorded.</p>
            </div>

            <!-- ── Empty: no filter results ────────────────────────────── -->
            <div v-else-if="filteredDisbursements.length === 0"
                class="bg-gray-50 rounded-2xl border-2 border-gray-200 py-16 text-center">
                <div class="w-14 h-14 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                    <Search class="h-7 w-7 text-gray-500" />
                </div>
                <h2 class="text-lg font-bold text-gray-700 mb-1">No Results Found</h2>
                <p class="text-gray-400 text-sm">Try adjusting your search terms or filters.</p>
            </div>

            <!-- ── Table ───────────────────────────────────────────────── -->
            <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

                <!-- Meta bar -->
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm text-gray-500">
                        Showing
                        <span class="font-bold text-gray-800">{{ (currentPage - 1) * perPage + 1 }}</span>
                        –
                        <span class="font-bold text-gray-800">{{ Math.min(currentPage * perPage, filteredDisbursements.length) }}</span>
                        of
                        <span class="font-bold text-gray-800">{{ filteredDisbursements.length }}</span>
                        record{{ filteredDisbursements.length !== 1 ? 's' : '' }}
                    </p>
                    <p class="text-sm text-gray-500">
                        Page total:
                        <span class="font-black text-purple-700">
                            ₱{{ fmt(pagedRows.reduce((s, d) => s + parseFloat(d.amount || 0), 0)) }}
                        </span>
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-purple-600 to-purple-700 text-white text-xs font-bold uppercase tracking-wide">
                                <th class="px-5 py-4 text-center w-12 border-r border-purple-500">#</th>
                                <th class="px-5 py-4 text-left border-r border-purple-500">Operating Account</th>
                                <th class="px-5 py-4 text-left border-r border-purple-500">Check Number</th>
                                <th class="px-5 py-4 text-left border-r border-purple-500">Payment Type</th>
                                <th class="px-5 py-4 text-left border-r border-purple-500">Date</th>
                                <th class="px-5 py-4 text-right border-r border-purple-500">Amount</th>
                                <th class="px-5 py-4 text-center border-r border-purple-500">Status</th>
                                <th class="px-5 py-4 text-left border-r border-purple-500">Created</th>
                                <th class="px-5 py-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(disbursement, i) in pagedRows"
                                :key="disbursement.id"
                                :class="[
                                    'border-b border-gray-100 transition-colors duration-150',
                                    i % 2 === 0 ? 'bg-white hover:bg-purple-50' : 'bg-gray-50/60 hover:bg-purple-50'
                                ]"
                            >
                                <td class="px-5 py-4 text-sm text-center text-gray-400 font-semibold border-r border-gray-100 w-12">
                                    {{ rowIndex(i) }}
                                </td>

                                <td class="px-5 py-4 border-r border-gray-100">
                                    <p class="text-sm font-bold text-gray-900 leading-tight">{{ disbursement.accountName }}</p>
                                    <p class="text-xs text-gray-400 font-mono mt-0.5">{{ disbursement.accountNumber }}</p>
                                </td>

                                <td class="px-5 py-4 text-sm font-mono font-semibold text-gray-700 border-r border-gray-100">
                                    {{ disbursement.check_number || '—' }}
                                </td>

                                <td class="px-5 py-4 border-r border-gray-100">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-xs font-bold">
                                        {{ disbursement.payment_type || 'CHECK' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-sm text-gray-700 border-r border-gray-100 whitespace-nowrap">
                                    {{ disbursement.date ? fmtDateOnly(disbursement.date) : '—' }}
                                </td>

                                <td class="px-5 py-4 text-sm font-black text-gray-900 text-right border-r border-gray-100 whitespace-nowrap">
                                    ₱{{ fmt(disbursement.amount) }}
                                </td>

                                <td class="px-5 py-4 text-center border-r border-gray-100">
                                    <span :class="[
                                        'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold whitespace-nowrap',
                                        disbursement.status === 'processed'
                                            ? 'bg-green-100 text-green-700 border border-green-200'
                                            : 'bg-orange-100 text-orange-700 border border-orange-200'
                                    ]">
                                        <span :class="[
                                            'w-1.5 h-1.5 rounded-full flex-shrink-0',
                                            disbursement.status === 'processed' ? 'bg-green-500' : 'bg-orange-500'
                                        ]"></span>
                                        {{ disbursement.status === 'processed' ? 'Processed' : 'Pending' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-xs text-gray-500 border-r border-gray-100 whitespace-nowrap">
                                    {{ fmtDate(disbursement.created_at) }}
                                </td>

                                <td class="px-5 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button
                                            @click="openDetails(disbursement)"
                                            class="inline-flex items-center px-3 py-1.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold transition-colors duration-150 shadow-sm"
                                        >
                                            View
                                        </button>
                                        <button
                                            @click="deleteDisbursement(disbursement)"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-700 border border-red-200 hover:border-red-300 transition-colors duration-150"
                                            title="Delete disbursement"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>

                        <tfoot>
                            <tr class="bg-purple-600 text-white font-bold">
                                <td colspan="5" class="px-5 py-4 text-sm border-r border-purple-500">
                                    Grand Total — {{ filteredDisbursements.length }} record{{ filteredDisbursements.length !== 1 ? 's' : '' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-right border-r border-purple-500 whitespace-nowrap">
                                    ₱{{ fmt(filteredTotal) }}
                                </td>
                                <td colspan="3" class="px-5 py-4"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- ── Pagination ──────────────────────────────────────── -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500 order-2 sm:order-1">
                        Page <span class="font-bold text-gray-800">{{ currentPage }}</span>
                        of <span class="font-bold text-gray-800">{{ totalPages }}</span>
                    </p>

                    <div class="flex items-center gap-1 order-1 sm:order-2">
                        <button
                            @click="prevPage"
                            :disabled="currentPage === 1"
                            :class="[
                                'flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150',
                                currentPage === 1
                                    ? 'bg-gray-100 text-gray-300 cursor-not-allowed'
                                    : 'bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600'
                            ]"
                        >
                            <ChevronLeft class="h-4 w-4" /> Prev
                        </button>

                        <template v-for="(page, idx) in pageNumbers" :key="page">
                            <span v-if="idx > 0 && page - pageNumbers[idx - 1] > 1" class="px-2 text-gray-400 select-none">…</span>
                            <button
                                @click="currentPage = page"
                                :class="[
                                    'w-10 h-10 rounded-lg text-sm font-bold transition-all duration-150',
                                    currentPage === page
                                        ? 'bg-purple-600 text-white shadow-sm'
                                        : 'bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600'
                                ]"
                            >{{ page }}</button>
                        </template>

                        <button
                            @click="nextPage"
                            :disabled="currentPage === totalPages"
                            :class="[
                                'flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-150',
                                currentPage === totalPages
                                    ? 'bg-gray-100 text-gray-300 cursor-not-allowed'
                                    : 'bg-white border border-gray-300 text-gray-600 hover:bg-purple-50 hover:border-purple-400 hover:text-purple-600'
                            ]"
                        >
                            Next <ChevronRight class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

        </div>

        <!-- ── Detail Modal ────────────────────────────────────────────── -->
        <div v-if="showModal && selectedRow" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click="showModal = false">
            <div class="bg-white rounded-xl shadow-2xl w-full mx-4 max-w-lg max-h-[85vh] overflow-hidden flex flex-col" @click.stop>

                <!-- Header -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-5 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h2 class="text-xl font-bold text-white">Disbursement Details</h2>
                        <p class="text-purple-100 text-sm mt-0.5">{{ selectedRow.accountName }}</p>
                    </div>
                    <button @click="showModal = false" class="text-white hover:bg-purple-700 p-1.5 rounded-lg transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Body -->
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-3">

                    <!-- Status -->
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <span class="text-sm font-semibold text-gray-500">Status</span>
                        <span :class="[
                            'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold',
                            selectedRow.status === 'processed'
                                ? 'bg-green-100 text-green-700 border border-green-200'
                                : 'bg-orange-100 text-orange-700 border border-orange-200'
                        ]">
                            <span :class="['w-1.5 h-1.5 rounded-full', selectedRow.status === 'processed' ? 'bg-green-500' : 'bg-orange-500']"></span>
                            {{ selectedRow.status === 'processed' ? 'Processed' : 'Pending' }}
                        </span>
                    </div>

                    <!-- Account -->
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                        <p class="text-xs font-bold text-purple-400 uppercase tracking-wider mb-1">Operating Account</p>
                        <p class="text-sm font-bold text-gray-900">{{ selectedRow.accountName }}</p>
                        <p class="text-xs font-mono text-gray-400 mt-0.5">{{ selectedRow.accountNumber }}</p>
                    </div>

                    <!-- Amount -->
                    <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 flex items-center justify-between">
                        <p class="text-sm font-semibold text-gray-600">Amount</p>
                        <p class="text-2xl font-black text-purple-700">₱{{ fmt(selectedRow.amount) }}</p>
                    </div>

                    <!-- Check Number -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-sm font-semibold text-gray-500">Check Number</span>
                        <span class="text-sm font-mono font-bold text-gray-800">{{ selectedRow.check_number || '—' }}</span>
                    </div>

                    <!-- Payment Type -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-sm font-semibold text-gray-500">Payment Type</span>
                        <span class="text-sm font-bold text-gray-800">{{ selectedRow.payment_type || 'CHECK' }}</span>
                    </div>

                    <!-- Date -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-sm font-semibold text-gray-500">Date</span>
                        <span class="text-sm font-semibold text-gray-800">{{ selectedRow.date ? fmtDateOnly(selectedRow.date) : '—' }}</span>
                    </div>

                    <!-- Payments breakdown -->
                    <div v-if="selectedRow.payments && selectedRow.payments.length > 0" class="space-y-2">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider pt-1">Payment Breakdown ({{ selectedRow.payments.length }})</p>
                        <div
                            v-for="(payment, idx) in selectedRow.payments"
                            :key="payment.id"
                            class="p-4 bg-purple-50 rounded-xl border border-purple-100 space-y-2"
                        >
                            <p class="text-xs font-bold text-purple-500 uppercase">Payment {{ idx + 1 }}</p>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">For:</span>
                                <span class="text-gray-900 font-semibold">{{ payment.payment_for || '—' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Payable To:</span>
                                <span class="text-gray-900 font-semibold">{{ payment.payable_to || '—' }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 font-medium">Amount:</span>
                                <span class="font-black text-purple-700">₱{{ fmt(payment.amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Created at -->
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 rounded-xl border border-gray-200">
                        <span class="text-sm font-semibold text-gray-500">Created</span>
                        <span class="text-xs text-gray-600 font-medium">{{ fmtDate(selectedRow.created_at) }}</span>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end flex-shrink-0">
                    <button
                        @click="showModal = false"
                        class="px-6 py-2.5 rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-bold text-sm transition-colors shadow-sm"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

    </SuperadminLayout>
</template>
