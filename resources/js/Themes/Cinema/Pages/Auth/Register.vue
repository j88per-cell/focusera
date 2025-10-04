<script setup>
import { useForm } from '@inertiajs/vue3';
import ThemeLayout from '../../Layouts/app.layout.vue';

const form = useForm({
  name: '',
  email: '',
});

function submit() {
  form.post('/register');
}
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-panel">
        <header class="text-center mb-8">
          <p class="text-xs uppercase tracking-[0.5em] text-white/50">Onboarding</p>
          <h1 class="mt-3 text-3xl font-semibold text-white">Create Admin Account</h1>
        </header>

        <form
          class="space-y-6"
          @submit.prevent="submit"
        >
          <div>
            <label
              for="name"
              class="block text-xs uppercase tracking-[0.4em] text-white/50"
            >Name</label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              class="mt-3 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white placeholder:text-white/30 focus:border-white/40 focus:outline-none"
            >
            <p
              v-if="form.errors.name"
              class="mt-2 text-sm text-red-300"
            >
              {{ form.errors.name }}
            </p>
          </div>

          <div>
            <label
              for="email"
              class="block text-xs uppercase tracking-[0.4em] text-white/50"
            >Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-3 w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-white placeholder:text-white/30 focus:border-white/40 focus:outline-none"
            >
            <p
              v-if="form.errors.email"
              class="mt-2 text-sm text-red-300"
            >
              {{ form.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            class="w-full rounded-full bg-white/15 py-3 text-xs font-semibold uppercase tracking-[0.4em] text-white transition hover:bg-white/25 disabled:opacity-40"
          >
            {{ form.processing ? 'Submitting…' : 'Register' }}
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
  padding: clamp(2.5rem, 5vw, 3.25rem);
  color: #f8fafc;
}
</style>
