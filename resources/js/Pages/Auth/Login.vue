<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="h-screen w-screen bg-gradient-to-br from-gray-900 via-slate-800 to-gray-900 overflow-hidden flex">
        <Head title="Login - Daily Bank Deposit Management System" />

        <!-- Left Column - Branding & Info -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-yellow-500 via-yellow-600 to-amber-700 flex-col justify-between items-center p-12">
            <!-- Top Branding -->
            <div class="flex flex-col items-center justify-center flex-1">
                <div class="mb-8 drop-shadow-2xl">
                    <img src="/logoonly.png" alt="Bank Deposit Logo" class="w-40 h-40">
                </div>
                <h1 class="text-4xl font-bold text-white mb-4 text-center">Daily Bank Deposit</h1>
                <h2 class="text-2xl font-semibold text-yellow-100 mb-6 text-center">Management System</h2>
                
                <!-- Features -->
                <div class="space-y-4 w-full mt-8">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-yellow-400/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-yellow-100 font-medium">Secure Deposit Tracking</p>
                            <p class="text-yellow-200 text-sm">Real-time monitoring of all daily deposits</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-yellow-400/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-yellow-100 font-medium">Comprehensive Reports</p>
                            <p class="text-yellow-200 text-sm">Detailed analytics and transaction history</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-yellow-400/20">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-yellow-100 font-medium">Enterprise Security</p>
                            <p class="text-yellow-200 text-sm">Bank-grade encryption and protection</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Login Form -->
        <div class="w-full lg:w-1/2 bg-gradient-to-b from-slate-800 via-gray-800 to-gray-900 flex flex-col justify-center items-center p-6 sm:p-8">
            <div class="w-full max-w-sm">
                <!-- Mobile Logo (shown on small screens) -->
                <div class="lg:hidden text-center mb-10">
                    <div class="flex justify-center mb-6">
                        <img src="/logoonly.png" alt="Bank Deposit Logo" class="w-32 h-32">
                    </div>
                    <h1 class="text-2xl font-bold text-white">Daily Bank Deposit</h1>
                    <p class="text-yellow-300 mt-1 font-medium">Management System</p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/50 rounded-xl text-sm font-medium text-yellow-300 backdrop-blur-sm">
                    {{ status }}
                </div>

                <!-- Login Title -->
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-white">Welcome Back</h2>
                    <p class="text-gray-400 mt-2 text-sm">Enter your credentials to access the system</p>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-gray-300 font-semibold mb-2 block text-sm" />
                        <TextInput
                            id="email"
                            type="email"
                            class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400/50 focus:outline-none transition text-white placeholder-gray-500 backdrop-blur-sm"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="you@company.com"
                        />
                        <InputError class="mt-2 text-red-400 text-sm" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-gray-300 font-semibold mb-2 block text-sm" />
                        <TextInput
                            id="password"
                            type="password"
                            class="w-full px-4 py-3 bg-gray-700/50 border border-gray-600 rounded-lg focus:border-yellow-500 focus:ring-2 focus:ring-yellow-400/50 focus:outline-none transition text-white placeholder-gray-500 backdrop-blur-sm"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                        />
                        <InputError class="mt-2 text-red-400 text-sm" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center cursor-pointer">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            <span class="ms-2 text-sm text-gray-400 select-none">Remember me</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-yellow-400 hover:text-yellow-300 font-medium transition"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <!-- Login Button -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        class="w-full bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-gray-900 shadow-lg shadow-yellow-500/30"
                    >
                        <span v-if="!form.processing">Sign In</span>
                        <span v-else class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Signing in...
                        </span>
                    </button>
                </form>

                <!-- Footer -->
                <div class="mt-8 pt-6 border-t border-gray-700">
                    <p class="text-center text-xs text-gray-500">
                        For issues or support, please contact <span class="text-yellow-400 font-semibold">IT Support</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
