<script setup>
import TreasuryLayout from '@/Layouts/TreasuryLayout.vue';
import CreateCorporateBondModal from './Create.vue';
import EditCorporateBondModal from './Edit.vue';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

onMounted(() => {
    document.title = 'Corporate Bonds Management - Daily Deposit';
});

const props = defineProps({
    corporateBonds: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const showModal = ref(false);
const showEditModal = ref(false);
const selectedCorporateBond = ref(null);
const draggedIndex = ref(null);

const hasCorporateBonds = computed(() => filteredCorporateBonds.value && filteredCorporateBonds.value.length > 0);

const filteredCorporateBonds = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.corporateBonds;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.corporateBonds.filter(bond => 
        bond.corporate_bond_name.toLowerCase().includes(query) ||
        bond.account_number.toLowerCase().includes(query)
    );
});

const openModal = () => {
    selectedCorporateBond.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedCorporateBond.value = null;
};

const openEditModal = (bond) => {
    selectedCorporateBond.value = bond;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedCorporateBond.value = null;
};

const totalBeginningBalance = computed(() => {
    return filteredCorporateBonds.value.reduce((sum, bond) => {
        return sum + parseFloat(bond.beginning_balance || 0);
    }, 0);
});

const handleDragStart = (index, event) => {
    draggedIndex.value = index;
    event.dataTransfer.effectAllowed = 'move';
    // Create a custom drag image
    const dragImage = new Image();
    dragImage.src = 'data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><rect fill="%23FBBF24" width="40" height="40" rx="8"/><text x="20" y="24" fill="%23fff" font-size="20" text-anchor="middle" font-weight="bold" font-family="Arial">\u21d5</text></svg>';
    event.dataTransfer.setDragImage(dragImage, 20, 20);
};

const handleDragOver = (event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
};

const handleDrop = (targetIndex) => {
    if (draggedIndex.value !== null && draggedIndex.value !== targetIndex) {
        const temp = props.corporateBonds[draggedIndex.value];
        props.corporateBonds[draggedIndex.value] = props.corporateBonds[targetIndex];
        props.corporateBonds[targetIndex] = temp;
    }
    draggedIndex.value = null;
};

const handleDragEnd = () => {
    draggedIndex.value = null;
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

const deleteCorporateBond = async (bond) => {
    const result = await Swal.fire({
        title: 'Delete Corporate Bond?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>Corporate Bond:</strong> ${bond.corporate_bond_name}</p>
                <p class="mb-3"><strong>Account:</strong> ${bond.account_number}</p>
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
        router.delete(`/treasury/corporate-bonds/${bond.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Corporate Bond has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete corporate bond. Please try again.',
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
                        <h1 class="text-3xl font-black text-gray-900">Corporate Bonds Management</h1>
                        <p class="text-gray-600 mt-1">Manage and track your corporate bonds</p>
                    </div>
                    <button
                        @click="openModal"
                        class="flex items-center space-x-2 px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create New</span>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by corporate bond name or account number..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasCorporateBonds && filteredCorporateBonds.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Corporate Bonds Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by creating your first corporate bond.</p>
                    <button
                        @click="openModal"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Create First Corporate Bond</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredCorporateBonds.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No corporate bonds match your search query.</p>
                </div>
            </div>

            <!-- Corporate Bonds Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300 cursor-move">⋮⋮ Corporate Bond Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Account Number</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Beginning Balance</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Maturity Date</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(bond, index) in filteredCorporateBonds" 
                                :key="bond.id"
                                draggable="true"
                                @dragstart="handleDragStart(index, $event)"
                                @dragover="handleDragOver"
                                @drop="handleDrop(index)"
                                @dragend="handleDragEnd"
                                :class="[
                                    'relative transition-all duration-300 ease-out cursor-move select-none',
                                    'border-b border-gray-300',
                                    draggedIndex === index ? 'dragging-row opacity-40 scale-95 bg-yellow-200' : index % 2 === 0 ? 'bg-white' : 'bg-gray-50',
                                    draggedIndex !== null && draggedIndex !== index ? 'hover:ring-2 hover:ring-yellow-400 ring-inset' : 'hover:bg-yellow-100'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 bg-yellow-500 rounded-full mr-3"></div>
                                        {{ bond.corporate_bond_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-300">{{ bond.account_number }}</td>
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                    ₱ {{ parseFloat(bond.beginning_balance).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ formatMaturityDate(bond.maturity_date) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ new Date(bond.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(bond)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteCorporateBond(bond)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-yellow-50 border-t-2 border-gray-300 font-bold">
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">SUB-TOTAL CORPORATE BONDS</td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300">
                                    ₱ {{ totalBeginningBalance.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900 border-r border-gray-300"></td>
                                <td class="px-6 py-4 text-sm text-gray-900"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-300">
                    <p class="text-sm text-gray-600">Total: <span class="font-semibold text-gray-900">{{ filteredCorporateBonds.length }}</span> corporate bond(s)</p>
                </div>
            </div>

            <!-- Create Modal -->
            <CreateCorporateBondModal :isOpen="showModal" :existingCorporateBonds="props.corporateBonds" @close="closeModal" />

            <!-- Edit Modal -->
            <EditCorporateBondModal :isOpen="showEditModal" :corporateBond="selectedCorporateBond" :existingCorporateBonds="props.corporateBonds" @close="closeEditModal" />
        </div>
    </TreasuryLayout>
</template>

<style scoped>
@keyframes dragPulse {
    0%, 100% {
        box-shadow: inset 0 0 0 rgba(251, 191, 36, 0);
    }
    50% {
        box-shadow: inset 0 0 8px rgba(251, 191, 36, 0.3);
    }
}

@keyframes slideDown {
    from {
        transform: translateY(-8px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

tr {
    animation: slideDown 0.3s ease-out;
}

.dragging-row {
    animation: dragPulse 0.6s ease-in-out infinite;
    box-shadow: 0 8px 16px rgba(251, 191, 36, 0.3);
    border-radius: 4px;
}

tbody tr:hover td:first-child {
    font-weight: 600;
    color: #1f2937;
}
</style>
