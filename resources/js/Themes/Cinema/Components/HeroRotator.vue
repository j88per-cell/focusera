<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    images: { type: Array, required: true }, // [{src,alt}, ...]
    intervalMs: { type: Number, default: 5000 },
    title: { type: String, default: "" },
    subtitle: { type: String, default: "" },
});

const idx = ref(0);
let timer = null;

const currentSrc = computed(() => props.images?.[idx.value]?.src ?? "");
const currentAlt = computed(() => props.images?.[idx.value]?.alt ?? props.title);

const stopRotation = () => {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
};

const startRotation = () => {
    stopRotation();
    const total = Array.isArray(props.images) ? props.images.length : 0;
    if (total <= 1 || !Number.isFinite(props.intervalMs) || props.intervalMs <= 0) {
        return;
    }
    timer = setInterval(() => {
        const count = Array.isArray(props.images) ? props.images.length : 0;
        if (count <= 1) {
            stopRotation();
            return;
        }
        idx.value = (idx.value + 1) % count;
    }, props.intervalMs);
};

const resetRotation = () => {
    idx.value = 0;
    startRotation();
};

onMounted(() => {
    resetRotation();
});

onUnmounted(() => {
    stopRotation();
});

watch(
    () => props.images,
    () => {
        resetRotation();
    },
    { deep: true }
);

watch(
    () => props.intervalMs,
    () => {
        startRotation();
    }
);

const scrollTo = (id) => document.getElementById(id)?.scrollIntoView({ behavior: "smooth" });
</script>

<template>
    <section class="relative h-96 md:h-[500px] overflow-hidden">
        <div class="absolute inset-0">
            <img :src="currentSrc" :alt="currentAlt" class="w-full h-full object-cover transition-opacity duration-1000" />
            <div class="absolute inset-0 bg-black/40" />
        </div>
        <div class="relative z-10 flex items-center justify-center h-full text-center text-white">
            <div class="max-w-4xl mx-auto px-4">
                <h2 class="text-4xl md:text-6xl font-bold mb-4 text-balance">
                    {{ title }}
                </h2>
                <p class="text-xl md:text-2xl mb-8 text-pretty">
                    {{ subtitle }}
                </p>
                <button
                    class="bg-accent hover:bg-accent/90 text-accent-foreground px-8 py-3 rounded-lg text-lg font-semibold transition-colors"
                    @click="scrollTo('galleries')">
                    Explore Galleries
                </button>
            </div>
        </div>
    </section>
</template>
