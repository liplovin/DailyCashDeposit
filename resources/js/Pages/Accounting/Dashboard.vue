<script setup>
import AccountingLayout from '@/Layouts/AccountingLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Wallet, CheckCircle, Clock, ChevronLeft, ChevronRight, Calculator } from 'lucide-vue-next';

const currentDate = ref(new Date());
const currentTime = ref('');
const showCalculator = ref(false);
const calculatorDisplay = ref('0');
const calculatorFirstValue = ref(null);
const calculatorOperation = ref(null);
const calculatorShouldResetDisplay = ref(false);

const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'];
const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const today = computed(() => {
    const now = new Date();
    return now.getDate();
});

const currentMonth = computed(() => currentDate.value.getMonth());
const currentYear = computed(() => currentDate.value.getFullYear());

const monthYear = computed(() => {
    return `${monthNames[currentMonth.value]} ${currentYear.value}`;
});

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const calendarDays = computed(() => {
    const days = [];
    
    const prevMonthDays = new Date(currentYear.value, currentMonth.value, 0).getDate();
    for (let i = firstDayOfMonth.value - 1; i >= 0; i--) {
        days.push({ day: prevMonthDays - i, isCurrentMonth: false });
    }
    
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push({ day: i, isCurrentMonth: true });
    }
    
    const remainingDays = 42 - days.length;
    for (let i = 1; i <= remainingDays; i++) {
        days.push({ day: i, isCurrentMonth: false });
    }
    
    return days;
});

const isToday = (day) => {
    return day === today.value && currentMonth.value === new Date().getMonth() && 
           currentYear.value === new Date().getFullYear();
};

const formatTime = () => {
    const now = new Date();
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    currentTime.value = new Intl.DateTimeFormat('en-US', timeOptions).format(now);
};

const previousMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value - 1);
};

const nextMonth = () => {
    currentDate.value = new Date(currentYear.value, currentMonth.value + 1);
};

const goToToday = () => {
    currentDate.value = new Date();
};

const getGreeting = computed(() => {
    const hour = new Date().getHours();
    
    if (hour < 12) {
        return 'Good morning';
    } else if (hour < 18) {
        return 'Good afternoon';
    } else {
        return 'Good evening';
    }
});

const navigateTo = (route) => {
    router.get(route);
};

const handleCalculatorInput = (value) => {
    if (value === 'C') {
        calculatorDisplay.value = '0';
        calculatorFirstValue.value = null;
        calculatorOperation.value = null;
        calculatorShouldResetDisplay.value = false;
    } else if (value === '=') {
        if (calculatorFirstValue.value !== null && calculatorOperation.value) {
            const secondValue = parseFloat(calculatorDisplay.value);
            const result = calculate(calculatorFirstValue.value, secondValue, calculatorOperation.value);
            calculatorDisplay.value = result.toString();
            calculatorFirstValue.value = null;
            calculatorOperation.value = null;
            calculatorShouldResetDisplay.value = true;
        }
    } else if (['+', '-', '*', '/'].includes(value)) {
        calculatorFirstValue.value = parseFloat(calculatorDisplay.value);
        calculatorOperation.value = value;
        calculatorShouldResetDisplay.value = true;
    } else {
        if (calculatorShouldResetDisplay.value) {
            calculatorDisplay.value = value;
            calculatorShouldResetDisplay.value = false;
        } else {
            if (calculatorDisplay.value === '0') {
                calculatorDisplay.value = value;
            } else {
                calculatorDisplay.value += value;
            }
        }
    }
};

const calculate = (first, second, operation) => {
    switch (operation) {
        case '+':
            return first + second;
        case '-':
            return first - second;
        case '*':
            return first * second;
        case '/':
            return second !== 0 ? first / second : 0;
        default:
            return second;
    }
};

const handleKeyboardInput = (event) => {
    if (!showCalculator.value) return;

    if (/^\d$/.test(event.key)) {
        // Numbers 0-9
        event.preventDefault();
        handleCalculatorInput(event.key);
    } else if (/[+\-*/]/.test(event.key)) {
        // Operators
        event.preventDefault();
        handleCalculatorInput(event.key);
    } else if (event.key === 'Enter' || event.key === '=') {
        // Equals
        event.preventDefault();
        handleCalculatorInput('=');
    } else if (event.key === 'Backspace') {
        // Delete last character
        event.preventDefault();
        if (calculatorDisplay.value !== '0') {
            calculatorDisplay.value = calculatorDisplay.value.slice(0, -1) || '0';
        }
    } else if (event.key === 'c' || event.key === 'C') {
        // Clear
        event.preventDefault();
        handleCalculatorInput('C');
    }
};

