<template>
  <div class="flex items-center justify-between">
    <div class="flex items-center space-x-2">
      <button
        @click="$emit('page', currentPage - 1)"
        :disabled="currentPage === 1"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Previous
      </button>

      <div class="flex space-x-1">
        <button
          v-for="page in visiblePages"
          :key="page"
          @click="$emit('page', page)"
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
        @click="$emit('page', currentPage + 1)"
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
          :value="photosPerPage"
          @change="$emit('perPage', parseInt($event.target.value))"
          class="ml-2 border border-gray-300 rounded-md px-2 py-1 text-sm"
        >
          <option :value="12">12</option>
          <option :value="24">24</option>
          <option :value="48">48</option>
        </select>
      </label>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: Number,
  totalPages: Number,
  photosPerPage: Number
})

const visiblePages = computed(() => {
  const pages = []
  const maxVisible = 5
  let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2))
  let end = Math.min(props.totalPages, start + maxVisible - 1)

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1)
  }

  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})
</script>
