<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', subject: '', message: '' });
function submit() { form.post('/contact', { preserveScroll: true }); }
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-frame">
        <header class="text-center mb-10">
          <p class="text-xs uppercase tracking-[0.5em] text-white/50">Say Hello</p>
          <h1 class="mt-3 text-4xl font-semibold text-white">Contact</h1>
          <p class="mt-4 text-sm text-white/60">
            Share the details of your project or event and we’ll get back to you.
          </p>
        </header>

        <form
          class="cinema-form"
          @submit.prevent="submit"
        >
          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <label class="cinema-label">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="cinema-input"
              >
              <p
                v-if="form.errors.name"
                class="cinema-error"
              >
                {{ form.errors.name }}
              </p>
            </div>
            <div>
              <label class="cinema-label">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="cinema-input"
              >
              <p
                v-if="form.errors.email"
                class="cinema-error"
              >
                {{ form.errors.email }}
              </p>
            </div>
          </div>

          <div>
            <label class="cinema-label">Subject</label>
            <input
              v-model="form.subject"
              type="text"
              class="cinema-input"
            >
            <p
              v-if="form.errors.subject"
              class="cinema-error"
            >
              {{ form.errors.subject }}
            </p>
          </div>

          <div>
            <label class="cinema-label">Message</label>
            <textarea
              v-model="form.message"
              rows="6"
              class="cinema-input h-40 resize-none"
            />
            <p
              v-if="form.errors.message"
              class="cinema-error"
            >
              {{ form.errors.message }}
            </p>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="form.processing"
              class="cinema-submit"
            >
              {{ form.processing ? 'Sending…' : 'Send Message' }}
            </button>
          </div>
        </form>
      </div>
    </section>
  </ThemeLayout>
</template>

<style scoped>
.cinema-shell {
  min-height: calc(100vh - 6rem);
  padding: 4rem 1.5rem 6rem;
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.45), rgba(5, 6, 8, 0.95));
  display: flex;
  justify-content: center;
}

.cinema-frame {
  width: min(900px, 100%);
}

.cinema-form {
  margin-top: 3rem;
  padding: clamp(2.5rem, 4vw, 3.5rem);
  border-radius: 32px;
  border: 1px solid rgba(148, 163, 184, 0.25);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.55));
  backdrop-filter: blur(14px);
  display: flex;
  flex-direction: column;
  gap: 1.75rem;
  color: #f8fafc;
}

.cinema-label {
  display: block;
  margin-bottom: 0.75rem;
  font-size: 0.75rem;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  color: rgba(248, 250, 252, 0.55);
}

.cinema-input {
  width: 100%;
  border-radius: 18px;
  border: 1px solid rgba(248, 250, 252, 0.25);
  background: rgba(15, 23, 42, 0.6);
  color: #f8fafc;
  padding: 1rem 1.25rem;
  outline: none;
  transition: border 200ms ease, background 200ms ease;
}

.cinema-input:focus {
  border-color: rgba(248, 250, 252, 0.5);
  background: rgba(15, 23, 42, 0.75);
}

.cinema-error {
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #fca5a5;
}

.cinema-submit {
  padding: 0.85rem 2.75rem;
  border-radius: 9999px;
  background: rgba(248, 250, 252, 0.14);
  color: #f8fafc;
  font-size: 0.75rem;
  letter-spacing: 0.4em;
  text-transform: uppercase;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-submit:hover {
  background: rgba(248, 250, 252, 0.24);
  transform: translateY(-1px);
}

.cinema-submit:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}
</style>
