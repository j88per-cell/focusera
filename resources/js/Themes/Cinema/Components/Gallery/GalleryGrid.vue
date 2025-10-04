<template>
  <div
    :class="gridClasses"
    class="grid gap-6"
  >
    <Link
      v-for="gallery in galleries"
      :key="gallery.id ?? gallery.slug ?? gallery.title"
      :href="galleryHref(gallery)"
      class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white/60 focus-visible:ring-offset-slate-900"
    >
      <PhotoCard :gallery="gallery" />
    </Link>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PhotoCard from './PhotoCard.vue';

const props = defineProps({
  galleries: Array,
  columns: { type: Number, default: 4 }
});

const galleryHref = (gallery = {}) => {
  if (gallery.url) return gallery.url;
  if (gallery.slug) return `/galleries/${gallery.slug}`;
  if (gallery.id) return `/galleries/${gallery.id}`;
  return '/galleries';
};

const gridClasses = computed(() => {
  const map = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    5: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
    6: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
  };
  return map[props.columns] || map[4];
});
</script>
