<script setup>
import { X } from 'lucide-vue-next';
import { router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, nextTick } from 'vue';
import Swal from 'sweetalert2';
import { Upload, Plus } from 'lucide-vue-next';
import axios from 'axios';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    account: {
        type: Object,
        default: null
    },
    operatingAccounts: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close']);

const selectedAccountId = ref(null);

const selectedAccount = computed(() => {
    if (!selectedAccountId.value) return null;
    return props.operatingAccounts.find(acc => acc.id === parseInt(selectedAccountId.value));
});

const form = ref({
    collections: [
        {
            collection_amount: '',
            deposit_slip: null,
            check: null
        }
    ]
});

const errors = ref({});
const isSubmitting = ref(false);
const depositSlipFileNames = ref(['']);
const checkFileNames = ref(['']);

const handleAmountInput = (event, index) => {
    let value = event.target.value;
    
    // Remove all non-numeric characters except decimal point
    value = value.replace(/[^\d.]/g, '');
    
    // Prevent multiple decimal points - keep only the first one
    if (value.split('.').length > 2) {
        const decimalIndex = value.indexOf('.');
        value = value.substring(0, decimalIndex + 1) + value.substring(decimalIndex + 1).replace(/\./g, '');
    }
    
    if (value === '') {
        form.value.collections[index].collection_amount = '';
        return;
    }
    
    // Split into integer and decimal parts
    const parts = value.split('.');
    let integerPart = parts[0] || '';
    let decimalPart = parts[1];
    
    // Add commas to integer part
    if (integerPart) {
        integerPart = integerPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
    
    // Format the value - preserve decimal point if it exists
    let formattedValue;
    if (value.includes('.')) {
        formattedValue = `${integerPart}.${decimalPart !== undefined ? decimalPart : ''}`;
    } else {
        formattedValue = integerPart;
    }
    
    form.value.collections[index].collection_amount = formattedValue;
};

const handleFileUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].deposit_slip = 'Only images (JPG, PNG, GIF) and PDF files are allowed';
            form.value.collections[index].deposit_slip = null;
            depositSlipFileNames.value[index] = '';
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].deposit_slip = 'File size must be less than 5MB';
            form.value.collections[index].deposit_slip = null;
            depositSlipFileNames.value[index] = '';
            return;
        }
        
        form.value.collections[index].deposit_slip = file;
        depositSlipFileNames.value[index] = file.name;
        if (!errors.value[index]) errors.value[index] = {};
        errors.value[index].deposit_slip = '';
    }
};

const removeFile = (index) => {
    form.value.collections[index].deposit_slip = null;
    depositSlipFileNames.value[index] = '';
    if (!errors.value[index]) errors.value[index] = {};
    errors.value[index].deposit_slip = '';
};

const handleCheckUpload = (event, index) => {
    const file = event.target.files[0];
    if (file) {
        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].check = 'Only images (JPG, PNG, GIF) and PDF files are allowed';
            form.value.collections[index].check = null;
            checkFileNames.value[index] = '';
            return;
        }
        
        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            if (!errors.value[index]) errors.value[index] = {};
            errors.value[index].check = 'File size must be less than 5MB';
            form.value.collections[index].check = null;
            checkFileNames.value[index] = '';
            return;
        }
        
        form.value.collections[index].check = file;
        checkFileNames.value[index] = file.name;
        if (!errors.value[index]) errors.value[index] = {};
        errors.value[index].check = '';
    }
};

const removeCheckFile = (index) => {
    form.value.collections[index].check = null;
    checkFileNames.value[index] = '';
    if (!errors.value[index]) errors.value[index] = {};
    errors.value[index].check = '';
};

const addCollection = () => {
    form.value.collections.push({
        collection_amount: '',
        deposit_slip: null,
        check: null
    });
    depositSlipFileNames.value.push('');
    checkFileNames.value.push('');
};

const removeCollection = (index) => {
    form.value.collections.splice(index, 1);
    depositSlipFileNames.value.splice(index, 1);
    checkFileNames.value.splice(index, 1);
};

