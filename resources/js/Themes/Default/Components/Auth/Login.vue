<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ open: Boolean })
const emit  = defineEmits(['update:open'])

const emailEl = ref(null)

const close = () => emit('update:open', false)

const esc = (e) => { if (e.key === 'Escape') close() }

onMounted(() => {
  window.addEventListener('keydown', esc)
  // focus email when opened
  if (props.open) setTimeout(() => emailEl.value?.focus(), 0)
})
onUnmounted(() => window.removeEventListener('keydown', esc))

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onSuccess: close,
    onFinish: () => form.reset('password'),
    preserveScroll: true,
  })
}
</script>

<template>
  <div v-if="open" class="fixed inset-0 z-50">
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/50" @click="close" />

    <!-- Modal -->
    <div
      class="absolute inset-0 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="login-title"
    >
      <div class="w-full max-w-md rounded-xl shadow-xl bg-white text-gray-900">
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h2 id="login-title" class="text-lg font-semibold">Log in</h2>
          <button class="text-gray-500 hover:text-gray-700" @click="close" aria-label="Close">✕</button>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-4">
          <div>
            <label class="block text-sm mb-1" for="email">Email</label>
            <input
              id="email"
              ref="emailEl"
              v-model="form.email"
              type="email"
              autocomplete="email"
              class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="{'border-red-500': form.errors.email}"
            />
            <p v-if="form.errors.email" class="text-red-600 text-sm mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm mb-1" for="password">Password</label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              autocomplete="current-password"
              class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="{'border-red-500': form.errors.password}"
            />
            <p v-if="form.errors.password" class="text-red-600 text-sm mt-1">{{ form.errors.password }}</p>
          </div>

          <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm">
              <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
              Remember me
            </label>
            <a href="/forgot-password" class="text-sm text-indigo-600 hover:underline">Forgot?</a>
          </div>

          <button
            :disabled="form.processing"
            class="w-full rounded-md bg-gray-900 text-white px-4 py-2 font-semibold hover:bg-gray-800 disabled:opacity-50"
          >
            {{ form.processing ? 'Signing in…' : 'Sign in' }}
          </button>

          <p class="text-center text-sm text-gray-600">
            New here?
            <a href="/register" class="text-indigo-600 hover:underline" @click="close">Create an account</a>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>
