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
    <div class="max-w-md mx-auto py-16 px-4">
      <h1 class="text-2xl font-semibold mb-4">Private Gallery Access</h1>
      <p class="text-sm text-gray-600 mb-6">
        Enter your access code to open the gallery.
      </p>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Access Code</label>
          <input v-model="form.code" type="text" class="mt-1 block w-full rounded-md border-gray-300" placeholder="e.g. 8-character code" />
          <p v-if="form.errors.code" class="text-sm text-red-600 mt-1">{{ form.errors.code }}</p>
        </div>
        <div class="flex items-center justify-end">
          <button type="submit" :disabled="form.processing || !form.code" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
            {{ form.processing ? 'Checking…' : 'Unlock' }}
          </button>
        </div>
      </form>
    </div>
  </ThemeLayout>
  </template>

