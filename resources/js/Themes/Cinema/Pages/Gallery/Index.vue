<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-frame">
        <header class="mb-10 text-center">
          <p class="text-xs uppercase tracking-[0.5em] text-white/50">Collections</p>
          <h1 class="mt-3 text-4xl font-semibold text-white">
            Galleries
          </h1>
        </header>

        <div class="cinema-panel">
          <GalleryHeader
            v-model:search="search"
            :total-photos="totalGalleries"
            :current-page="currentPage"
            :total-pages="totalPages"
          />

          <div class="mt-8">
            <GalleryGrid
              :galleries="galleries"
              :columns="columns"
            />
          </div>

          <div class="mt-10">
            <Pagination
              :current-page="currentPage"
              :total-pages="totalPages"
              :photos-per-page="perPage"
              @page="goToPage"
              @per-page="updatePerPage"
            />
          </div>
        </div>
      </div>
    </section>

    <Lightbox
      v-if="selectedPhoto"
      :photo="selectedPhoto"
      @close="closeLightbox"
    />
  </ThemeLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import ThemeLayout from '../../Layouts/app.layout.vue';
import GalleryHeader from '../../Components/Gallery/GalleryHeader.vue';
import GalleryGrid from '../../Components/Gallery/GalleryGrid.vue';
import Pagination from '../../Components/Gallery/Pagination.vue';
import Lightbox from '../../Components/Gallery/Lightbox.vue';

const props = defineProps({
  galleries: {
    type: Object,
    default: () => ({ data: [], current_page: 1, per_page: 12, total: 0, last_page: 1 }),
  },
  columns: { type: Number, default: 4 },
  initialPerPage: { type: Number, default: 12 },
});

const currentPage    = ref(props.galleries.current_page || 1);
const perPage        = ref(props.galleries.per_page || props.initialPerPage);
const galleries      = ref(props.galleries.data || []);
const totalGalleries = ref(props.galleries.total || 0);
const totalPages     = ref(props.galleries.last_page || 1);
const search         = ref('');
const selectedPhoto  = ref(null);

const fetchIfApiExists = async () => {
  // Placeholder for future API driven filtering
};

watch([currentPage, perPage, search], fetchIfApiExists);

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page;
};

const updatePerPage = (value) => {
  perPage.value = value;
  currentPage.value = 1;
};

const closeLightbox = () => {
  selectedPhoto.value = null;
  document.body.style.overflow = 'auto';
};
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

.cinema-panel {
  margin-top: 3rem;
  padding: clamp(2rem, 4vw, 3rem);
  border-radius: 28px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.55));
  backdrop-filter: blur(12px);
  box-shadow: 0 30px 60px rgba(15, 23, 42, 0.45);
}
</style>
