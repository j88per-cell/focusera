<template>
  <div class="cinema-pagination">
    <div class="flex items-center gap-3">
      <button
        :disabled="currentPage === 1"
        class="cinema-nav-btn"
        @click="$emit('page', currentPage - 1)"
      >
        Previous
      </button>

      <div class="flex gap-2">
        <button
          v-for="page in visiblePages"
          :key="page"
          :class="[
            'cinema-page-btn',
            page === currentPage ? 'active' : null
          ]"
          @click="$emit('page', page)"
        >
          {{ page }}
        </button>
      </div>

      <button
        :disabled="currentPage === totalPages"
        class="cinema-nav-btn"
        @click="$emit('page', currentPage + 1)"
      >
        Next
      </button>
    </div>

    <div class="cinema-select">
      <span class="text-xs uppercase tracking-[0.35em] text-white/45">Photos per page</span>
      <select
        :value="photosPerPage"
        class="cinema-input"
        @change="$emit('perPage', parseInt($event.target.value))"
      >
        <option :value="12">12</option>
        <option :value="24">24</option>
        <option :value="48">48</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: Number,
  totalPages: Number,
  photosPerPage: Number
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2));
  let end = Math.min(props.totalPages, start + maxVisible - 1);

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) pages.push(i);
  return pages;
});
</script>

<style scoped>
.cinema-pagination {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

@media (min-width: 768px) {
  .cinema-pagination {
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
  }
}

.cinema-nav-btn {
  padding: 0.65rem 1.75rem;
  border-radius: 9999px;
  background: rgba(248, 250, 252, 0.12);
  color: #f8fafc;
  font-size: 0.75rem;
  letter-spacing: 0.35em;
  text-transform: uppercase;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-nav-btn:hover:not(:disabled) {
  background: rgba(248, 250, 252, 0.2);
  transform: translateY(-1px);
}

.cinema-nav-btn:disabled {
  opacity: 0.35;
  cursor: not-allowed;
}

.cinema-page-btn {
  min-width: 2.5rem;
  padding: 0.5rem 0.75rem;
  border-radius: 9999px;
  border: 1px solid rgba(248, 250, 252, 0.2);
  background: rgba(15, 23, 42, 0.6);
  color: rgba(248, 250, 252, 0.7);
  font-size: 0.75rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  transition: background 200ms ease, color 200ms ease, border 200ms ease;
}

.cinema-page-btn:hover {
  border-color: rgba(248, 250, 252, 0.45);
  color: #f8fafc;
}

.cinema-page-btn.active {
  background: rgba(248, 250, 252, 0.25);
  color: #f8fafc;
  border-color: transparent;
}

.cinema-select {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 0.5rem;
}

.cinema-input {
  border-radius: 9999px;
  border: 1px solid rgba(248, 250, 252, 0.25);
  background: rgba(15, 23, 42, 0.6);
  color: #f8fafc;
  padding: 0.55rem 1.25rem;
  font-size: 0.75rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  outline: none;
}

.cinema-input:focus {
  border-color: rgba(248, 250, 252, 0.5);
}
</style>
