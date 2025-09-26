<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import LoginModal from './Auth/Login.vue'

const page = usePage()
const user = computed(() => page.props?.auth?.user || null)
const registrationEnabled = computed(() => Boolean(page.props?.features?.registration))
const shouldOpenLogin = computed(() => Boolean(page.props?.ui?.open_login))
const showNews = computed(() => Boolean(page.props?.features?.news))
const avatarUrl = computed(() => {
  const u = user.value || {}
  return u.avatar_url || u.avatar || null
})

const showLogin = ref(false)
const openMenu = ref(false)

function toggleMenu() { openMenu.value = !openMenu.value }
function closeMenu() { openMenu.value = false }
function logout() { router.post('/logout') }

function initials(name) {
  if (!name) return 'U'
  const parts = String(name).trim().split(/\s+/)
  const first = parts[0]?.[0] || ''
  const last = parts.length > 1 ? parts[parts.length-1][0] : ''
  return (first + last).toUpperCase() || 'U'
}

function onClickOutside(e) {
  const menu = document.getElementById('user-menu')
  const btn = document.getElementById('user-menu-btn')
  if (openMenu.value && menu && !menu.contains(e.target) && btn && !btn.contains(e.target)) {
    closeMenu()
  }
}

function onOpenLogin() { showLogin.value = true }

onMounted(() => {
  document.addEventListener('click', onClickOutside)
  window.addEventListener('open-login', onOpenLogin)
  if (shouldOpenLogin.value) showLogin.value = true
})
onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside)
  window.removeEventListener('open-login', onOpenLogin)
})
</script>

<template>
  <header class="bg-primary text-primary-foreground shadow-sm">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-16">
        <div class="text-xl font-bold">PhotoStudio</div>
        <div class="hidden md:flex items-center gap-4">
          <a href="/" class="hover:text-accent px-3 py-2 text-sm">Home</a>
          <a href="/galleries" class="hover:text-accent px-3 py-2 text-sm">Galleries</a>
          <a href="/access" class="hover:text-accent px-3 py-2 text-sm">Private Access</a>
          <a v-if="showNews" href="/news" class="hover:text-accent px-3 py-2 text-sm">News</a>
          <a href="/contact" class="hover:text-accent px-3 py-2 text-sm">Contact</a>

          <template v-if="!user">
            <button class="px-3 py-2 rounded-md text-sm bg-accent text-white hover:bg-accent/90"
                    @click="showLogin = true">
              Log in
            </button>
            <a v-if="registrationEnabled" href="/register" class="px-3 py-2 rounded-md text-sm border border-accent hover:bg-accent/10">Sign up</a>
          </template>

          <template v-else>
            <div class="relative" id="user-menu">
              <button id="user-menu-btn" @click="toggleMenu" class="flex items-center gap-2 px-2 py-1 rounded hover:bg-white/10">
                <template v-if="avatarUrl">
                  <img :src="avatarUrl" :alt="user.name" class="w-8 h-8 rounded-full object-cover" />
                </template>
                <template v-else>
                  <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-semibold">
                    {{ initials(user.name) }}
                  </div>
                </template>
                <span class="text-sm">{{ user.name }}</span>
                <svg class="w-4 h-4 opacity-80" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z" clip-rule="evenodd"/></svg>
              </button>
              <div v-show="openMenu" class="absolute right-0 mt-2 w-48 bg-white text-gray-800 rounded shadow-lg py-2 z-50">
                <a href="/dashboard" class="block px-4 py-2 text-sm hover:bg-gray-100">Dashboard</a>
                <a href="/profile" class="block px-4 py-2 text-sm hover:bg-gray-100">Profile</a>
                <hr class="my-1" />
                <button @click="logout" class="w-full text-left block px-4 py-2 text-sm hover:bg-gray-100">Log out</button>
              </div>
            </div>
          </template>
        </div>
      </div>
    </nav>
    <LoginModal v-model:open="showLogin" />
  </header>
</template>

<style scoped>
.bg-primary { background-color: #1f2937; }
.text-primary-foreground { color: #ffffff; }
.bg-accent { background-color: #8b5cf6; }
.text-accent { color: #8b5cf6; }
</style>
