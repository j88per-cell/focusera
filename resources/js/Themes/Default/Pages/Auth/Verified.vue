<script setup>
import ThemeLayout from '@/Layouts/app.layout.vue';

const props = defineProps({ isAuthenticated: { type: Boolean, default: false } });
</script>

<template>
  <ThemeLayout>
    <div class="max-w-md mx-auto py-12 text-center">
      <h1 class="text-2xl font-bold mb-3">Email Verified</h1>
      <p class="text-gray-600 mb-6">Your email address has been confirmed.</p>

      <div class="flex items-center justify-center gap-3">
        <a href="/login" class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Log in</a>
        <a href="/" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-50">Home</a>
      </div>
    </div>
  </ThemeLayout>
  
</template>

