<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', subject: '', message: '' });
function submit() { form.post('/contact', { preserveScroll: true }); }
</script>

<template>
  <ThemeLayout>
    <div class="max-w-xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-semibold mb-4">Contact</h1>
      <p class="text-gray-600 mb-6">Send a message and we'll get back to you.</p>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700">Name</label>
          <input v-model="form.name" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Email</label>
          <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300" />
          <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Subject</label>
          <input v-model="form.subject" type="text" class="mt-1 block w-full rounded-md border-gray-300" />
          <p v-if="form.errors.subject" class="text-sm text-red-600 mt-1">{{ form.errors.subject }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Message</label>
          <textarea v-model="form.message" rows="6" class="mt-1 block w-full rounded-md border-gray-300"></textarea>
          <p v-if="form.errors.message" class="text-sm text-red-600 mt-1">{{ form.errors.message }}</p>
        </div>
        <div class="flex items-center justify-end">
          <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
            {{ form.processing ? 'Sending…' : 'Send Message' }}
          </button>
        </div>
      </form>
    </div>
  </ThemeLayout>
  </template>

