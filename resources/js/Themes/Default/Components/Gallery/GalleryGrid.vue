<template>
  <div
    :class="gridClasses"
    class="grid gap-4 mb-8"
  >
    <Link
      v-for="gallery in galleries"
      :key="gallery.id"
      :href="`/galleries/${gallery.id}`"
      class="block"
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
