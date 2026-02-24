<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { X } from 'lucide-vue-next';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: Boolean,
    operatingAccount: Object,
});

const emit = defineEmits(['close']);

const form = ref({
    new_maturity_date: '',
    explanation: '',
});

const errors = ref({});
const isSubmitting = ref(false);

watch(() => props.operatingAccount, (newOperatingAccount) => {
    if (newOperatingAccount && props.isOpen) {
        form.value = {
            new_maturity_date: formatDateForInput(newOperatingAccount.maturity_date),
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
    form.value.new_maturity_date = '';
    form.value.explanation = '';
    errors.value = {};
    emit('close');
};

const validateForm = () => {
    errors.value = {};
    const newErrors = {};

    if (!form.value.new_maturity_date) {
        newErrors.new_maturity_date = 'New maturity date is required';
    } else {
        const selectedDate = new Date(form.value.new_maturity_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);

        if (selectedDate <= today) {
            newErrors.new_maturity_date = 'Date must be in the future';
        }

        if (props.operatingAccount?.maturity_date) {
            const currentMaturity = new Date(props.operatingAccount.maturity_date);
            currentMaturity.setHours(0, 0, 0, 0);

            if (selectedDate <= currentMaturity) {
                newErrors.new_maturity_date = 'New maturity date must be after current maturity date';
            }
        }
    }

    if (!form.value.explanation || !form.value.explanation.trim()) {
        newErrors.explanation = 'Explanation is required';
    }

    errors.value = newErrors;
    return Object.keys(newErrors).length === 0;
};

const handleSubmit = async () => {
    if (!validateForm()) {
        return;
    }

    isSubmitting.value = true;

    router.post(`/treasury/operating-accounts/${props.operatingAccount.id}/renew`, form.value, {
        onSuccess: () => {
            Swal.fire({
                title: 'Renewed!',
                text: 'Operating Account has been renewed successfully.',
                icon: 'success',
                confirmButtonColor: '#F59E0B',
                timer: 2000,
                timerProgressBar: true,
            }).then(() => {
                window.location.reload();
            });
            closeModal();
        },
        onError: () => {
            Swal.fire({
                title: 'Error!',
                text: 'Failed to renew operating account. Please try again.',
                icon: 'error',
                confirmButtonColor: '#F59E0B',
            });
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
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
                    <h2 class="text-xl font-bold text-gray-900">Renew Operating Account</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Operating Account Info -->
                    <div v-if="operatingAccount" class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Account:</span> {{ operatingAccount.operating_account_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Account Number:</span> {{ operatingAccount.account_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Current Maturity:</span> 
                            {{ operatingAccount.maturity_date ? new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'short', day: 'numeric' }).format(new Date(operatingAccount.maturity_date)) : 'N/A' }}
                        </p>
                    </div>

                    <!-- New Maturity Date Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            New Maturity Date <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.new_maturity_date"
                            type="date"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 text-gray-900"
                            :class="{ 'border-red-500 focus:ring-red-500': errors.new_maturity_date }"
                        />
                        <p v-if="errors.new_maturity_date" class="text-red-500 text-sm mt-1">{{ errors.new_maturity_date }}</p>
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

                    <!-- Form Footer -->
                    <div class="flex gap-2 pt-2">
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
                            class="flex-1 px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg hover:bg-yellow-600 disabled:bg-gray-400 transition-all duration-200"
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
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