const getTotalAmount = () => {
    const total = form.value.collections.reduce((total, collection) => {
        const cleanAmount = collection.collection_amount.toString().replace(/,/g, '');
        const amount = parseFloat(cleanAmount) || 0;
        return total + amount;
    }, 0);
    
    return total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const handleSubmit = () => {
    errors.value = {};
    let hasErrors = false;
    
    // Validation for all collections
    form.value.collections.forEach((collection, index) => {
        if (!errors.value[index]) {
            errors.value[index] = {};
        }
        
        const cleanAmount = collection.collection_amount.toString().replace(/,/g, '');
        
        if (!cleanAmount || cleanAmount === '') {
            errors.value[index].collection_amount = 'Collection amount is required';
            hasErrors = true;
        } else if (isNaN(cleanAmount) || parseFloat(cleanAmount) <= 0) {
            errors.value[index].collection_amount = 'Collection amount must be a valid positive number';
            hasErrors = true;
        }
        
        if (!collection.deposit_slip) {
            errors.value[index].deposit_slip = 'Deposit slip is required';
            hasErrors = true;
        }
    });
    
    if (hasErrors) {
        // Build detailed error message
        let errorMessages = [];
        Object.entries(errors.value).forEach(([index, indexErrors]) => {
            if (Object.keys(indexErrors).length > 0) {
                let collectionErrors = [];
                Object.entries(indexErrors).forEach(([field, message]) => {
                    if (message) {
                        collectionErrors.push(`• ${message}`);
                    }
                });
                if (collectionErrors.length > 0) {
                    errorMessages.push(`\n🔴 Collection ${parseInt(index) + 1}:\n${collectionErrors.join('\n')}`);
                }
            }
        });
        
        Swal.fire({
            title: 'Validation Error!',
            html: `<div style="text-align: left; font-size: 14px;">Please fix the following errors:${errorMessages.join('')}</div>`,
            icon: 'warning',
            confirmButtonColor: '#F59E0B'
        });
        return;
    }
    
    isSubmitting.value = true;
    
    // Create FormData to handle multiple file uploads
    const formData = new FormData();
    formData.append('operating_account_id', selectedAccount.value.id);
    
    form.value.collections.forEach((collection, index) => {
        const cleanAmount = collection.collection_amount.toString().replace(/,/g, '');
        formData.append(`collections[${index}][collection_amount]`, cleanAmount);
        formData.append(`collections[${index}][deposit_slip]`, collection.deposit_slip);
        formData.append(`collections[${index}][check]`, collection.check);
    });
    
    const page = usePage();
    const csrfToken = page.props.csrf_token || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    if (!csrfToken) {
        Swal.fire({
            title: 'Error!',
            text: 'CSRF token not found. Please refresh the page and try again.',
            icon: 'error',
            confirmButtonColor: '#F59E0B'
        });
        isSubmitting.value = false;
        return;
    }
    
    axios.post(`/treasury/operating-accounts/${selectedAccount.value.id}/collection`, formData, {
        headers: {
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (response.data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Collections saved successfully.',
                icon: 'success',
                confirmButtonColor: '#F59E0B',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                closeModal();
                router.visit('/treasury/operating-accounts');
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: response.data.message || 'Failed to save collections.',
                icon: 'error',
                confirmButtonColor: '#F59E0B'
            });
        }
        isSubmitting.value = false;
    })
    .catch(error => {
        console.error('Collection submission error:', error);
        Swal.fire({
            title: 'Error!',
            text: error.response?.data?.message || error.message || 'Failed to save collections. Please try again.',
            icon: 'error',
            confirmButtonColor: '#F59E0B'
        });
        isSubmitting.value = false;
    });
};

const closeModal = () => {
    selectedAccountId.value = null;
    form.value = {
        collections: [
            {
                collection_amount: '',
                deposit_slip: null,
                check: null
            }
        ]
    };
    depositSlipFileNames.value = [''];
    checkFileNames.value = [''];
    errors.value = {};
    emit('close');
};

