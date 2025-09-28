<template>
  <ThemeLayout>
    <div class="w-full max-w-6xl mx-auto p-6">
      <!-- Header / Search / Counts -->
      <GalleryHeader
        v-model:search="search"
        :total-photos="totalGalleries"
        :current-page="currentPage"
        :total-pages="totalPages"
      />

      <!-- Galleries Grid (uses real data from controller) -->
      <GalleryGrid
        :galleries="galleries"
        :columns="columns"
      />

      <!-- Pagination Controls -->
      <Pagination
        :current-page="currentPage"
        :total-pages="totalPages"
        :photos-per-page="perPage"
        @page="goToPage"
        @per-page="updatePerPage"
      />

      <!-- Optional: lightbox hook if/when needed -->
      <Lightbox
        v-if="selectedPhoto"
        :photo="selectedPhoto"
        @close="closeLightbox"
      />
    </div>
  </ThemeLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import ThemeLayout from '../../Layouts/app.layout.vue';
import axios from 'axios';
import GalleryHeader from '@/Components/Gallery/GalleryHeader.vue';
import GalleryGrid from '@/Components/Gallery/GalleryGrid.vue';
import Pagination from '@/Components/Gallery/Pagination.vue';
import Lightbox from '@/Components/Gallery/Lightbox.vue';

// Props from controller
const props = defineProps({
  galleries: {
    type: Object,
    default: () => ({ data: [], current_page: 1, per_page: 12, total: 0, last_page: 1 }),
  },
  columns: { type: Number, default: 4 },
  initialPerPage: { type: Number, default: 12 },
});

// Reactive state based on server props
const currentPage    = ref(props.galleries.current_page || 1);
const perPage        = ref(props.galleries.per_page || props.initialPerPage);
const galleries      = ref(props.galleries.data || []);
const totalGalleries = ref(props.galleries.total || 0);
const totalPages     = ref(props.galleries.last_page || 1);
const search         = ref('');
const selectedPhoto  = ref(null);

// Optional: refetch when controls change (wire up to your real endpoint if/when added)
const fetchIfApiExists = async () => {
  // Placeholder: if you later add an API, wire it here
};
watch([currentPage, perPage, search], fetchIfApiExists);

const goToPage = (page) => { if (page >= 1 && page <= totalPages.value) currentPage.value = page; };
const updatePerPage = (value) => { perPage.value = value; currentPage.value = 1; };
const closeLightbox = () => { selectedPhoto.value = null; document.body.style.overflow = 'auto'; };
</script>
