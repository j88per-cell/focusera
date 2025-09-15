<template>
  <div
    class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square cursor-pointer hover:shadow-lg transition-all duration-300"
    @click="$emit('click', photo)"
  >
    <img
      :src="photo.url"
      :alt="photo.title"
      class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
      loading="lazy"
    />
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-end">
      <div class="p-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <h3 class="font-semibold text-sm">{{ photo.title }}</h3>
        <p class="text-xs text-gray-200">{{ photo.photographer }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  photo: Object
})
</script>
