<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close']);

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'treasury'
});

const errors = ref({});
const isSubmitting = ref(false);

const roles = [
    { value: 'admin', label: 'Admin' },
    { value: 'treasury', label: 'Treasury' },
    { value: 'treasury2', label: 'Treasury 2' },
    { value: 'treasury3', label: 'Treasury 3' },
    { value: 'accounting', label: 'Accounting' },
    { value: 'accounting2', label: 'Accounting 2' }
];

const handleSubmit = () => {
    errors.value = {};
    
    // Validation
    if (!form.value.name.trim()) {
        errors.value.name = 'Name is required';
    }
    if (!form.value.email.trim()) {
        errors.value.email = 'Email is required';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.value.email = 'Email must be valid';
    }
    if (!form.value.password) {
        errors.value.password = 'Password is required';
    } else if (form.value.password.length < 8) {
        errors.value.password = 'Password must be at least 8 characters';
    }
    if (!form.value.password_confirmation) {
        errors.value.password_confirmation = 'Password confirmation is required';
    } else if (form.value.password !== form.value.password_confirmation) {
        errors.value.password_confirmation = 'Passwords do not match';
    }
    if (!form.value.role) {
        errors.value.role = 'Role is required';
    }
    
    if (Object.keys(errors.value).length === 0) {
        isSubmitting.value = true;
        
        router.post('/users', {
            name: form.value.name,
            email: form.value.email,
            password: form.value.password,
            password_confirmation: form.value.password_confirmation,
            role: form.value.role
        }, {
            onSuccess: () => {
                Swal.fire({
                    title: 'Created!',
                    text: 'User has been created successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                });
                closeModal();
                isSubmitting.value = false;
            },
            onError: (err) => {
                if (err.email) {
                    errors.value.email = err.email;
                }
                if (err.name) {
                    errors.value.name = err.name;
                }
                if (err.password) {
                    errors.value.password = err.password;
                }
                isSubmitting.value = false;
            }
        });
    }
};

const closeModal = () => {
    form.value = { name: '', email: '', password: '', password_confirmation: '', role: 'treasury' };
    errors.value = {};
    emit('close');
};
</script>

<template>
    <!-- Modal Backdrop -->
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <!-- Modal -->
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <!-- Modal Header -->
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Create New User</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Name Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Full Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Enter full name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.name }"
                        />
                        <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="Enter email address"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.email }"
                        />
                        <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="Enter password (min 8 characters)"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.password }"
                        />
                        <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Confirm Password <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Confirm password"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.password_confirmation }"
                        />
                        <p v-if="errors.password_confirmation" class="text-red-500 text-sm mt-1">{{ errors.password_confirmation }}</p>
                    </div>

                    <!-- Role Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            User Role <span class="text-red-500">*</span>
                        </label>
                        <select
                            v-model="form.role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 bg-white"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.role }"
                        >
                            <option v-for="role in roles" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                        <p v-if="errors.role" class="text-red-500 text-sm mt-1">{{ errors.role }}</p>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex space-x-3 pt-4 border-t border-gray-200">
                        <button
                            type="button"
                            @click="closeModal"
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            class="flex-1 px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Creating...' : 'Create User' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
