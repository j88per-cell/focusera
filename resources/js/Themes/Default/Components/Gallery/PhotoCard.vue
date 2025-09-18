<template>
  <div
    class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square hover:shadow-lg transition-all duration-300"
  >
    <img
      :src="thumbSrc"
      :alt="gallery.title"
      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      loading="lazy"
    />
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-end">
      <div class="p-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <h3 class="font-semibold text-sm">{{ gallery.title }}</h3>
        <p class="text-xs text-gray-200"></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  gallery: Object
})

function normalizeSrc(src) {
  if (!src) return ''
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src
  if (src.startsWith('storage/')) return `/${src}`
  return `/${src}`
}

const thumbSrc = computed(() => {
  return normalizeSrc(props.gallery?.thumbnail || props.gallery?.path_thumb || props.gallery?.path_web)
})
</script>
