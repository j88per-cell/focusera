<template>
    <ThemeLayout>
  <div class="w-full max-w-6xl mx-auto p-6">
    <!-- Gallery Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">Photo Gallery</h1>
      <p class="text-gray-600">{{ totalPhotos }} photos • Page {{ currentPage }} of {{ totalPages }}</p>
    </div>

    <!-- Gallery Grid -->
    <div 
      class="grid gap-4 mb-8"
      :class="gridClasses"
    >
      <div
        v-for="photo in paginatedPhotos"
        :key="photo.id"
        class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square cursor-pointer hover:shadow-lg transition-all duration-300"
        @click="openLightbox(photo)"
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
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        
        <div class="flex space-x-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            @click="goToPage(page)"
            :class="[
              'px-3 py-2 text-sm font-medium rounded-md',
              page === currentPage
                ? 'bg-blue-600 text-white'
                : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50'
            ]"
          >
            {{ page }}
          </button>
        </div>

        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>

      <div class="flex items-center space-x-4">
        <label class="text-sm font-medium text-gray-700">
          Photos per page:
          <select
            v-model="photosPerPage"
            @change="resetToFirstPage"
            class="ml-2 border border-gray-300 rounded-md px-2 py-1 text-sm"
          >
            <option :value="12">12</option>
            <option :value="24">24</option>
            <option :value="48">48</option>
          </select>
        </label>
      </div>
    </div>

    <!-- Lightbox Modal -->
    <div
      v-if="selectedPhoto"
      class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4"
      @click="closeLightbox"
    >
      <div class="relative max-w-4xl max-h-full">
        <img
          :src="selectedPhoto.url"
          :alt="selectedPhoto.title"
          class="max-w-full max-h-full object-contain"
          @click.stop
        />
        <button
          @click="closeLightbox"
          class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold"
        >
          ×
        </button>
        <div class="absolute bottom-4 left-4 text-white">
          <h3 class="text-lg font-semibold">{{ selectedPhoto.title }}</h3>
          <p class="text-sm text-gray-300">by {{ selectedPhoto.photographer }}</p>
        </div>
      </div>
    </div>
  </div>
  </ThemeLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import ThemeLayout from '../../Layouts/app.layout.vue';

// Props
const props = defineProps({
  columns: {
    type: Number,
    default: 4,
    validator: (value) => value >= 1 && value <= 6
  },
  initialPhotosPerPage: {
    type: Number,
    default: 24
  }
})

// Reactive state
const currentPage = ref(1)
const photosPerPage = ref(props.initialPhotosPerPage)
const selectedPhoto = ref(null)
const photos = ref([])

// Sample photo data (replace with your actual data source)
const generateSamplePhotos = () => {
  const photographers = ['Alex Johnson', 'Maria Garcia', 'David Chen', 'Sarah Wilson', 'Mike Brown']
  const subjects = ['Landscape', 'Portrait', 'Architecture', 'Nature', 'Street', 'Abstract']
  
  return Array.from({ length: 150 }, (_, i) => ({
    id: i + 1,
    title: `${subjects[i % subjects.length]} Photo ${i + 1}`,
    photographer: photographers[i % photographers.length],
    url: `/placeholder.svg?height=400&width=400&query=${subjects[i % subjects.length]} photography`
  }))
}

// Computed properties
const gridClasses = computed(() => {
  const columnMap = {
    1: 'grid-cols-1',
    2: 'grid-cols-1 sm:grid-cols-2',
    3: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3',
    4: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
    5: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5',
    6: 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6'
  }
  return columnMap[props.columns] || columnMap[4]
})

const totalPhotos = computed(() => photos.value.length)

const totalPages = computed(() => Math.ceil(totalPhotos.value / photosPerPage.value))

const paginatedPhotos = computed(() => {
  const start = (currentPage.value - 1) * photosPerPage.value
  const end = start + photosPerPage.value
  return photos.value.slice(start, end)
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxVisible / 2))
  let end = Math.min(totalPages.value, start + maxVisible - 1)
  
  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Methods
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const resetToFirstPage = () => {
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

// Lifecycle
onMounted(() => {
  photos.value = generateSamplePhotos()
})

// Cleanup on unmount
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.body.style.overflow = 'auto'
})
</script>