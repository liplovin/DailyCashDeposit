<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Admin Dashboard" />
    <AdminLayout>
        <div class="space-y-8">
            <!-- Welcome Section -->
            <div>
                <h1 class="text-3xl font-black text-gray-900">Welcome, {{ user.name }}!</h1>
                <p class="text-gray-600 mt-2">Manage your system administration tasks and settings from here.</p>
            </div>
        </div>
    </AdminLayout>
</template>