// Scroll to top when account is selected
watch(() => selectedAccountId.value, (newValue) => {
    if (newValue) {
        nextTick(() => {
            const formContainer = document.querySelector('.form-collections-container');
            if (formContainer) {
                formContainer.scrollTop = 0;
            }
        });
    }
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/50 transition-opacity duration-300"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full mx-3 md:mx-4 transform transition-all duration-300 max-h-[calc(100vh-2rem)] md:max-h-[90vh] flex flex-col max-w-lg md:max-w-3xl lg:max-w-5xl overflow-hidden">
            <!-- Header with Account Selector -->
            <div class="flex flex-col gap-4 px-6 py-5 border-b border-gray-200 bg-white flex-shrink-0">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-900">Add Collections</h2>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 p-1.5 hover:bg-gray-100 rounded-lg transition-all duration-200"
                    >
                        <X class="h-6 w-6" />
                    </button>
                </div>
                
                <!-- Account Selector Dropdown -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Operating Account</label>
                    <select
                        v-model="selectedAccountId"
                        class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition-all bg-white text-gray-900 font-medium"
                    >
                        <option value="">-- Choose Account --</option>
                        <option v-for="account in operatingAccounts" :key="account.id" :value="account.id">
                            {{ account.operating_account_name }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Body -->
            <div v-if="!selectedAccount" class="flex-1 flex items-center justify-center px-6 py-12">
                <div class="text-center">
                    <div class="text-6xl mb-4">📋</div>
                    <p class="text-lg text-gray-600">Please select an operating account to get started</p>
                </div>
            </div>

            <!-- Body -->
            <div v-else class="flex flex-col lg:flex-row gap-0 flex-1 min-h-0 overflow-hidden">
                <!-- Left Column - Form -->
                <div class="form-collections-container w-full lg:w-5/12 space-y-4 px-5 py-5 overflow-y-auto border-b lg:border-b-0 lg:border-r border-gray-200 bg-gray-50 flex-1 min-h-0">
                    <!-- Account Name Display -->
                    <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200 flex-shrink-0">
                        <p class="text-xs text-gray-600 font-semibold mb-0.5">Operating Account</p>
                        <p class="text-sm font-bold text-gray-900">{{ selectedAccount?.operating_account_name }}</p>
                    </div>

                    <!-- Collections List -->
                    <div class="space-y-4 min-w-0">
                        <div v-for="(collection, index) in form.collections" :key="index" class="p-4 bg-white rounded-lg border border-gray-200 hover:border-yellow-400 transition-colors shadow-sm">
                            <!-- Collection Header -->
                            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-200">
                                <h4 class="text-sm font-bold text-gray-900">Collection {{ index + 1 }}</h4>
                                <button
                                    v-if="form.collections.length > 1"
                                    @click="removeCollection(index)"
                                    class="px-2.5 py-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200 text-xs font-semibold transition-colors"
                                >
                                    Remove
                                </button>
                            </div>

                            <!-- Amount and Slip Stacked -->
                            <div class="space-y-3">
                                <!-- Collection Amount -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Amount (₱) <span class="text-red-600">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 text-sm font-medium">₱</span>
                                        <input
                                            v-model="collection.collection_amount"
                                            @input="handleAmountInput($event, index)"
                                            type="text"
                                            inputmode="decimal"
                                            :placeholder="'0.00'"
                                            class="w-full pl-7 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent outline-none transition-all bg-white"
                                        />
                                    </div>
                                    <p v-if="errors[index]?.collection_amount" class="mt-1 text-xs text-red-600">
                                        {{ errors[index].collection_amount }}
                                    </p>
                                </div>

                                <!-- Deposit Slip Upload -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Deposit Slip <span class="text-red-600">*</span>
                                    </label>
                                    
                                    <div v-if="!depositSlipFileNames[index]" class="relative">
                                        <input
                                            @change="handleFileUpload($event, index)"
                                            type="file"
                                            accept="image/*,.pdf"
                                            class="hidden"
                                            :id="`deposit_slip_${index}`"
                                        />
                                        <label
                                            :for="`deposit_slip_${index}`"
                                            class="flex items-center justify-center gap-2 px-3 py-3 border-2 border-dashed border-yellow-300 rounded-lg bg-yellow-50 hover:bg-yellow-100 cursor-pointer transition-colors"
                                        >
                                            <Upload class="h-4 w-4 text-yellow-600" />
                                            <span class="text-sm font-medium text-yellow-900">Click to upload</span>
                                        </label>
                                    </div>

                                    <div v-else class="flex items-center gap-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-200 flex items-center justify-center">
                                            <span class="text-xs font-bold text-green-700">✓</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ depositSlipFileNames[index] }}</p>
                                        </div>
                                        <button
                                            @click="removeFile(index)"
                                            class="flex-shrink-0 text-gray-400 hover:text-gray-600"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <p v-if="errors[index]?.deposit_slip" class="mt-1 text-xs text-red-600">
                                        {{ errors[index].deposit_slip }}
                                    </p>
                                </div>

                                <!-- Check Upload (Optional) -->
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        Check <span class="text-gray-500 text-xs">(Optional)</span>
                                    </label>
                                    
                                    <div v-if="!checkFileNames[index]" class="relative">
                                        <input
                                            @change="handleCheckUpload($event, index)"
                                            type="file"
                                            accept="image/*,.pdf"
                                            class="hidden"
                                            :id="`check_${index}`"
                                        />
                                        <label
                                            :for="`check_${index}`"
                                            class="flex items-center justify-center gap-2 px-3 py-3 border-2 border-dashed border-blue-300 rounded-lg bg-blue-50 hover:bg-blue-100 cursor-pointer transition-colors"
                                        >
                                            <Upload class="h-4 w-4 text-blue-600" />
                                            <span class="text-sm font-medium text-blue-900">Click to upload</span>
                                        </label>
                                    </div>

                                    <div v-else class="flex items-center gap-2 p-3 bg-green-50 rounded-lg border border-green-200">
                                        <div class="flex-shrink-0 w-5 h-5 rounded-full bg-green-200 flex items-center justify-center">
                                            <span class="text-xs font-bold text-green-700">✓</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ checkFileNames[index] }}</p>
                                        </div>
                                        <button
                                            @click="removeCheckFile(index)"
                                            class="flex-shrink-0 text-gray-400 hover:text-gray-600"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <p v-if="errors[index]?.check" class="mt-1 text-xs text-red-600">
                                        {{ errors[index].check }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Another Collection Button -->
                    <button
                        @click="addCollection"
                        class="w-full px-4 py-2.5 rounded-lg border-2 border-dashed border-yellow-300 text-yellow-700 hover:bg-yellow-50 transition-colors font-semibold text-sm flex items-center justify-center gap-2"
                    >
                        <Plus class="h-4 w-4" />
                        Add Another Collection
                    </button>
                </div>

                <!-- Right Column - Receipt/Summary -->
                <div class="w-full lg:w-7/12 bg-gradient-to-b from-amber-50 to-yellow-50 px-5 py-5 flex flex-col border-t lg:border-t-0 border-gray-200 overflow-y-auto hidden lg:flex">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 uppercase tracking-wide">Summary</h3>
                    
                    <!-- Collections List -->
                    <div class="flex-1 space-y-3 mb-4 min-h-0 overflow-y-auto">
                        <div v-if="form.collections.length === 0" class="text-sm text-gray-500 text-center py-16 flex items-center justify-center h-full">
                            No collections added yet
                        </div>
                        <div v-for="(collection, index) in form.collections" :key="index" class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                            <div class="text-sm font-bold text-gray-900 mb-3 pb-2 border-b border-gray-100">Collection {{ index + 1 }}</div>
                            <div class="space-y-2.5 text-sm">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-700">Amount:</span>
                                    <span class="font-bold text-lg text-yellow-600">₱{{ collection.collection_amount || '0.00' }}</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-gray-700">Slip:</span>
                                    <span v-if="depositSlipFileNames[index]" class="text-green-600 font-semibold text-xs bg-green-100 px-2 py-0.5 rounded-full">✓ Attached</span>
                                    <span v-else class="text-red-600 font-semibold text-xs bg-red-100 px-2 py-0.5 rounded-full">Missing</span>
                                </div>
                                <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                                    <span class="text-gray-700">Check:</span>
                                    <span v-if="checkFileNames[index]" class="text-blue-600 font-semibold text-xs bg-blue-100 px-2 py-0.5 rounded-full">✓ Attached</span>
                                    <span v-else class="text-gray-400 font-semibold text-xs bg-gray-100 px-2 py-0.5 rounded-full">Optional</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t-2 border-yellow-300 my-3"></div>

                    <!-- Total -->
                    <div class="bg-gradient-to-br from-yellow-100 to-amber-100 rounded-lg p-4 border-2 border-yellow-400 shadow-md">
                        <p class="text-xs text-gray-700 mb-1 font-semibold uppercase tracking-wide">Total Amount</p>
                        <p class="text-4xl font-black text-yellow-700 mb-2">
                            ₱{{ getTotalAmount() }}
                        </p>
                        <p class="text-sm text-yellow-800 font-semibold">{{ form.collections.length }} collection(s)</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-4 px-6 py-4 border-t border-gray-200 bg-white flex-shrink-0">
                <button
                    @click="closeModal"
                    class="flex-1 px-4 py-3 rounded-lg border-2 border-gray-300 text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-400 transition-all duration-200"
                >
                    Cancel
                </button>
                <button
                    @click="handleSubmit"
                    :disabled="isSubmitting"
                    class="flex-1 px-4 py-3 rounded-lg bg-gradient-to-r from-yellow-500 to-amber-500 text-white font-semibold hover:from-yellow-600 hover:to-amber-600 disabled:from-gray-400 disabled:to-gray-400 disabled:cursor-not-allowed transition-all duration-200 shadow-lg"
                >
                    <span v-if="!isSubmitting" class="flex items-center justify-center gap-2">
                        <span>Add Collections</span>
                    </span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Adding...</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</template>
