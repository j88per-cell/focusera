<template>
  <div class="cinema-header">
    <div>
      <h1 class="text-3xl font-semibold text-white mb-2">
        Photo Gallery
      </h1>
      <p class="text-white/60 text-sm tracking-[0.25em] uppercase">
        {{ totalPhotos }} photos • Page {{ currentPage }} of {{ totalPages }}
      </p>
    </div>
    <div class="cinema-search">
      <input
        v-model="localSearch"
        type="text"
        placeholder="Search galleries…"
        class="cinema-input"
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

<style scoped>
.cinema-header {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

@media (min-width: 640px) {
  .cinema-header {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.cinema-search {
  width: 100%;
  max-width: 320px;
}

.cinema-input {
  width: 100%;
  border-radius: 9999px;
  border: 1px solid rgba(248, 250, 252, 0.25);
  background: rgba(15, 23, 42, 0.6);
  color: #f8fafc;
  padding: 0.75rem 1.5rem;
  font-size: 0.875rem;
  outline: none;
  transition: border 200ms ease, background 200ms ease;
}

.cinema-input::placeholder {
  color: rgba(248, 250, 252, 0.4);
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

.cinema-input:focus {
  border-color: rgba(248, 250, 252, 0.5);
  background: rgba(15, 23, 42, 0.75);
}
</style>
