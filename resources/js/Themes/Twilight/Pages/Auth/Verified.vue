<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';

const props = defineProps({ isAuthenticated: { type: Boolean, default: false } });
</script>

<template>
  <ThemeLayout>
    <div class="max-w-md mx-auto py-12 text-center">
      <h1 class="text-2xl font-bold mb-3">
        Email Verified
      </h1>
      <p class="text-muted-foreground mb-6">
        Your email address has been confirmed.
      </p>

      <div class="flex items-center justify-center gap-3">
        <button
          type="button"
          class="px-4 py-2 rounded-md bg-primary text-primary-foreground hover:opacity-90"
          @click="window.dispatchEvent(new CustomEvent('open-login'))"
        >
          Log in
        </button>
        <a
          href="/"
          class="px-4 py-2 rounded-md border border-border hover:bg-muted"
        >Home</a>
      </div>
    </div>
  </ThemeLayout>
</template>
