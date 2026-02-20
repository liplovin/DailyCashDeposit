<script setup>
import { X, Upload } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
    isOpen: {
        type: Boolean,
        default: false
    },
    collection: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'updated']);

const form = ref({
    collection_amount: '',
    deposit_slip: null,
    check: null
});
const editFileNames = ref('');
const checkFileNames = ref('');
const isEditing = ref(false);

const handleAmountKeyDown = (event) => {
    // Allow: Backspace, Delete, Tab, Escape, Enter, Decimal
    const allowedKeys = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', '.', ','];
    
    // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
    if ((event.ctrlKey || event.metaKey) && ['a', 'c', 'v', 'x'].includes(event.key.toLowerCase())) {
        return;
    }
    
    // Only allow numbers and decimal point
    if (!/^\d|\./.test(event.key) && !allowedKeys.includes(event.key)) {
        event.preventDefault();
    }
};

const handleAmountInput = (event) => {
    let value = event.target.value.replace(/[^\d.]/g, '');
    
    // Prevent multiple decimal points
    const parts = value.split('.');
    if (parts.length > 2) {
        value = parts[0] + '.' + parts.slice(1).join('');
    }
    
    if (value === '' || value === '.') {
        form.value.collection_amount = '';
        return;
    }
    
    // Limit decimal places to 2
    const decimalParts = value.split('.');
    if (decimalParts.length > 1 && decimalParts[1].length > 2) {
        value = decimalParts[0] + '.' + decimalParts[1].substring(0, 2);
    }
    
    // Format number with commas
    const [integerStr, decimalStr] = value.split('.');
    const formattedInteger = integerStr.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    form.value.collection_amount = decimalStr !== undefined 
        ? formattedInteger + '.' + decimalStr 
        : formattedInteger;
};

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            Swal.fire({
                title: 'Invalid File Type',
                text: 'Only images (JPG, PNG, GIF) and PDF files are allowed',
                icon: 'warning',
                confirmButtonColor: '#3B82F6'
            });
            event.target.value = '';
            form.value.deposit_slip = null;
            editFileNames.value = '';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                title: 'File Too Large',
                text: 'File size must be less than 5MB',
                icon: 'warning',
                confirmButtonColor: '#3B82F6'
            });
            event.target.value = '';
            form.value.deposit_slip = null;
            editFileNames.value = '';
            return;
        }
        
        form.value.deposit_slip = file;
        editFileNames.value = file.name;
    }
};

const removeFile = () => {
    form.value.deposit_slip = null;
    editFileNames.value = '';
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) fileInput.value = '';
};

const handleCheckUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf'];
        if (!validTypes.includes(file.type)) {
            Swal.fire({
                title: 'Invalid File Type',
                text: 'Only images (JPG, PNG, GIF) and PDF files are allowed',
                icon: 'warning',
                confirmButtonColor: '#3B82F6'
            });
            event.target.value = '';
            form.value.check = null;
            checkFileNames.value = '';
            return;
        }
        
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                title: 'File Too Large',
                text: 'File size must be less than 5MB',
                icon: 'warning',
                confirmButtonColor: '#3B82F6'
            });
            event.target.value = '';
            form.value.check = null;
            checkFileNames.value = '';
            return;
        }
        
        form.value.check = file;
        checkFileNames.value = file.name;
    }
};

const removeCheckFile = () => {
    form.value.check = null;
    checkFileNames.value = '';
    const checkInput = document.querySelectorAll('input[type="file"]')[1];
    if (checkInput) checkInput.value = '';
};

