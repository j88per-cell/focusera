<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ open: Boolean });
const emit  = defineEmits(['update:open']);

const close = () => emit('update:open', false);

// step 1 = request OTP, step 2 = verify OTP
const step = ref(1);

// forms
const requestForm = useForm({ email: '' });
const verifyForm  = useForm({ email: '', code: '' });

function requestOtp() {
  requestForm.post('/login/request-otp', {
    onSuccess: () => {
      verifyForm.email = requestForm.email;
      step.value = 2;
    },
    preserveScroll: true,
  });
}

function verifyOtp() {
  verifyForm.post('/login/verify-otp', {
    onSuccess: () => {
      router.visit('/dashboard', { replace: true });
    },
    onFinish: () => {
      close();
    },
    preserveScroll: true,
  });
}
</script>

<template>
  <div
    v-if="open"
    class="fixed inset-0 z-50"
  >
    <!-- overlay -->
    <div
      class="absolute inset-0 bg-black/50"
      @click="close"
    />

    <!-- modal -->
    <div
      class="absolute inset-0 flex items-center justify-center p-4"
      role="dialog"
      aria-modal="true"
      aria-labelledby="login-title"
    >
      <div class="w-full max-w-md rounded-2xl border border-white/10 bg-slate-950/90 text-slate-100 shadow-[0_30px_60px_rgba(0,0,0,0.45)] backdrop-blur-xl">
        <!-- header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
          <h2
            id="login-title"
            class="text-lg font-semibold tracking-[0.3em] uppercase text-white/80"
          >
            Log in
          </h2>
          <button
            class="text-white/40 hover:text-white"
            aria-label="Close"
            @click="close"
          >
            ✕
          </button>
        </div>

        <!-- info line -->
        <div class="px-6 pt-4 text-xs uppercase tracking-[0.3em] text-white/50">
          <span v-if="step === 1">You'll receive a one-time login code by email.</span>
          <span v-else>Enter the 6-digit code we sent to your email.</span>
        </div>

        <!-- Step 1: request OTP -->
        <form
          v-if="step === 1"
          class="p-6 space-y-5"
          @submit.prevent="requestOtp"
        >
          <div>
            <label
              for="email"
              class="block text-xs uppercase tracking-[0.3em] text-white/50 mb-2"
            >Email</label>
            <input
              id="email"
              v-model="requestForm.email"
              type="email"
              autocomplete="email"
              class="w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/30 focus:outline-none focus:border-white/40"
              :class="{ 'border-red-400': requestForm.errors.email }"
            >
            <p
              v-if="requestForm.errors.email"
              class="text-sm text-red-300 mt-2"
            >
              {{ requestForm.errors.email }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="requestForm.processing"
            class="w-full rounded-full bg-white/15 text-xs font-semibold uppercase tracking-[0.4em] text-white px-4 py-3 transition hover:bg-white/25 disabled:opacity-40"
          >
            {{ requestForm.processing ? 'Sending…' : 'Send code' }}
          </button>
        </form>

        <!-- Step 2: verify OTP -->
        <form
          v-else
          class="p-6 space-y-5"
          @submit.prevent="verifyOtp"
        >
          <div>
            <label class="block text-xs uppercase tracking-[0.3em] text-white/50 mb-2">Email</label>
            <input
              v-model="verifyForm.email"
              type="email"
              readonly
              class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/70"
            >
          </div>

          <div>
            <label
              for="code"
              class="block text-xs uppercase tracking-[0.3em] text-white/50 mb-2"
            >Code</label>
            <input
              id="code"
              v-model="verifyForm.code"
              type="text"
              maxlength="6"
              class="w-full rounded-xl border border-white/20 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-white/30 focus:outline-none focus:border-white/40"
              :class="{ 'border-red-400': verifyForm.errors.code }"
            >
            <p
              v-if="verifyForm.errors.code"
              class="text-sm text-red-300 mt-2"
            >
              {{ verifyForm.errors.code }}
            </p>
          </div>

          <button
            type="submit"
            :disabled="verifyForm.processing"
            class="w-full rounded-full bg-white/15 text-xs font-semibold uppercase tracking-[0.4em] text-white px-4 py-3 transition hover:bg-white/25 disabled:opacity-40"
          >
            {{ verifyForm.processing ? 'Verifying…' : 'Log in' }}
          </button>

          <!-- resend link -->
          <p class="text-sm text-white/60 mt-4 text-center">
            Didn’t get the code?
            <button
              type="button"
              class="text-white hover:underline"
              @click="step = 1"
            >
              Resend
            </button>
          </p>
        </form>
      </div>
    </div>
  </div>
</template>
