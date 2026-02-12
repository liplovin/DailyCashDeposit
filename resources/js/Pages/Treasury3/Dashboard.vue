<script setup>
import Treasury3Layout from '@/Layouts/Treasury3Layout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
import { Clock, ChevronLeft, ChevronRight } from 'lucide-vue-next';

const page = usePage();
const user = computed(() => page.props.auth.user);

const currentDate = ref(new Date());
const currentTime = ref('');

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
    
    // Previous month's days
    const prevMonthDays = new Date(currentYear.value, currentMonth.value, 0).getDate();
    for (let i = firstDayOfMonth.value - 1; i >= 0; i--) {
        days.push({ day: prevMonthDays - i, isCurrentMonth: false });
    }
    
    // Current month's days
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push({ day: i, isCurrentMonth: true });
    }
    
    // Next month's days
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

onMounted(() => {
    formatTime();
    setInterval(formatTime, 1000);
});
</script>

<template>
    <Head title="Treasury 3 Dashboard" />
    <Treasury3Layout>
        <div class="w-full h-full">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 rounded-xl shadow-xl p-8 mb-8">
                <h1 class="text-4xl font-black text-white mb-2">
                    Welcome, {{ user.name }}! 👋
                </h1>
                <p class="text-yellow-50 text-lg">
                    Treasury 3 Management System - Manage your collection deposits efficiently
                </p>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Current Time Card -->
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-8 py-5">
                            <p class="text-white text-sm font-semibold">Current Time</p>
                            <p class="text-yellow-50 text-xs font-medium">Real-time clock</p>
                        </div>
                        <div class="p-12 bg-gradient-to-br from-gray-50 to-white">
                            <div class="relative flex items-center justify-center">
                                <Clock class="absolute left-0 h-20 w-20 text-yellow-500 animate-pulse" />
                                <p class="text-7xl font-bold text-gray-900 font-mono tracking-wider">
                                    {{ currentTime }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info Cards -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Collections Card -->
                        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
                            <h3 class="text-gray-600 text-sm font-semibold mb-2">Collections</h3>
                            <p class="text-3xl font-bold text-gray-900 mb-2">Pending</p>
                            <p class="text-gray-600 text-sm">Manage collection deposits</p>
                        </div>

                        <!-- Processed Card -->
                        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
                            <h3 class="text-gray-600 text-sm font-semibold mb-2">Processed</h3>
                            <p class="text-3xl font-bold text-gray-900 mb-2">Records</p>
                            <p class="text-gray-600 text-sm">View all processed items</p>
                        </div>
                    </div>
                </div>

                <!-- Calendar Sidebar -->
                <div class="bg-white rounded-xl shadow-xl overflow-hidden h-fit sticky top-8">
                    <!-- Calendar Header -->
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 px-6 py-5">
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
                                        ? 'bg-yellow-500 text-white shadow-lg scale-110'
                                        : dateObj.isCurrentMonth
                                        ? 'text-gray-900 hover:bg-yellow-100'
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
        </div>
    </Treasury3Layout>
</template>
