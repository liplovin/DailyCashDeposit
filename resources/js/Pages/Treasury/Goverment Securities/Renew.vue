<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    governmentSecurity: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = ref({
    maturity_date: '',
    explanation: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Watch for government security changes and populate form
watch(() => props.governmentSecurity, (newSecurity) => {
    if (newSecurity && props.isOpen) {
        form.value = {
            maturity_date: formatDateForInput(newSecurity.maturity_date),
            explanation: ''
        };
        errors.value = {};
    }
}, { deep: true });

const formatDateForInput = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const closeModal = () => {
    form.value = {
        maturity_date: '',
        explanation: ''
    };
    errors.value = {};
    emit('close');
};

const handleSubmit = () => {
    errors.value = {};

    // Validation
    if (!form.value.maturity_date.trim()) {
        errors.value.maturity_date = 'Maturity date is required';
    }

    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Explanation is required';
    }

    // Check if new date is in the future
    if (form.value.maturity_date) {
        const selectedDate = new Date(form.value.maturity_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate <= today) {
            errors.value.maturity_date = 'New maturity date must be in the future';
        }
    }

    // Check if new date is after current maturity date
    if (form.value.maturity_date && props.governmentSecurity?.maturity_date) {
        const selectedDate = new Date(form.value.maturity_date);
        const currentDate = new Date(props.governmentSecurity.maturity_date);
        if (selectedDate <= currentDate) {
            errors.value.maturity_date = 'New maturity date must be after current maturity date';
        }
    }

    if (Object.keys(errors.value).length > 0) return;

    isSubmitting.value = true;

    router.post(
        `/treasury/government-securities/${props.governmentSecurity.id}/renew`,
        {
            new_maturity_date: form.value.maturity_date,
            explanation: form.value.explanation
        },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Government Security has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
                closeModal();
            },
            onError: (errors) => {
                if (errors.message) {
                    Swal.fire({
                        title: 'Error!',
                        text: errors.message,
                        icon: 'error',
                        confirmButtonColor: '#F59E0B'
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to renew government security. Please try again.',
                        icon: 'error',
                        confirmButtonColor: '#F59E0B'
                    });
                }
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        }
    );
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
                    <h2 class="text-xl font-bold text-gray-900">Renew Government Security</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Security Info -->
                    <div v-if="governmentSecurity" class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Security:</span> {{ governmentSecurity.government_security_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Reference:</span> {{ governmentSecurity.reference_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Current Maturity:</span> 
                            {{ new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(governmentSecurity.maturity_date)) }}
                        </p>
                    </div>

                    <!-- New Maturity Date Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            New Maturity Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.maturity_date"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.maturity_date }"
                        />
                        <p v-if="errors.maturity_date" class="text-red-500 text-sm mt-1">{{ errors.maturity_date }}</p>
                    </div>

                    <!-- Explanation Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Explanation / Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.explanation"
                            placeholder="Enter reason or notes for renewal..."
                            rows="4"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400 resize-none"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.explanation }"
                        />
                        <p v-if="errors.explanation" class="text-red-500 text-sm mt-1">{{ errors.explanation }}</p>
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
                            class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Renewing...' : 'Renew' }}
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
