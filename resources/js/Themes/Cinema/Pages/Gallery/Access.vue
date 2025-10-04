<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ code: '' });

function submit() {
  form.post('/access', { preserveScroll: true });
}
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-panel">
        <header class="text-center mb-8">
          <p class="text-xs uppercase tracking-[0.5em] text-white/50">Private</p>
          <h1 class="mt-3 text-3xl font-semibold text-white">Unlock a Gallery</h1>
          <p class="mt-4 text-sm text-white/60">
            Enter the access code provided by your photographer to view the collection.
          </p>
        </header>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label class="block text-xs uppercase tracking-[0.4em] text-white/50">Access Code</label>
            <input
              v-model="form.code"
              type="text"
              class="mt-3 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white placeholder:text-white/30 focus:border-white/40 focus:outline-none"
              placeholder="8-character code"
            >
            <p
              v-if="form.errors.code"
              class="mt-2 text-sm text-red-300"
            >
              {{ form.errors.code }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="form.processing || !form.code"
            class="w-full rounded-full bg-white/15 py-3 text-xs font-semibold uppercase tracking-[0.4em] text-white transition hover:bg-white/25 disabled:opacity-40"
          >
            {{ form.processing ? 'Checking…' : 'Unlock Gallery' }}
          </button>
        </form>
      </div>
    </section>
  </ThemeLayout>
</template>

<style scoped>
.cinema-shell {
  min-height: calc(100vh - 6rem);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 4rem 1.5rem 6rem;
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.45), rgba(5, 6, 8, 0.95));
}

.cinema-panel {
  width: min(420px, 100%);
  border-radius: 28px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.65));
  backdrop-filter: blur(14px);
  padding: clamp(2.25rem, 4vw, 3rem);
  color: #f8fafc;
}
</style>
