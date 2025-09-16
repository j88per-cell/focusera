<template>
  <ThemeLayout>
    <div class="w-full max-w-6xl mx-auto p-6">
      <!-- Header -->
      <GalleryHeader
        :totalPhotos="totalGalleries"
        :currentPage="currentPage"
        :totalPages="totalPages"
        v-model:search="search"
      />

      <GalleryGrid
        :galleries="galleries"
        :columns="columns"
      />

      <Pagination
        :currentPage="currentPage"
        :totalPages="totalPages"
        :photosPerPage="perPage"
        @page="goToPage"
        @perPage="updatePerPage"
      />

      <!-- Lightbox -->
      <Lightbox
        v-if="selectedPhoto"
        :photo="selectedPhoto"
        @close="closeLightbox"
      />
    </div>
  </ThemeLayout>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import ThemeLayout from '@/Layouts/app.layout.vue'
import GalleryHeader from '@/Components/Gallery/GalleryHeader.vue'
import GalleryGrid from '@/Components/Gallery/GalleryGrid.vue'
import Pagination from '@/Components/Gallery/Pagination.vue'
import Lightbox from '@/Components/Gallery/Lightbox.vue'

const props = defineProps({
  galleries: {
    type: Object,
    default: () => ({
      data: [],
      current_page: 1,
      per_page: 12,
      total: 0,
      last_page: 1,
    }),
  },
  columns: { type: Number, default: 4 },
  initialPerPage: { type: Number, default: 12 },
});

// Reactive state
const currentPage   = ref(props.galleries.current_page || 1);
const perPage       = ref(props.galleries.per_page || props.initialPerPage);
const galleries     = ref(props.galleries.data || []);
const totalGalleries= ref(props.galleries.total || 0);
const totalPages    = ref(props.galleries.last_page || 1);
const search        = ref('');

// API fetcher
const fetchPhotos = async () => {
  const { data } = await axios.get('/api/photos', {
    params: {
      page: currentPage.value,
      per_page: perPage.value,
      search: search.value
    }
  })
  photos.value = data.data
  totalPhotos.value = data.total
  totalPages.value = data.last_page
}

watch([currentPage, perPage, search], fetchPhotos)

// Methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}
const updatePerPage = (value) => {
  perPage.value = value
  currentPage.value = 1
}
const openLightbox = (photo) => {
  selectedPhoto.value = photo
  document.body.style.overflow = 'hidden'
}
const closeLightbox = () => {
  selectedPhoto.value = null
  document.body.style.overflow = 'auto'
}
</script>