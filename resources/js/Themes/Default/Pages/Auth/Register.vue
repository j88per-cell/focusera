<script setup>
import { useForm } from '@inertiajs/vue3'
import ThemeLayout from '@/Layouts/app.layout.vue';

const form = useForm({
  name: '',
  email: '',
})

function submit() {
  form.post('/register')
}
</script>

<template>
    <ThemeLayout>
        <div class="max-w-md mx-auto mt-12">
            <h1 class="text-2xl font-bold mb-6">Create Admin Account</h1>

            <form @submit.prevent="submit" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input v-model="form.name" id="name" type="text"
                class="w-full rounded border-gray-300" />
                <div v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</div>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input v-model="form.email" id="email" type="email"
                class="w-full rounded border-gray-300" />
                <div v-if="form.errors.email" class="text-red-600 text-sm">{{ form.errors.email }}</div>
            </div>

            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded">
                Register
            </button>
            </form>
        </div>
    </ThemeLayout>
</template>
