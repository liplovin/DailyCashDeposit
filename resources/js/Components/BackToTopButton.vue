<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { ChevronUp } from 'lucide-vue-next';

const isVisible = ref(false);
let scrollContainer = null;

const toggleVisibility = () => {
    if (scrollContainer) {
        isVisible.value = scrollContainer.scrollTop > 300;
    }
};

const scrollToTop = () => {
    if (scrollContainer) {
        scrollContainer.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
};

onMounted(() => {
    // Find the scrollable container (the main content div with overflow-auto)
    scrollContainer = document.querySelector('[data-scroll-container]') || 
                     Array.from(document.querySelectorAll('div'))
                         .find(el => el.classList.contains('overflow-auto') && el.offsetHeight < el.scrollHeight);
    
    if (scrollContainer) {
        scrollContainer.addEventListener('scroll', toggleVisibility);
        toggleVisibility();
    } else {
        // Fallback to window scroll
        window.addEventListener('scroll', toggleVisibility);
        toggleVisibility();
    }
});

onUnmounted(() => {
    if (scrollContainer) {
        scrollContainer.removeEventListener('scroll', toggleVisibility);
    } else {
        window.removeEventListener('scroll', toggleVisibility);
    }
});
</script>

<template>
    <Transition name="fade">
        <button
            v-if="isVisible"
            @click="scrollToTop"
            class="fixed bottom-8 right-8 z-40 bg-gradient-to-r from-yellow-400 to-yellow-500 text-white rounded-full p-3 shadow-lg hover:shadow-xl hover:scale-110 transition-all duration-200 flex items-center justify-center group"
            title="Back to top"
        >
            <ChevronUp class="h-6 w-6 group-hover:translate-y-0.5 transition-transform" />
        </button>
    </Transition>
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
