<script setup>
import Treasury2Layout from '@/Layouts/Treasury2Layout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <Head title="Treasury 2 Dashboard" />
    <Treasury2Layout>
        <!-- Welcome Card -->
        <div class="bg-white rounded-xl shadow-sm p-8 max-w-2xl">
            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                Welcome, {{ user.name }}!
            </h3>
            <p class="text-gray-600 text-lg">
                You have access to treasury 2 management features. Use the sidebar menu to navigate through the system.
            </p>
        </div>
    </Treasury2Layout>
</template>
