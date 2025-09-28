<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', subject: '', message: '' });
function submit() { form.post('/contact', { preserveScroll: true }); }
</script>

<template>
  <ThemeLayout>
    <div class="max-w-xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-semibold mb-4">
        Contact
      </h1>
      <p class="text-muted-foreground mb-6">
        Send a message and we'll get back to you.
      </p>
      <form
        class="space-y-4"
        @submit.prevent="submit"
      >
        <div>
          <label class="block text-sm font-medium">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="mt-1 block w-full rounded-md border border-border bg-background text-foreground"
          >
          <p
            v-if="form.errors.name"
            class="text-sm text-red-400 mt-1"
          >
            {{ form.errors.name }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="mt-1 block w-full rounded-md border border-border bg-background text-foreground"
          >
          <p
            v-if="form.errors.email"
            class="text-sm text-red-400 mt-1"
          >
            {{ form.errors.email }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium">Subject</label>
          <input
            v-model="form.subject"
            type="text"
            class="mt-1 block w-full rounded-md border border-border bg-background text-foreground"
          >
          <p
            v-if="form.errors.subject"
            class="text-sm text-red-400 mt-1"
          >
            {{ form.errors.subject }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium">Message</label>
          <textarea
            v-model="form.message"
            rows="6"
            class="mt-1 block w-full rounded-md border border-border bg-background text-foreground"
          />
          <p
            v-if="form.errors.message"
            class="text-sm text-red-400 mt-1"
          >
            {{ form.errors.message }}
          </p>
        </div>
        <div class="flex items-center justify-end">
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-primary text-primary-foreground rounded-md hover:opacity-90"
          >
            {{ form.processing ? 'Sending…' : 'Send Message' }}
          </button>
        </div>
      </form>
    </div>
  </ThemeLayout>
</template>

