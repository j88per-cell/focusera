<script setup>
import { ref } from 'vue'
import LoginModal from '../Components/Auth/Login.vue'

const showLogin = ref(false)
</script>

<template>
  <div class="min-h-screen bg-background text-foreground">
    <header class="bg-primary text-primary-foreground shadow-sm">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <div class="text-xl font-bold">PhotoStudio</div>
          <div class="hidden md:flex items-center gap-4">
            <a href="/" class="hover:text-accent px-3 py-2 text-sm">Home</a>
            <a href="/galleries" class="hover:text-accent px-3 py-2 text-sm">Galleries</a>
            <a href="/news" class="hover:text-accent px-3 py-2 text-sm">News</a>
            <a href="/contact" class="hover:text-accent px-3 py-2 text-sm">Contact</a>

            <button class="px-3 py-2 rounded-md text-sm bg-accent text-white hover:bg-accent/90"
                    @click="showLogin = true">
              Log in
            </button>
            <a href="/register" class="px-3 py-2 rounded-md text-sm border border-accent hover:bg-accent/10">
              Sign up
            </a>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <slot />
    </main>

    <LoginModal v-model:open="showLogin" />
  </div>
</template>


<style scoped>
.bg-background { background-color: #ffffff; }
.text-foreground { color: #1f2937; }
.bg-primary { background-color: #1f2937; }
.text-primary-foreground { color: #ffffff; }
.bg-accent { background-color: #8b5cf6; }
.text-accent { color: #8b5cf6; }
.text-accent-foreground { color: #ffffff; }
.bg-muted { background-color: #f1f5f9; }
.text-muted-foreground { color: #6b7280; }
.bg-card { background-color: #f1f5f9; }
.text-card-foreground { color: #1f2937; }
</style>
