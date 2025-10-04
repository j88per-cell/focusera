<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-panel text-center">
        <p class="text-xs uppercase tracking-[0.5em] text-white/50">Almost there</p>
        <h1 class="mt-4 text-3xl font-semibold text-white">Verify your email</h1>
        <p class="mt-6 text-sm text-white/65">
          We’ve sent a verification link to your inbox. Follow it to finish setting up your account.
        </p>
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
