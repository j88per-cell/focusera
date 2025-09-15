<template>
  <div :class="gridClasses" class="grid gap-4 mb-8">
    <PhotoCard
      v-for="photo in photos"
      :key="photo.id"
      :photo="photo"
      @click="$emit('select', photo)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue'
import PhotoCard from './PhotoCard.vue'

const props = defineProps({
  photos: Array,
  columns: { type: Number, default: 4 }
})

const gridClasses = computed(() => {
  const map = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    5: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
    6: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
  }
  return map[props.columns] || map[4]
})
</script>
