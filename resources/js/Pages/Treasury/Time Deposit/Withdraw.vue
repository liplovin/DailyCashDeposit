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
    timeDeposit: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close']);

const form = ref({
    explanation: ''
});

const errors = ref({});
const isSubmitting = ref(false);

// Watch for time deposit changes and reset form
watch(() => props.timeDeposit, (newTimeDeposit) => {
    if (newTimeDeposit && props.isOpen) {
        form.value = {
            explanation: ''
        };
        errors.value = {};
    }
}, { deep: true });

const closeModal = () => {
    form.value = {
        explanation: ''
    };
    errors.value = {};
    emit('close');
};

const handleSubmit = () => {
    errors.value = {};

    // Validation
    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Explanation is required';
    }

    if (Object.keys(errors.value).length > 0) {
        return;
    }

    isSubmitting.value = true;

    router.post(
        `/treasury/time-deposit/${props.timeDeposit.id}/withdraw`,
        {
            explanation: form.value.explanation
        },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Withdrawn!',
                    text: 'Time deposit has been withdrawn successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
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
                        text: 'Failed to withdraw time deposit. Please try again.',
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
                    <h2 class="text-xl font-bold text-gray-900">Withdraw Time Deposit</h2>
                    <button
                        @click="closeModal"
                        class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <!-- Modal Content -->
                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <!-- Time Deposit Info -->
                    <div v-if="timeDeposit" class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Time Deposit:</span> {{ timeDeposit.time_deposit_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Account:</span> {{ timeDeposit.account_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Amount:</span> 
                            ₱ {{ parseFloat(timeDeposit.beginning_balance || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                        </p>
                        <p class="text-orange-600 text-sm font-semibold mt-2">
                            ⚠️ This action will withdraw the full time deposit amount.
                        </p>
                    </div>

                    <!-- Explanation Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">
                            Explanation / Notes <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.explanation"
                            placeholder="Enter reason or notes for withdrawal..."
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
                            class="flex-1 px-4 py-2 bg-orange-500 text-white font-semibold rounded-lg hover:bg-orange-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed"
                        >
                            {{ isSubmitting ? 'Withdrawing...' : 'Withdraw' }}
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
