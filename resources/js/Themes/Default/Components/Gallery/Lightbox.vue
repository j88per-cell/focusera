<template>
  <div
    class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4"
    @click="$emit('close')"
  >
    <div class="relative max-w-4xl max-h-full" @click.stop>
      <img
        :src="photo.url"
        :alt="photo.title"
        class="max-w-full max-h-full object-contain"
      />
      <button
        @click="$emit('close')"
        class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold"
      >
        ×
      </button>
      <div class="absolute bottom-4 left-4 text-white">
        <h3 class="text-lg font-semibold">{{ photo.title }}</h3>
        <p class="text-sm text-gray-300">by {{ photo.photographer }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  photo: Object
})
</script>
