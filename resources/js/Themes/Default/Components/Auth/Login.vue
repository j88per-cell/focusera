<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ open: Boolean })
const emit  = defineEmits(['update:open'])

const close = () => emit('update:open', false)

const form = useForm({
  email: '',
})

const submit = () => {
  form.post('/login', {
    onSuccess: close,
    preserveScroll: true,
  })
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50">
    <!-- overlay -->
    <div class="absolute inset-0 bg-black/50" @click="close" />

    <!-- modal -->
    <div
      class="absolute inset-0 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="login-title"
    >
      <div class="w-full max-w-md rounded-xl shadow-xl bg-white text-gray-900">
        <!-- header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h2 id="login-title" class="text-lg font-semibold">Log in</h2>
          <button @click="close" class="text-gray-500 hover:text-gray-700" aria-label="Close">✕</button>
        </div>

        <!-- info line (new) -->
        <div class="px-6 pt-4 text-sm text-gray-600">
          <span>You'll receive a temporary one-time password shortly to log in.</span>
        </div>

        <!-- form -->
        <form @submit.prevent="submit" class="p-6 space-y-4">
          <div>
            <label for="email" class="block text-sm mb-1">Email</label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="{ 'border-red-500': form.errors.email }"
            />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full rounded-md bg-gray-900 text-white px-4 py-2 font-semibold hover:bg-gray-800 disabled:opacity-50"
          >
            {{ form.processing ? 'Sending code…' : 'Send login link' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
