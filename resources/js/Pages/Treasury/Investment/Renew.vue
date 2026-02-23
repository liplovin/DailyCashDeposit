<script setup>
import { X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: { type: Boolean, default: false },
    investment: { type: Object, default: null }
});

const emit = defineEmits(['close']);
const form = ref({ new_maturity_date: '', explanation: '' });
const errors = ref({});
const isSubmitting = ref(false);

watch(() => props.investment, (newInvestment) => {
    if (newInvestment && props.isOpen) {
        form.value = {
            new_maturity_date: formatDateForInput(newInvestment.maturity_date),
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
    form.value = { new_maturity_date: '', explanation: '' };
    errors.value = {};
    emit('close');
};

const handleSubmit = () => {
    errors.value = {};
    if (!form.value.new_maturity_date.trim()) {
        errors.value.new_maturity_date = 'New maturity date is required';
    }
    if (!form.value.explanation.trim()) {
        errors.value.explanation = 'Explanation is required';
    }
    if (form.value.new_maturity_date) {
        const newDate = new Date(form.value.new_maturity_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        newDate.setHours(0, 0, 0, 0);
        if (newDate <= today) {
            errors.value.new_maturity_date = 'New maturity date must be in the future';
        }
    }
    if (form.value.new_maturity_date && props.investment?.maturity_date && !errors.value.new_maturity_date) {
        const newDate = new Date(form.value.new_maturity_date);
        const currentDate = new Date(props.investment.maturity_date);
        if (newDate <= currentDate) {
            errors.value.new_maturity_date = 'New maturity date must be after the current maturity date';
        }
    }
    if (Object.keys(errors.value).length > 0) return;
    
    isSubmitting.value = true;
    router.post(`/treasury/investment/${props.investment.id}/renew`,
        { new_maturity_date: form.value.new_maturity_date, explanation: form.value.explanation },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Investment has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#F59E0B',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => { window.location.reload(); });
                closeModal();
            },
            onError: (errors) => {
                if (errors.message) {
                    Swal.fire({ title: 'Error!', text: errors.message, icon: 'error', confirmButtonColor: '#F59E0B' });
                } else {
                    Swal.fire({ title: 'Error!', text: 'Failed to renew investment. Please try again.', icon: 'error', confirmButtonColor: '#F59E0B' });
                }
            },
            onFinish: () => { isSubmitting.value = false; }
        }
    );
};
</script>

<template>
    <transition name="fade">
        <div v-if="isOpen" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900">Renew Investment</h2>
                    <button @click="closeModal" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="handleSubmit" class="px-6 py-4 space-y-4">
                    <div v-if="investment" class="bg-yellow-50 rounded-lg p-3 border border-yellow-200">
                        <p class="text-sm font-semibold text-gray-800">
                            <span class="text-gray-600">Investment:</span> {{ investment.investment_name }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Reference:</span> {{ investment.reference_number }}
                        </p>
                        <p class="text-sm font-semibold text-gray-800 mt-1">
                            <span class="text-gray-600">Current Maturity Date:</span> 
                            {{ new Date(investment.maturity_date).toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                        </p>
                        <p class="text-yellow-600 text-sm font-semibold mt-2">
                            ⚠️ Set a new maturity date for this investment.
                        </p>
                    </div>

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

                    <div class="flex space-x-3 pt-4 border-t border-gray-200">
                        <button @click="closeModal" type="button" class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200">
                            Cancel
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="flex-1 px-4 py-2 bg-green-500 text-white font-semibold rounded-lg hover:bg-green-600 transition-all duration-200 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            {{ isSubmitting ? 'Renewing...' : 'Renew' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
