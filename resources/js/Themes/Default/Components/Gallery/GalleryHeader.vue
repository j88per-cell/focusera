<template>
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-900 mb-2">
        Photo Gallery
      </h1>
      <p class="text-gray-600">
        {{ totalPhotos }} photos • Page {{ currentPage }} of {{ totalPages }}
      </p>
    </div>
    <div>
      <input
        v-model="localSearch"
        type="text"
        placeholder="Search photos..."
        class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full sm:w-64"
        @input="$emit('update:search', localSearch)"
      >
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  totalPhotos: Number,
  currentPage: Number,
  totalPages: Number,
  search: String
});

const localSearch = ref(props.search || '');

watch(() => props.search, (val) => {
  if (val !== localSearch.value) localSearch.value = val;
});
</script>
