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
    <div class="max-w-md mx-auto mt-12">
      <h1 class="text-2xl font-bold mb-6">
        Create Admin Account
      </h1>

      <form
        class="space-y-4"
        @submit.prevent="submit"
      >
        <div>
          <label
            for="name"
            class="block text-sm font-medium"
          >Name</label>
          <input
            id="name"
            v-model="form.name"
            type="text"
            class="w-full rounded border border-border bg-background text-foreground"
          >
          <div
            v-if="form.errors.name"
            class="text-red-400 text-sm"
          >
            {{ form.errors.name }}
          </div>
        </div>

        <div>
          <label
            for="email"
            class="block text-sm font-medium"
          >Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="w-full rounded border border-border bg-background text-foreground"
          >
          <div
            v-if="form.errors.email"
            class="text-red-400 text-sm"
          >
            {{ form.errors.email }}
          </div>
        </div>

        <button
          type="submit"
          class="bg-primary hover:opacity-90 text-primary-foreground font-medium px-4 py-2 rounded"
        >
          Register
        </button>
      </form>
    </div>
  </ThemeLayout>
</template>

