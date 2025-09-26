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

      <Lightbox v-if="selectedPhoto" :photo="selectedPhoto" @close="closeLightbox">
        <template #actions="{ photo }">
          <button
            v-if="orderingEnabled"
            @click="openBuy(photo)"
            class="px-3 py-1.5 rounded bg-accent text-white text-sm hover:bg-accent/90">
            Buy
          </button>
        </template>
      </Lightbox>
    </div>
  </ThemeLayout>
  
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import ThemeLayout from '@/Layouts/app.layout.vue'
import Lightbox from '@/Components/Gallery/Lightbox.vue'
import { useCart } from '@/composables/useCart.js'

const props = defineProps({
  gallery: Object,
})

const page = usePage()
const salesEnabled = computed(() => Boolean(page.props?.features?.sales))
const selectedPhoto = ref(null)
const showBuy = ref(false)
const buyPhoto = ref(null)
const orderingEnabled = computed(() => salesEnabled.value && Boolean(props.gallery?.allow_orders))
const { addItem } = useCart()

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

function openBuy(photo) {
  buyPhoto.value = photo
  // open a simple prompt for now; can be replaced with a side drawer later
  // For MVP, add with a fixed base price (e.g., 10.00) and default quantity 1
  addItem({ photo_id: photo.id, quantity: 1, unit_price_base: 10.0 })
    .then(() => {
      alert('Added to cart')
    })
    .catch(() => {
      alert('Failed to add to cart')
    })
}
</script>
