<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const props = defineProps({ open: Boolean })
const emit  = defineEmits(['update:open'])

const close = () => emit('update:open', false)

// step 1 = request OTP, step 2 = verify OTP
const step = ref(1)

// forms
const requestForm = useForm({ email: '' })
const verifyForm  = useForm({ email: '', code: '' })

function requestOtp() {
  requestForm.post('/login/request-otp', {
    onSuccess: () => {
      verifyForm.email = requestForm.email
      step.value = 2
    },
    preserveScroll: true,
  })
}

function verifyOtp() {
  verifyForm.post('/login/verify-otp', {
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
    <div class="absolute inset-0 flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="login-title">
      <div class="w-full max-w-md rounded-xl shadow-xl bg-white text-gray-900">
        
        <!-- header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
          <h2 id="login-title" class="text-lg font-semibold">Log in</h2>
          <button @click="close" class="text-gray-500 hover:text-gray-700" aria-label="Close">✕</button>
        </div>

        <!-- info line -->
        <div class="px-6 pt-4 text-sm text-gray-600">
          <span v-if="step === 1">You'll receive a one-time login code by email.</span>
          <span v-else>Enter the 6-digit code we sent to your email.</span>
        </div>

        <!-- Step 1: request OTP -->
        <form v-if="step === 1" @submit.prevent="requestOtp" class="p-6 space-y-4">
          <div>
            <label for="email" class="block text-sm mb-1">Email</label>
            <input
              id="email"
              v-model="requestForm.email"
              type="email"
              autocomplete="email"
              class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="{ 'border-red-500': requestForm.errors.email }"
            />
            <p v-if="requestForm.errors.email" class="text-sm text-red-600 mt-1">{{ requestForm.errors.email }}</p>
          </div>

          <button type="submit" :disabled="requestForm.processing"
            class="w-full rounded-md bg-gray-900 text-white px-4 py-2 font-semibold hover:bg-gray-800 disabled:opacity-50">
            {{ requestForm.processing ? 'Sending…' : 'Send code' }}
          </button>
        </form>

        <!-- Step 2: verify OTP -->
        <form v-else @submit.prevent="verifyOtp" class="p-6 space-y-4">
          <div>
            <label class="block text-sm mb-1">Email</label>
            <input v-model="verifyForm.email" type="email" readonly
              class="w-full rounded-md border bg-gray-100 px-3 py-2 text-sm" />
          </div>

          <div>
            <label for="code" class="block text-sm mb-1">Code</label>
            <input
              id="code"
              v-model="verifyForm.code"
              type="text"
              maxlength="6"
              class="w-full rounded-md border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              :class="{ 'border-red-500': verifyForm.errors.code }"
            />
            <p v-if="verifyForm.errors.code" class="text-sm text-red-600 mt-1">{{ verifyForm.errors.code }}</p>
          </div>

          <button type="submit" :disabled="verifyForm.processing"
            class="w-full rounded-md bg-gray-900 text-white px-4 py-2 font-semibold hover:bg-gray-800 disabled:opacity-50">
            {{ verifyForm.processing ? 'Verifying…' : 'Log in' }}
          </button>

          <!-- resend link -->
          <p class="text-sm text-gray-600 mt-3 text-center">
            Didn’t get the code?
            <button type="button" class="text-indigo-600 hover:underline" @click="step = 1">
              Resend
            </button>
          </p>
        </form>

      </div>
    </div>
  </div>
</template>
