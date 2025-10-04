<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';

const props = defineProps({ isAuthenticated: { type: Boolean, default: false } });
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-panel text-center">
        <p class="text-xs uppercase tracking-[0.5em] text-white/50">Welcome back</p>
        <h1 class="mt-4 text-3xl font-semibold text-white">Email Verified</h1>
        <p class="mt-6 text-sm text-white/65">
          Your email address is confirmed. You can continue to explore the studio experience.
        </p>

        <div class="mt-10 flex items-center justify-center gap-4">
          <button
            type="button"
            class="cinema-cta"
            @click="window.dispatchEvent(new CustomEvent('open-login'))"
          >
            Log In
          </button>
          <a
            href="/"
            class="cinema-outline"
          >Home</a>
        </div>
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
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.88), rgba(15, 23, 42, 0.6));
  backdrop-filter: blur(14px);
  padding: clamp(2.5rem, 5vw, 3.25rem);
  color: #f8fafc;
}

.cinema-cta {
  padding: 0.75rem 2rem;
  border-radius: 9999px;
  background: rgba(248, 250, 252, 0.14);
  font-size: 0.75rem;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  color: #f8fafc;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-cta:hover {
  background: rgba(248, 250, 252, 0.22);
  transform: translateY(-1px);
}

.cinema-outline {
  padding: 0.75rem 2rem;
  border-radius: 9999px;
  border: 1px solid rgba(248, 250, 252, 0.35);
  font-size: 0.75rem;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  color: #f8fafc;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-outline:hover {
  background: rgba(248, 250, 252, 0.12);
  transform: translateY(-1px);
}
</style>
