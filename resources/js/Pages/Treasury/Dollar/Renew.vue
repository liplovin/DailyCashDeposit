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
    dollar: {
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

// Watch for dollar changes and populate form
watch(() => props.dollar, (newDollar) => {
    if (newDollar && props.isOpen) {
        form.value = {
            maturity_date: formatDateForInput(newDollar.maturity_date),
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
    if (form.value.maturity_date && props.dollar?.maturity_date) {
        const selectedDate = new Date(form.value.maturity_date);
        const currentDate = new Date(props.dollar.maturity_date);
        if (selectedDate <= currentDate) {
            errors.value.maturity_date = 'New maturity date must be after current maturity date';
        }
    }

    if (Object.keys(errors.value).length > 0) return;

    isSubmitting.value = true;

    router.post(
        `/treasury/dollar/${props.dollar.id}/renew`,
        {
            new_maturity_date: form.value.maturity_date,
            explanation: form.value.explanation
        },
        {
            onSuccess: () => {
                Swal.fire({
                    title: 'Renewed!',
                    text: 'Dollar investment has been renewed successfully.',
                    icon: 'success',
                    confirmButtonColor: '#FBBF24',
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.reload();
                });
                closeModal();
            },
            onError: (errors) => {
                Swal.fire({
                    title: 'Error!',
                    text: errors.message || 'Failed to renew investment',
                    icon: 'error',
                    confirmButtonColor: '#FBBF24'
                });
            },
            onFinish: () => {
                isSubmitting.value = false;
            }
        }
    );
};
</script>

<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-md">
      <!-- Header -->
      <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 px-6 py-4 flex justify-between items-center">
        <h2 class="text-white text-lg font-semibold">Renew Dollar</h2>
        <button
          @click="closeModal"
          class="text-white hover:bg-yellow-800 rounded-full p-1 transition-colors"
        >
          <X :size="20" />
        </button>
      </div>

      <!-- Form -->
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <!-- Current Maturity Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Current Maturity Date</label>
            <input
              type="text"
              :value="formatDateForInput(dollar?.maturity_date)"
              disabled
              class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-600"
            />
          </div>

          <!-- New Maturity Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Maturity Date</label>
            <input
              type="date"
              v-model="form.maturity_date"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
            />
            <p v-if="errors.maturity_date" class="mt-1 text-sm text-red-600">{{ errors.maturity_date }}</p>
          </div>

          <!-- Explanation -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Explanation</label>
            <textarea
              v-model="form.explanation"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500"
              placeholder="Enter reason for renewal..."
            ></textarea>
            <p v-if="errors.explanation" class="mt-1 text-sm text-red-600">{{ errors.explanation }}</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50"
            >
              {{ isSubmitting ? 'Processing...' : 'Renew' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
