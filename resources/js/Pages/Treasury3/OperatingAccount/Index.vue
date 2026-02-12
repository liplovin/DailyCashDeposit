<script setup>
import Treasury3Layout from '@/Layouts/Treasury3Layout.vue';
import AddCollectionModal from './AddCollection.vue';
import { Head } from '@inertiajs/vue3';
import { Plus } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    operatingAccounts: {
        type: Array,
        default: () => []
    }
});

const showModal = ref(false);
const selectedAccount = ref(null);

const handleAddCollection = (account) => {
    selectedAccount.value = account;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedAccount.value = null;
};
</script>

<template>
    <Head title="Operating Accounts - Treasury 3" />
    <Treasury3Layout>
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Operating Accounts</h1>
                    <p class="text-gray-600 mt-2">View all available operating accounts</p>
                </div>
                <button
                    @click="handleAddCollection(null)"
                    class="px-6 py-3 rounded-lg bg-yellow-500 hover:bg-yellow-600 text-white font-semibold transition-all duration-200 flex items-center gap-2 shadow-lg hover:shadow-xl"
                >
                    <Plus class="h-5 w-5" />
                    Add Collection
                </button>
            </div>

            <!-- Coming Soon -->
            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl shadow-lg border-2 border-yellow-200 p-16 text-center">
                <div class="max-w-md mx-auto">
                    <div class="text-6xl mb-4">🏦</div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-3">Operating Accounts</h2>
                    <p class="text-lg text-gray-600 mb-2">Manage collection deposits for your operating accounts</p>
                    <p class="text-sm text-gray-500">Feature coming soon</p>
                </div>
            </div>
        </div>
    </Treasury3Layout>

    <!-- Add Collection Modal -->
    <AddCollectionModal :isOpen="showModal" :account="selectedAccount" :operatingAccounts="operatingAccounts" @close="closeModal" />
</template>