const handlePaste = (event) => {
    if (!showCalculator.value) return;

    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');
    const numbersOnly = pastedText.replace(/[^\d.]/g, '');
    
    if (numbersOnly) {
        if (calculatorShouldResetDisplay.value) {
            calculatorDisplay.value = numbersOnly;
            calculatorShouldResetDisplay.value = false;
        } else {
            if (calculatorDisplay.value === '0') {
                calculatorDisplay.value = numbersOnly;
            } else {
                calculatorDisplay.value += numbersOnly;
            }
        }
    }
};

onMounted(() => {
    formatTime();
    setInterval(formatTime, 1000);
    window.addEventListener('keydown', handleKeyboardInput);
    window.addEventListener('paste', handlePaste);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeyboardInput);
    window.removeEventListener('paste', handlePaste);
});
</script>

<template>
    <Head title="Accounting Dashboard" />
    <AccountingLayout>
        <!-- Welcome Card -->
        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl shadow-sm border border-blue-200 p-8 mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">
                {{ getGreeting }}, Accounting User!
            </h1>
            <p class="text-gray-700">
                Manage operating account disbursements and track processed transactions. Monitor all financial activities in one place.
            </p>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Current Time Card -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-400 to-blue-600 px-8 py-5">
                        <p class="text-white text-sm font-semibold">Current Time</p>
                        <p class="text-blue-50 text-xs font-medium">Real-time clock</p>
                    </div>
                    <div class="p-12 bg-gradient-to-br from-gray-50 to-white">
                        <div class="relative flex items-center justify-center">
                            <Clock class="absolute left-0 h-20 w-20 text-blue-500 animate-pulse" />
                            <p class="text-7xl font-bold text-gray-900 font-mono tracking-wider">
                                {{ currentTime }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Navigation Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Operating Accounts Card -->
                    <div 
                        @click="navigateTo('/accounting/operating-accounts')"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-blue-300 transition-all duration-200 cursor-pointer group"
                    >
                        <div class="h-2 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Operating Accounts</h3>
                                    <p class="text-sm text-gray-500 mt-1">Pending Disbursements</p>
                                </div>
                                <Wallet class="h-8 w-8 text-yellow-600 group-hover:scale-110 transition-transform" />
                            </div>
                            <p class="text-gray-600 text-sm">Manage and process operating account disbursements. Create, edit, and approve pending transactions.</p>
                            <div class="mt-4 inline-block bg-yellow-50 text-yellow-700 rounded-lg px-3 py-1 text-xs font-semibold">
                                Click to manage →
                            </div>
                        </div>
                    </div>

                    <!-- Processed Disbursements Card -->
                    <div 
                        @click="navigateTo('/accounting/processed-disbursement')"
                        class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg hover:border-blue-300 transition-all duration-200 cursor-pointer group"
                    >
                        <div class="h-2 bg-gradient-to-r from-blue-400 to-blue-600"></div>
                        <div class="p-8">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Processed Disbursements</h3>
                                    <p class="text-sm text-gray-500 mt-1">Verified & Completed</p>
                                </div>
                                <CheckCircle class="h-8 w-8 text-blue-600 group-hover:scale-110 transition-transform" />
                            </div>
                            <p class="text-gray-600 text-sm">Review all processed and completed disbursements. Track account subtotals and verify transaction details.</p>
                            <div class="mt-4 inline-block bg-blue-50 text-blue-700 rounded-lg px-3 py-1 text-xs font-semibold">
                                Click to view →
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Box -->
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-6">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-amber-900">Daily Processing</h3>
                            <p class="mt-2 text-sm text-amber-700">Pending disbursements are automatically processed daily at 12:00 AM. Make sure all records are verified before processing.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Sidebar -->
            <div class="bg-white rounded-xl shadow-xl overflow-hidden h-fit sticky top-8">
                <!-- Calendar Header -->
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 px-6 py-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-bold text-white">{{ monthYear }}</h3>
                        <button
                            @click="goToToday"
                            class="text-xs px-3 py-1 bg-white/25 hover:bg-white/35 text-white rounded-lg font-semibold transition-all"
                        >
                            Today
                        </button>
                    </div>
                    
                    <!-- Month Navigation -->
                    <div class="flex items-center justify-between">
                        <button
                            @click="previousMonth"
                            class="p-2 hover:bg-white/20 rounded-lg transition-all"
                        >
                            <ChevronLeft class="h-5 w-5 text-white" />
                        </button>
                        <div class="flex-1"></div>
                        <button
                            @click="nextMonth"
                            class="p-2 hover:bg-white/20 rounded-lg transition-all"
                        >
                            <ChevronRight class="h-5 w-5 text-white" />
                        </button>
                    </div>
                </div>

                <!-- Calendar Body -->
                <div class="p-4">
                    <!-- Day names -->
                    <div class="grid grid-cols-7 gap-1 mb-3">
                        <div v-for="day in dayNames" :key="day" class="text-center">
                            <p class="text-xs font-bold text-gray-600 py-2">{{ day }}</p>
                        </div>
                    </div>

                    <!-- Calendar dates -->
                    <div class="grid grid-cols-7 gap-1">
                        <button
                            v-for="(dateObj, index) in calendarDays"
                            :key="index"
                            :class="[
                                'p-2 rounded-lg text-sm font-semibold transition-all duration-200 hover:shadow-md',
                                isToday(dateObj.day) && dateObj.isCurrentMonth
                                    ? 'bg-blue-500 text-white shadow-lg scale-110'
                                    : dateObj.isCurrentMonth
                                    ? 'text-gray-900 hover:bg-blue-100'
                                    : 'text-gray-300 bg-gray-50'
                            ]"
                        >
                            {{ dateObj.day }}
                        </button>
                    </div>

                    <!-- Calendar Footer -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-600 text-center">
                            Selected: <strong>{{ today }}/{{ currentMonth + 1 }}/{{ currentYear }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculator Modal -->
        <div v-if="showCalculator" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-2xl w-80">
                <!-- Calculator Header -->
                <div class="bg-gradient-to-r from-blue-400 to-blue-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white">Calculator</h3>
                    <button
                        @click="showCalculator = false"
                        class="text-white hover:bg-white/20 rounded-lg p-2 transition-all"
                    >
                        ✕
                    </button>
                </div>

                <!-- Calculator Body -->
                <div class="p-6 bg-gray-50">
                    <!-- Display -->
                    <div class="bg-white border-2 border-blue-300 rounded-lg p-4 mb-4">
                        <p class="text-right text-4xl font-bold text-gray-900 font-mono">{{ calculatorDisplay }}</p>
                    </div>

                    <!-- Keyboard Help -->
                    <div class="text-xs text-gray-600 mb-3 bg-blue-50 p-2 rounded border border-blue-200">
                        <p>💡 <strong>Keyboard:</strong> Numbers, +−*/, Enter=, C=clear, Backspace=delete</p>
                        <p>💱 <strong>Paste:</strong> Numbers only allowed</p>
                    </div>

                    <!-- Buttons -->
                    <div class="grid grid-cols-4 gap-2">
                        <!-- Clear -->
                        <button
                            @click="handleCalculatorInput('C')"
                            class="col-span-4 bg-red-500 hover:bg-red-600 text-white font-bold py-2 rounded-lg transition-all"
                        >
                            Clear
                        </button>

                        <!-- Number and Operation Buttons -->
                        <button
                            v-for="btn in ['7', '8', '9', '/']"
                            :key="btn"
                            @click="handleCalculatorInput(btn)"
                            :class="[
                                'py-3 font-bold rounded-lg transition-all',
                                ['+', '-', '*', '/'].includes(btn)
                                    ? 'bg-blue-500 hover:bg-blue-600 text-white'
                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-900'
                            ]"
                        >
                            {{ btn }}
                        </button>

                        <button
                            v-for="btn in ['4', '5', '6', '*']"
                            :key="btn"
                            @click="handleCalculatorInput(btn)"
                            :class="[
                                'py-3 font-bold rounded-lg transition-all',
                                ['+', '-', '*', '/'].includes(btn)
                                    ? 'bg-blue-500 hover:bg-blue-600 text-white'
                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-900'
                            ]"
                        >
                            {{ btn }}
                        </button>

                        <button
                            v-for="btn in ['1', '2', '3', '-']"
                            :key="btn"
                            @click="handleCalculatorInput(btn)"
                            :class="[
                                'py-3 font-bold rounded-lg transition-all',
                                ['+', '-', '*', '/'].includes(btn)
                                    ? 'bg-blue-500 hover:bg-blue-600 text-white'
                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-900'
                            ]"
                        >
                            {{ btn }}
                        </button>

                        <button
                            v-for="btn in ['0', '.', '+']"
                            :key="btn"
                            @click="handleCalculatorInput(btn)"
                            :class="[
                                'py-3 font-bold rounded-lg transition-all',
                                btn === '+' ? 'col-span-1' : 'col-span-2',
                                ['+', '-', '*', '/'].includes(btn)
                                    ? 'bg-blue-500 hover:bg-blue-600 text-white'
                                    : 'bg-gray-200 hover:bg-gray-300 text-gray-900'
                            ]"
                        >
                            {{ btn }}
                        </button>

                        <!-- Equals -->
                        <button
                            @click="handleCalculatorInput('=')"
                            class="col-span-1 bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-lg transition-all"
                        >
                            =
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calculator Toggle Button -->
        <button
            @click="showCalculator = true"
            class="fixed bottom-8 right-8 bg-blue-500 hover:bg-blue-600 text-white rounded-full p-4 shadow-lg hover:shadow-xl transition-all duration-200 z-40"
            title="Open Calculator"
        >
            <Calculator class="h-6 w-6" />
        </button>
    </AccountingLayout>
</template>

<style scoped>
/* Add any component-specific styles here */
</style>
