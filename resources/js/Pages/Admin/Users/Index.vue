<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CreateUserModal from './Create.vue';
import EditUserModal from './Edit.vue';
import { Head } from '@inertiajs/vue3';
import { Plus, Trash2, Edit2, Search } from 'lucide-vue-next';
import { computed, ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';

onMounted(() => {
    document.title = 'User Management - Daily Deposit';
});

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const showModal = ref(false);
const showEditModal = ref(false);
const selectedUser = ref(null);

const hasUsers = computed(() => filteredUsers.value && filteredUsers.value.length > 0);

const filteredUsers = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.users;
    }
    
    const query = searchQuery.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        user.role.toLowerCase().includes(query)
    );
});

const openModal = () => {
    selectedUser.value = null;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedUser.value = null;
};

const openEditModal = (user) => {
    selectedUser.value = user;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
    selectedUser.value = null;
};

const getRoleBadgeClass = (role) => {
    const classes = {
        'admin': 'bg-red-100 text-red-800',
        'treasury': 'bg-blue-100 text-blue-800',
        'treasury2': 'bg-cyan-100 text-cyan-800',
        'accounting': 'bg-purple-100 text-purple-800'
    };
    return classes[role] || 'bg-gray-100 text-gray-800';
};

const getRoleColor = (role) => {
    const colors = {
        'admin': '#DC2626',
        'treasury': '#2563EB',
        'treasury2': '#0891B2',
        'accounting': '#A855F7'
    };
    return colors[role] || '#6B7280';
};

const deleteUser = async (user) => {
    const result = await Swal.fire({
        title: 'Delete User?',
        html: `
            <div class="text-left">
                <p class="mb-3"><strong>User:</strong> ${user.name}</p>
                <p class="mb-3"><strong>Email:</strong> ${user.email}</p>
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
        router.delete(`/users/${user.id}`, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Deleted!',
                    text: 'User has been deleted successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
            },
            onError: () => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to delete user. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#F59E0B'
                });
            }
        });
    }
};
</script>

<template>
    <Head title="User Management" />
    <AdminLayout>
        <div class="w-full px-8">
            <!-- Header Section -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-black text-gray-900">User Management</h1>
                        <p class="text-gray-600 mt-1">Manage system users and their roles</p>
                    </div>
                    <button
                        @click="openModal"
                        class="flex items-center space-x-2 px-6 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold shadow-md hover:shadow-lg"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Add User</span>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="relative">
                    <Search class="absolute left-4 top-3 h-5 w-5 text-gray-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, email or role..."
                        class="w-full pl-12 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="!hasUsers && filteredUsers.length === 0" class="max-w-6xl">
                <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-2xl border-2 border-yellow-200 p-12 text-center shadow-lg">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-yellow-200 rounded-full flex items-center justify-center mb-4">
                            <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Users Yet</h2>
                    <p class="text-gray-600 mb-6">Get started by adding your first user account.</p>
                    <button
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-all duration-200 font-semibold"
                    >
                        <Plus class="h-5 w-5" />
                        <span>Add First User</span>
                    </button>
                </div>
            </div>

            <!-- No Search Results -->
            <div v-else-if="searchQuery && filteredUsers.length === 0" class="max-w-6xl">
                <div class="bg-blue-50 rounded-2xl border-2 border-blue-200 p-12 text-center">
                    <div class="inline-block">
                        <div class="w-16 h-16 bg-blue-200 rounded-full flex items-center justify-center mb-4">
                            <Search class="h-8 w-8 text-blue-600" />
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">No Results Found</h2>
                    <p class="text-gray-600">No users match your search query.</p>
                </div>
            </div>

            <!-- Users Table -->
            <div v-else class="bg-white rounded-xl border-2 border-gray-300 shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gradient-to-r from-yellow-400 to-yellow-500">
                            <tr class="border-b-2 border-gray-300">
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Name</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Email</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Role</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white border-r border-gray-300">Created</th>
                                <th class="px-6 py-4 text-left text-sm font-bold text-white">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr 
                                v-for="(user, index) in filteredUsers" 
                                :key="user.id"
                                :class="[
                                    'relative transition-all duration-300 ease-out',
                                    'border-b border-gray-300',
                                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50',
                                    'hover:bg-yellow-100'
                                ]"
                            >
                                <td class="px-6 py-4 text-sm text-gray-900 font-semibold border-r border-gray-300">
                                    <div class="flex items-center">
                                        <div class="w-2 h-2 rounded-full mr-3" :style="{ backgroundColor: getRoleColor(user.role) }"></div>
                                        {{ user.name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-mono border-r border-gray-300">{{ user.email }}</td>
                                <td class="px-6 py-4 text-sm border-r border-gray-300">
                                    <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getRoleBadgeClass(user.role)]">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 border-r border-gray-300">
                                    {{ new Date(user.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center space-x-2">
                                        <button
                                            @click="openEditModal(user)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-blue-600 hover:bg-blue-100 rounded-lg transition-all duration-200"
                                            title="Edit"
                                        >
                                            <Edit2 class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteUser(user)"
                                            class="inline-flex items-center justify-center space-x-1 w-9 h-9 text-red-600 hover:bg-red-100 rounded-lg transition-all duration-200"
                                            title="Delete"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t-2 border-gray-300">
                    <p class="text-sm text-gray-600">Total: <span class="font-semibold text-gray-900">{{ filteredUsers.length }}</span> user(s)</p>
                </div>
            </div>

            <!-- Create User Modal -->
            <CreateUserModal :isOpen="showModal" @close="closeModal" />

            <!-- Edit User Modal -->
            <EditUserModal :isOpen="showEditModal" :user="selectedUser" @close="closeEditModal" />
        </div>
    </AdminLayout>
</template>

<style scoped>
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

tbody tr:hover td:first-child {
    font-weight: 600;
    color: #1f2937;
}
</style>