const saveEdit = async () => {
    const cleanAmount = form.value.collection_amount.toString().replace(/,/g, '');
    
    if (!cleanAmount || cleanAmount === '') {
        Swal.fire({
            title: 'Validation Error',
            text: 'Collection amount is required',
            icon: 'warning',
            confirmButtonColor: '#3B82F6'
        });
        return;
    }
    
    if (isNaN(cleanAmount) || parseFloat(cleanAmount) <= 0) {
        Swal.fire({
            title: 'Validation Error',
            text: 'Collection amount must be a valid positive number',
            icon: 'warning',
            confirmButtonColor: '#3B82F6'
        });
        return;
    }
    
    isEditing.value = true;
    
    const formData = new FormData();
    formData.append('collection_amount', cleanAmount);
    if (form.value.deposit_slip) {
        formData.append('deposit_slip', form.value.deposit_slip);
    }
    if (form.value.check) {
        formData.append('check', form.value.check);
    }
    
    try {
        const response = await fetch(`/treasury3/collection/${props.collection.id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: formData,
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire({
                title: 'Success!',
                text: 'Collection has been updated successfully.',
                icon: 'success',
                confirmButtonColor: '#3B82F6',
                timer: 2000,
                timerProgressBar: true
            }).then(() => {
                emit('updated');
                closeModal();
                window.location.reload();
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to update collection.',
                icon: 'error',
                confirmButtonColor: '#3B82F6'
            });
        }
    } catch (error) {
        Swal.fire({
            title: 'Error!',
            text: error.message || 'Failed to update collection.',
            icon: 'error',
            confirmButtonColor: '#3B82F6'
        });
    } finally {
        isEditing.value = false;
    }
};

const closeModal = () => {
    emit('close');
    form.value = {
        collection_amount: '',
        deposit_slip: null,
        check: null
    };
    editFileNames.value = '';
    checkFileNames.value = '';
};

const initializeForm = () => {
    if (props.collection) {
        form.value.collection_amount = props.collection.collection_amount.toString();
        form.value.deposit_slip = null;
        editFileNames.value = '';
    }
};

watch(() => props.isOpen, (newValue) => {
    if (newValue) {
        initializeForm();
    }
});
</script>

<template>
    <div v-if="isOpen && collection" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            @click="closeModal"
        ></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col transform transition-all duration-300">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-8 py-6 flex items-center justify-between flex-shrink-0">
                <div class="flex-1">
                    <h2 class="text-3xl font-bold text-white">Edit Collection</h2>
                    <p class="text-blue-100 text-sm mt-2">Update amount and deposit slip</p>
                </div>
                <button
                    @click="closeModal"
                    class="text-white hover:bg-blue-700 p-2 rounded-lg transition-all duration-200 hover:scale-110"
                >
                    <X class="h-7 w-7" />
                </button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto px-8 py-8 min-h-0">
                <div class="space-y-7">
                    <!-- Current Amount Display -->
                    <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                        <p class="text-xs font-semibold text-blue-700 uppercase mb-1">Current Amount</p>
                        <p class="text-2xl font-bold text-blue-900">₱{{ parseFloat(collection.collection_amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
                    </div>

                    <!-- Amount Input Section -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">New Collection Amount</label>
                        <div class="relative group">
                            <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-600 font-bold text-lg">₱</span>
                            <input
                                :value="form.collection_amount"
                                @keydown="handleAmountKeyDown"
                                @input="handleAmountInput"
                                type="text"
                                inputmode="decimal"
                                placeholder="0.00"
                                class="w-full pl-10 pr-4 py-3.5 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 font-mono text-lg group-hover:border-blue-400"
                            />
                        </div>
                        <p class="text-xs text-gray-600 mt-2">✓ Numbers only • Automatic comma formatting • Max 2 decimals</p>
                    </div>

                    <!-- File Upload Section -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Deposit Slip</label>
                        
                        <!-- Current File Info -->
                        <div v-if="collection.deposit_slip && !editFileNames" class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                            <p class="text-xs font-semibold text-green-700 uppercase mb-2">Current File</p>
                            <p class="text-sm text-green-900 font-medium mb-2">✓ Deposit slip file uploaded</p>
                            <p class="text-xs text-green-600">Upload a new file to replace it</p>
                        </div>

                        <!-- New File Upload -->
                        <label class="border-2 border-dashed border-gray-300 rounded-xl p-8 hover:border-blue-500 hover:bg-blue-50 transition-all duration-200 cursor-pointer group block">
                            <input
                                type="file"
                                @change="handleFileUpload"
                                accept="image/jpeg,image/jpg,image/png,image/gif,.pdf"
                                class="hidden"
                            />
                            <div class="flex flex-col items-center justify-center text-center pointer-events-none">
                                <div class="mb-3 p-3 bg-blue-100 rounded-full group-hover:bg-blue-200 transition-colors">
                                    <Upload class="h-6 w-6 text-blue-600" />
                                </div>
                                <p class="text-sm font-semibold text-gray-700 group-hover:text-blue-600">Click to upload new file</p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or PDF (max 5MB)</p>
                            </div>
                        </label>

                        <!-- Selected File Display -->
                        <div v-if="editFileNames" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-blue-100 rounded">
                                    <Upload class="h-4 w-4 text-blue-600" />
                                </div>
                                <p class="text-sm font-medium text-blue-900">{{ editFileNames }}</p>
                            </div>
                            <button
                                @click="removeFile"
                                class="text-gray-400 hover:text-red-600 transition-colors"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Check File Upload Section (Optional) -->
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-3">Check <span class="text-gray-500 text-xs">(Optional)</span></label>
                        
                        <!-- Current File Info -->
                        <div v-if="collection.check && !checkFileNames" class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                            <p class="text-xs font-semibold text-green-700 uppercase mb-2">Current File</p>
                            <p class="text-sm text-green-900 font-medium mb-2">✓ Check file uploaded</p>
                            <p class="text-xs text-green-600">Upload a new file to replace it</p>
                        </div>

                        <!-- New File Upload -->
                        <label class="border-2 border-dashed border-purple-300 rounded-xl p-8 hover:border-purple-500 hover:bg-purple-50 transition-all duration-200 cursor-pointer group block">
                            <input
                                type="file"
                                @change="handleCheckUpload"
                                accept="image/jpeg,image/jpg,image/png,image/gif,.pdf"
                                class="hidden"
                            />
                            <div class="flex flex-col items-center justify-center text-center pointer-events-none">
                                <div class="mb-3 p-3 bg-purple-100 rounded-full group-hover:bg-purple-200 transition-colors">
                                    <Upload class="h-6 w-6 text-purple-600" />
                                </div>
                                <p class="text-sm font-semibold text-gray-700 group-hover:text-purple-600">Click to upload new file</p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF or PDF (max 5MB)</p>
                            </div>
                        </label>

                        <!-- Selected File Display -->
                        <div v-if="checkFileNames" class="mt-4 p-3 bg-purple-50 rounded-lg border border-purple-200 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="p-2 bg-purple-100 rounded">
                                    <Upload class="h-4 w-4 text-purple-600" />
                                </div>
                                <p class="text-sm font-medium text-purple-900">{{ checkFileNames }}</p>
                            </div>
                            <button
                                @click="removeCheckFile"
                                class="text-gray-400 hover:text-red-600 transition-colors"
                            >
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-8 py-5 border-t border-gray-200 flex justify-end gap-3 flex-shrink-0">
                <button
                    @click="closeModal"
                    class="px-6 py-2.5 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 font-bold transition-all duration-200 shadow-sm hover:shadow-md"
                >
                    Cancel
                </button>
                <button
                    @click="saveEdit"
                    :disabled="isEditing"
                    class="px-8 py-2.5 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 text-white hover:shadow-lg hover:scale-105 font-bold transition-all duration-200 shadow-md disabled:opacity-50 disabled:cursor-not-allowed disabled:scale-100 disabled:hover:shadow-md"
                >
                    {{ isEditing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </div>
    </div>
</template>

