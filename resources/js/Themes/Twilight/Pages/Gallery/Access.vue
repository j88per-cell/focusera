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
      <h1 class="text-2xl font-semibold mb-4">
        Private Gallery Access
      </h1>
      <p class="text-sm text-muted-foreground mb-6">
        Enter your access code to open the gallery.
      </p>
      <form
        class="space-y-4"
        @submit.prevent="submit"
      >
        <div>
          <label class="block text-sm font-medium">Access Code</label>
          <input
            v-model="form.code"
            type="text"
            class="mt-1 block w-full rounded-md border border-border bg-background text-foreground"
            placeholder="e.g. 8-character code"
          >
          <p
            v-if="form.errors.code"
            class="text-sm text-red-400 mt-1"
          >
            {{ form.errors.code }}
          </p>
        </div>
        <div class="flex items-center justify-end">
          <button
            type="submit"
            :disabled="form.processing || !form.code"
            class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90 disabled:opacity-50"
          >
            {{ form.processing ? 'Checking…' : 'Unlock' }}
          </button>
        </div>
      </form>
    </div>
  </ThemeLayout>
</template>

