<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-frame">
        <header class="cinema-header">
          <div>
            <p class="text-xs uppercase tracking-[0.5em] text-white/40">Gallery</p>
            <h1 class="mt-2 text-4xl font-semibold text-white">{{ gallery.title }}</h1>
            <p
              v-if="gallery.description"
              class="mt-3 text-white/60 max-w-3xl"
            >
              {{ gallery.description }}
            </p>
            <p
              v-if="gallery.attribution"
              class="mt-2 text-sm text-white/50"
            >
              Attribution: {{ gallery.attribution }}
            </p>
            <p
              v-if="gallery.notes"
              class="mt-3 text-sm text-white/50 whitespace-pre-line max-w-3xl"
            >
              {{ gallery.notes }}
            </p>
          </div>
          <Link
            href="/galleries"
            class="cinema-back"
          >
            ← Galleries
          </Link>
        </header>

        <div
          v-if="childGalleries.length"
          class="cinema-panel mb-12"
        >
          <h2 class="text-2xl font-semibold text-white mb-6 uppercase tracking-[0.3em]">Sub Galleries</h2>
          <GalleryGrid
            :galleries="childGalleries"
            :columns="4"
          />
        </div>

        <div
          v-if="Array.isArray(gallery.photos) && gallery.photos.length"
          class="cinema-grid"
        >
          <button
            v-for="photo in gallery.photos"
            :key="photo.id"
            type="button"
            class="cinema-thumb"
            @click="openLightbox(photo)"
          >
            <img
              :src="thumbSrc(photo)"
              :alt="photo.title || 'Photo'"
              loading="lazy"
            >
          </button>
        </div>

        <div
          v-else-if="!childGalleries.length"
          class="text-center text-white/50"
        >
          No photos in this gallery yet.
        </div>
      </div>
    </section>

    <Lightbox
      v-if="selectedPhoto"
      :photo="selectedPhoto"
      @close="closeLightbox"
    >
      <template #actions="{ photo }">
        <button
          v-if="orderingEnabled"
          class="cinema-buy"
          @click="openBuy(photo)"
        >
          Buy Print
        </button>
      </template>
    </Lightbox>
  </ThemeLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ThemeLayout from '../../Layouts/app.layout.vue';
import Lightbox from '../../Components/Gallery/Lightbox.vue';
import GalleryGrid from '../../Components/Gallery/GalleryGrid.vue';
import { useCart } from '../../composables/useCart.js';

const props = defineProps({
  gallery: Object,
});

const page = usePage();
const siteSettings = computed(() => page.props?.site ?? {});
const salesEnabled = computed(() => toBoolean(page.props?.features?.sales));
const usePhotoProxy = computed(() => toBoolean(siteSettings.value?.photoproxy));
const publicBaseUrl = computed(() => siteSettings.value?.storage?.public_base_url || '/storage');
const childGalleries = computed(() => Array.isArray(props.gallery?.child_galleries) ? props.gallery.child_galleries : []);
const selectedPhoto = ref(null);
const orderingEnabled = computed(() => salesEnabled.value && Boolean(props.gallery?.allow_orders));
const { addItem } = useCart();

function normalizeSrc(src) {
  if (!src) return '';
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src;
  if (src.startsWith('storage/')) return `/${src}`;
  return joinPublicBase(src);
}

function openLightbox(photo) {
  const url = webImageUrl(photo);
  selectedPhoto.value = { ...photo, url };
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  selectedPhoto.value = null;
  document.body.style.overflow = 'auto';
}

async function openBuy(photo) {
  try {
    await addItem({ photo_id: photo.id, quantity: 1, unit_price_base: 10.0 });
    alert('Added to cart');
  } catch (e) {
    alert('Failed to add to cart');
  }
}

function thumbSrc(photo) {
  if (photo?.thumb_url) return photo.thumb_url;
  if (photo?.path_thumb) return normalizeSrc(photo.path_thumb);
  return webImageUrl(photo);
}

function webImageUrl(photo) {
  const direct = normalizeSrc(photo?.web_url || photo?.path_web || photo?.url || photo?.path_original);
  if (!usePhotoProxy.value) return direct;
  if (!photo?.id) return direct;
  const version = photo?.updated_at ? encodeURIComponent(photo.updated_at) : photo.id;
  return `/media/photos/${photo.id}?v=${version}`;
}

function toBoolean(value) {
  if (typeof value === 'string') {
    const lower = value.toLowerCase();
    if (['1', 'true', 'yes', 'on'].includes(lower)) return true;
    if (['0', 'false', 'no', 'off', ''].includes(lower)) return false;
  }
  if (typeof value === 'number') return value === 1;
  return Boolean(value);
}

function joinPublicBase(path) {
  const base = publicBaseUrl.value || '';
  if (!base) return `/${path}`;
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}
</script>

<style scoped>
.cinema-shell {
  min-height: calc(100vh - 6rem);
  padding: 4rem 1.5rem 6rem;
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.45), rgba(5, 6, 8, 0.95));
}

.cinema-frame {
  width: min(1100px, 100%);
  margin: 0 auto;
}

.cinema-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 3rem;
}

.cinema-back {
  color: rgba(248, 250, 252, 0.75);
  text-transform: uppercase;
  letter-spacing: 0.4em;
  font-size: 0.75rem;
  padding-top: 0.75rem;
  transition: color 200ms ease;
}

.cinema-back:hover {
  color: #f8fafc;
}

.cinema-panel {
  padding: clamp(1.75rem, 3vw, 2.5rem);
  border-radius: 24px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.55));
  backdrop-filter: blur(12px);
}

.cinema-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem;
}

.cinema-thumb {
  position: relative;
  overflow: hidden;
  border-radius: 24px;
  aspect-ratio: 1 / 1;
  background: rgba(30, 41, 59, 0.6);
  border: 1px solid rgba(148, 163, 184, 0.2);
  transition: transform 200ms ease, border-color 200ms ease;
}

.cinema-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 400ms ease;
}

.cinema-thumb:hover {
  transform: translateY(-6px);
  border-color: rgba(148, 163, 184, 0.45);
}

.cinema-thumb:hover img {
  transform: scale(1.08);
}

.cinema-buy {
  padding: 0.75rem 1.75rem;
  border-radius: 9999px;
  background: rgba(96, 165, 250, 0.25);
  color: #f8fafc;
  text-transform: uppercase;
  letter-spacing: 0.4em;
  font-size: 0.75rem;
  transition: background 200ms ease;
}

.cinema-buy:hover {
  background: rgba(96, 165, 250, 0.45);
}
</style>
