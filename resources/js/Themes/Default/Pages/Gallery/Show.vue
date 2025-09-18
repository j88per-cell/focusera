<template>
  <ThemeLayout>
    <div class="w-full max-w-6xl mx-auto p-6">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">{{ gallery.title }}</h1>
          <p v-if="gallery.description" class="text-gray-600 mt-1">{{ gallery.description }}</p>
        </div>
        <Link href="/galleries" class="text-primary-600 hover:underline">← Back to galleries</Link>
      </div>

      <div v-if="Array.isArray(gallery.photos) && gallery.photos.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <button
          v-for="photo in gallery.photos"
          :key="photo.id"
          type="button"
          class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square hover:shadow-md transition"
          @click="openLightbox(photo)"
        >
          <img
            :src="normalizeSrc(photo.path_thumb || photo.path_web || photo.url)"
            :alt="photo.title || 'Photo'"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
          />
        </button>
      </div>

      <div v-else class="text-gray-600">No photos in this gallery yet.</div>

      <Lightbox
        v-if="selectedPhoto"
        :photo="selectedPhoto"
        @close="closeLightbox"
      />
    </div>
  </ThemeLayout>
  
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import ThemeLayout from '@/Layouts/app.layout.vue'
import Lightbox from '@/Components/Gallery/Lightbox.vue'

const props = defineProps({
  gallery: Object,
})

const selectedPhoto = ref(null)

function normalizeSrc(src) {
  if (!src) return ''
  // Absolute URLs or already rooted
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src
  // Normalize common storage paths
  if (src.startsWith('storage/')) return `/${src}`
  return `/${src}`
}

function openLightbox(photo) {
  // Adapt to Lightbox API expecting `photo.url`
  const url = normalizeSrc(photo?.path_web || photo?.url || photo?.path_original)
  selectedPhoto.value = {
    ...photo,
    url,
  }
  document.body.style.overflow = 'hidden'
}

function closeLightbox() {
  selectedPhoto.value = null
  document.body.style.overflow = 'auto'
}
</script>
