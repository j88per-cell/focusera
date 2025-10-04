<template>
  <div
    class="group relative overflow-hidden rounded-[28px] aspect-square border border-white/10 bg-slate-900/60 shadow-lg shadow-black/30 transition-transform duration-300 hover:-translate-y-1"
  >
    <template v-if="hasThumb">
      <img
        :src="thumbSrc"
        :alt="gallery.title"
        class="w-full h-full object-cover transition-transform duration-[600ms] group-hover:scale-105"
        loading="lazy"
      >
    </template>
    <template v-else>
      <div class="w-full h-full flex items-center justify-center bg-slate-800 text-white/70">
        <svg
          class="w-12 h-12 opacity-80"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="1.5"
            d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
          />
        </svg>
      </div>
    </template>
    <div
      v-if="isFeatured"
      class="absolute top-4 left-4 rounded-full bg-white/15 px-3 py-1 text-[11px] uppercase tracking-[0.4em] text-white"
    >
      Featured
    </div>
    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/10 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100 flex items-end">
      <div class="w-full p-5">
        <h3 class="text-white text-lg font-semibold text-pretty">
          {{ gallery.title }}
        </h3>
        <p class="mt-1 text-xs uppercase tracking-[0.4em] text-white/60">
          {{ photosLabel }}<span v-if="childLabel"> • {{ childLabel }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  gallery: Object
});

const page = usePage();
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage');

function normalizeSrc(src) {
  if (!src) return '';
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src;
  if (src.startsWith('storage/')) return `/${src}`;
  return joinPublicBase(src);
}

const thumbSrc = computed(() => {
  return normalizeSrc(props.gallery?.thumbnail || props.gallery?.path_thumb || props.gallery?.thumb_url || props.gallery?.path_web || props.gallery?.web_url);
});

const hasThumb = computed(() => Boolean(thumbSrc.value));

const isFeatured = computed(() => Boolean(props.gallery?.featured));

const photosLabel = computed(() => {
  const count = Number(props.gallery?.photos_count ?? 0);
  return `${count} photo${count === 1 ? '' : 's'}`;
});

const childLabel = computed(() => {
  const count = Number(props.gallery?.children_count ?? 0);
  if (!count) return '';
  return `${count} sub-${count === 1 ? 'gallery' : 'galleries'}`;
});

function joinPublicBase(path) {
  const base = publicBaseUrl.value || '';
  if (!base) return '/' + path.replace(/^\/+/, '');
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}
</script>
