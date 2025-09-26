<template>
  <ThemeLayout>
    <div class="w-full max-w-6xl mx-auto p-6">
      <div class="mb-6 flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold">{{ gallery.title }}</h1>
          <p v-if="gallery.description" class="text-muted-foreground mt-1">{{ gallery.description }}</p>
        </div>
        <Link href="/galleries" class="text-accent hover:underline">← Back to galleries</Link>
      </div>

      <div v-if="Array.isArray(gallery.photos) && gallery.photos.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <button
          v-for="photo in gallery.photos"
          :key="photo.id"
          type="button"
          class="group relative overflow-hidden rounded-lg bg-secondary aspect-square hover:shadow-md transition"
          @click="openLightbox(photo)"
        >
          <img
            :src="thumbSrc(photo)"
            :alt="photo.title || 'Photo'"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
            loading="lazy"
          />
        </button>
      </div>

      <div v-else class="text-muted-foreground">No photos in this gallery yet.</div>

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
import ThemeLayout from '../../Layouts/app.layout.vue'
import Lightbox from '@/Components/Gallery/Lightbox.vue'
import { useCart } from '@/composables/useCart.js'

const props = defineProps({
  gallery: Object,
})

const page = usePage()
const siteSettings = computed(() => page.props?.site ?? {})
const salesEnabled = computed(() => toBoolean(page.props?.features?.sales))
const usePhotoProxy = computed(() => toBoolean(siteSettings.value?.photoproxy))
const publicBaseUrl = computed(() => siteSettings.value?.storage?.public_base_url || '/storage')
const selectedPhoto = ref(null)
const buyPhoto = ref(null)
const orderingEnabled = computed(() => salesEnabled.value && Boolean(props.gallery?.allow_orders))
const { addItem } = useCart()

function normalizeSrc(src) {
  if (!src) return ''
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src
  if (src.startsWith('storage/')) return `/${src}`
  return joinPublicBase(src)
}

function openLightbox(photo) {
  const url = webImageUrl(photo)
  selectedPhoto.value = { ...photo, url }
  document.body.style.overflow = 'hidden'
}

function closeLightbox() {
  selectedPhoto.value = null
  document.body.style.overflow = 'auto'
}

function openBuy(photo) {
  buyPhoto.value = photo
  addItem({ photo_id: photo.id, quantity: 1, unit_price_base: 10.0 })
    .then(() => { alert('Added to cart') })
    .catch(() => { alert('Failed to add to cart') })
}

function thumbSrc(photo) {
  if (photo?.thumb_url) return photo.thumb_url
  if (photo?.path_thumb) return normalizeSrc(photo.path_thumb)
  return webImageUrl(photo)
}

function webImageUrl(photo) {
  const direct = normalizeSrc(photo?.path_web || photo?.url || photo?.path_original)
  if (!usePhotoProxy.value) return direct
  if (!photo?.id) return direct
  const version = photo?.updated_at ? encodeURIComponent(photo.updated_at) : photo.id
  return `/media/photos/${photo.id}?v=${version}`
}

function toBoolean(value) {
  if (typeof value === 'string') {
    const lower = value.toLowerCase()
    if (['1', 'true', 'yes', 'on'].includes(lower)) return true
    if (['0', 'false', 'no', 'off', ''].includes(lower)) return false
  }
  if (typeof value === 'number') return value === 1
  return Boolean(value)
}

function joinPublicBase(path) {
  const base = publicBaseUrl.value || ''
  if (!base) return `/${path}`
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`
}
</script>
