<script setup>
import { computed, onMounted, onBeforeUnmount, ref, watch } from 'vue';
import ThemeLayout from '../../Layouts/app.layout.vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const container = ref(null);
const touchStartY = ref(null);
const lastAdvanceAt = ref(0);
const publicBase = computed(() => page.props?.site?.storage?.public_base_url ?? "");

const MAX_FEATURED = 12;
const featuredGalleries = computed(() => {
    const items = Array.isArray(page.props?.featuredGalleries) ? page.props.featuredGalleries : [];
    return items.slice(0, MAX_FEATURED);
});

function normalizeSrc(src, fallback = "") {
    if (!src) return fallback;
    const value = String(src).trim();
    if (!value.length) return fallback;
    if (/^(https?:)?\/\//i.test(value) || value.startsWith("data:")) {
        return value;
    }
    if (value.startsWith("/")) {
        return value;
    }
    if (value.startsWith("storage/")) {
        return `/${value.replace(/^\/+/, "")}`;
    }

    const base = publicBase.value || "";
    if (base) {
        return `${base.replace(/\/$/, "")}/${value.replace(/^\//, "")}`;
    }

    return `/${value.replace(/^\/+/, "")}`;
}

const frames = computed(() => {
    if (!featuredGalleries.value.length) {
        return [
            {
                id: "cinema-placeholder",
                title: "Placeholder",
                src: "/placeholder.svg?height=1080&width=1920",
            },
        ];
    }

    return featuredGalleries.value.map((gallery, index) => {
        const fallback = `/placeholder.svg?height=1080&width=1920&text=${encodeURIComponent(gallery.title ?? `Gallery ${index + 1}`)}`;
        const raw = gallery.heroImage || gallery.featuredImage || gallery.thumbnail || gallery.thumb_url;
        const src = normalizeSrc(raw, fallback);
        return {
            id: gallery.id ?? gallery.slug ?? index,
            src,
            alt: gallery.title || `Gallery ${index + 1}`,
            url: gallery.url || (gallery.slug ? `/galleries/${gallery.slug}` : gallery.id ? `/galleries/${gallery.id}` : "/galleries"),
        };
    });
});

const activeIndex = ref(0);
const activeFrame = computed(() => frames.value[activeIndex.value % frames.value.length]);

watch(frames, (value) => {
    if (activeIndex.value >= value.length) {
        activeIndex.value = 0;
    }
});

function advanceFrame() {
    if (!frames.value.length) return;
    const now = Date.now();
    if (now - lastAdvanceAt.value < 350) {
        return;
    }
    lastAdvanceAt.value = now;
    activeIndex.value = (activeIndex.value + 1) % frames.value.length;
}

function onWheel(event) {
    if (!frames.value.length) return;
    if (event.deltaY > 12) {
        event.preventDefault();
        advanceFrame();
    }
}

function onTouchStart(event) {
    if (!event.touches || event.touches.length === 0) return;
    touchStartY.value = event.touches[0].clientY;
}

function onTouchEnd(event) {
    if (!frames.value.length) return;
    if (touchStartY.value === null) return;
    const touch = event.changedTouches?.[0];
    if (!touch) return;
    const delta = touchStartY.value - touch.clientY;
    touchStartY.value = null;
    if (delta > 40) {
        advanceFrame();
    }
}

onMounted(() => {
    if (!container.value) return;
    container.value.addEventListener("wheel", onWheel, { passive: false });
    container.value.addEventListener("touchstart", onTouchStart, { passive: true });
    container.value.addEventListener("touchend", onTouchEnd, { passive: true });
});

onBeforeUnmount(() => {
    if (!container.value) return;
    container.value.removeEventListener("wheel", onWheel);
    container.value.removeEventListener("touchstart", onTouchStart);
    container.value.removeEventListener("touchend", onTouchEnd);
});
</script>

<template>
    <ThemeLayout>
        <section ref="container" class="cinema-stage">
            <transition name="cinema-fade" mode="out-in">
                <a :key="activeFrame?.id" :href="activeFrame?.url || '/galleries'" class="cinema-frame">
                    <img v-if="activeFrame" :src="activeFrame.src" :alt="activeFrame.alt" />
                    <div class="cinema-scrim" />
                </a>
            </transition>
            <div class="cinema-progress">
                <span v-for="(frame, idx) in frames" :key="frame.id || idx" :class="['progress-dot', { active: idx === activeIndex }]" />
            </div>
        </section>
    </ThemeLayout>
</template>

<style scoped>
.cinema-stage {
    position: relative;
    height: 100vh;
    width: 100%;
    overflow: hidden;
    background: #050608;
    display: flex;
    align-items: stretch;
    justify-content: stretch;
}

.cinema-frame {
    position: relative;
    display: block;
    height: 100%;
    width: 100%;
}

.cinema-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cinema-scrim {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, transparent, rgba(0, 0, 0, 0.65));
}

.cinema-fade-enter-active,
.cinema-fade-leave-active {
    transition: opacity 600ms ease;
}

.cinema-fade-enter-from,
.cinema-fade-leave-to {
    opacity: 0;
}

.cinema-progress {
    position: absolute;
    bottom: 2.5rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 0.75rem;
}

.progress-dot {
    width: 8px;
    height: 8px;
    border-radius: 9999px;
    background: rgba(255, 255, 255, 0.25);
    transition: all 250ms ease;
}

.progress-dot.active {
    background: rgba(255, 255, 255, 0.85);
    transform: scale(1.4);
}
</style>
