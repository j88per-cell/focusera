<template>
  <ThemeLayout>
    <div class="w-full max-w-6xl mx-auto p-6">
      <!-- Header -->
      <GalleryHeader
        :totalPhotos="totalPhotos"
        :currentPage="currentPage"
        :totalPages="totalPages"
        v-model:search="search"
      />

      <!-- Grid -->
      <GalleryGrid
        :photos="photos"
        :columns="columns"
        @select="openLightbox"
      />

      <!-- Pagination -->
      <Pagination
        :currentPage="currentPage"
        :totalPages="totalPages"
        :photosPerPage="photosPerPage"
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
  initialPhotos: {
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
  initialPhotosPerPage: { type: Number, default: 24 },
})

// Reactive state
const currentPage = ref(props.initialPhotos.current_page || 1)
const photosPerPage = ref(props.initialPhotos.per_page || props.initialPhotosPerPage)
const photos = ref(props.initialPhotos.data || [])
const totalPhotos = ref(props.initialPhotos.total || 0)
const totalPages = ref(props.initialPhotos.last_page || 1)
const selectedPhoto = ref(null)
const search = ref('')

// API fetcher
const fetchPhotos = async () => {
  const { data } = await axios.get('/api/photos', {
    params: {
      page: currentPage.value,
      per_page: photosPerPage.value,
      search: search.value
    }
  })
  photos.value = data.data
  totalPhotos.value = data.total
  totalPages.value = data.last_page
}

watch([currentPage, photosPerPage, search], fetchPhotos)

// Methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}
const updatePerPage = (value) => {
  photosPerPage.value = value
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
