<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import LoginModal from './Auth/Login.vue';

const page = usePage();
const user = computed(() => page.props?.auth?.user || null);
const registrationEnabled = computed(() => Boolean(page.props?.features?.registration));
const shouldOpenLogin = computed(() => Boolean(page.props?.ui?.open_login));
const showNews = computed(() => Boolean(page.props?.features?.news));
const avatarUrl = computed(() => {
  const u = user.value || {};
  return u.avatar_url || u.avatar || null;
});
const siteSettings = computed(() => page.props?.site ?? {});
const siteName = computed(() => {
  const config = siteSettings.value || {};
  const candidate = config.general?.site_name ?? config.site_name;
  if (typeof candidate === 'string') {
    const trimmed = candidate.trim();
    if (trimmed.length) return trimmed;
  }
  return 'Cinema';
});

const showLogin = ref(false);
const openMenu = ref(false);

function toggleMenu() { openMenu.value = !openMenu.value; }
function closeMenu() { openMenu.value = false; }
function logout() { router.post('/logout'); }

function initials(name) {
  if (!name) return 'U';
  const parts = String(name).trim().split(/\s+/);
  const first = parts[0]?.[0] || '';
  const last = parts.length > 1 ? parts[parts.length - 1][0] : '';
  return (first + last).toUpperCase() || 'U';
}

function onClickOutside(event) {
  const menu = document.getElementById('cinema-user-menu');
  const btn = document.getElementById('cinema-user-menu-btn');
  if (openMenu.value && menu && !menu.contains(event.target) && btn && !btn.contains(event.target)) {
    closeMenu();
  }
}

function onOpenLogin() { showLogin.value = true; }

onMounted(() => {
  document.addEventListener('click', onClickOutside);
  window.addEventListener('open-login', onOpenLogin);
  if (shouldOpenLogin.value) showLogin.value = true;
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onClickOutside);
  window.removeEventListener('open-login', onOpenLogin);
});
</script>

<template>
  <header class="cinema-nav shadow-lg backdrop-blur">
    <nav class="cinema-nav-inner">
      <div class="cinema-nav-bar">
        <div class="text-xl font-semibold tracking-wide">
          {{ siteName }}
        </div>
        <div class="hidden md:flex items-center gap-8 text-xs uppercase tracking-[0.35em]">
          <a
            href="/"
            class="cinema-link"
          >Home</a>
          <a
            href="/galleries"
            class="cinema-link"
          >Galleries</a>
          <a
            href="/access"
            class="cinema-link"
          >Access</a>
          <a
            v-if="showNews"
            href="/news"
            class="cinema-link"
          >News</a>
          <a
            href="/contact"
            class="cinema-link"
          >Contact</a>

          <template v-if="!user">
            <button
              class="cinema-cta"
              @click="showLogin = true"
            >
              Log In
            </button>
            <a
              v-if="registrationEnabled"
              href="/register"
              class="cinema-outline"
            >Sign Up</a>
          </template>

          <template v-else>
            <div
              id="cinema-user-menu"
              class="relative"
            >
              <button
                id="cinema-user-menu-btn"
                class="cinema-profile"
                @click="toggleMenu"
              >
                <template v-if="avatarUrl">
                  <img
                    :src="avatarUrl"
                    :alt="user.name"
                    class="w-8 h-8 rounded-full object-cover"
                  >
                </template>
                <template v-else>
                  <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-sm font-semibold">
                    {{ initials(user.name) }}
                  </div>
                </template>
                <span class="text-xs font-semibold uppercase tracking-[0.3em]">{{ user.name }}</span>
                <svg
                  class="w-3 h-3 opacity-75"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                ><path
                  fill-rule="evenodd"
                  d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                  clip-rule="evenodd"
                /></svg>
              </button>
              <div
                v-show="openMenu"
                class="absolute right-0 mt-2 w-48 bg-slate-900/95 text-slate-100 rounded-md shadow-lg py-2 z-50 border border-white/10"
              >
                <a
                  href="/dashboard"
                  class="block px-4 py-2 text-sm hover:bg-white/10"
                >Dashboard</a>
                <a
                  href="/profile"
                  class="block px-4 py-2 text-sm hover:bg-white/10"
                >Profile</a>
                <hr class="my-1 border-white/10">
                <button
                  class="w-full text-left block px-4 py-2 text-sm hover:bg-white/10"
                  @click="logout"
                >
                  Log out
                </button>
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
.cinema-nav {
  background: linear-gradient(90deg, rgba(10, 10, 10, 0.92), rgba(15, 23, 42, 0.92));
  color: #f8fafc;
  border-bottom: 1px solid rgba(148, 163, 184, 0.15);
}

.cinema-nav-inner {
  max-width: 1200px;
  margin: 0 auto;
  padding: clamp(1.25rem, 3vw, 2rem) clamp(1.25rem, 5vw, 2.5rem);
}

.cinema-nav-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 2rem;
}

.cinema-link {
  color: rgba(248, 250, 252, 0.8);
  padding: 0.75rem 0.25rem;
  transition: color 200ms ease, opacity 200ms ease;
}

.cinema-link:hover {
  color: #f8fafc;
  opacity: 1;
}

.cinema-cta {
  padding: 0.6rem 1.5rem;
  border-radius: 9999px;
  background: rgba(248, 250, 252, 0.14);
  font-weight: 600;
  letter-spacing: 0.4em;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-cta:hover {
  background: rgba(248, 250, 252, 0.22);
  transform: translateY(-1px);
}

.cinema-outline {
  padding: 0.6rem 1.5rem;
  border-radius: 9999px;
  border: 1px solid rgba(248, 250, 252, 0.35);
  letter-spacing: 0.4em;
  transition: background 200ms ease, transform 200ms ease;
}

.cinema-outline:hover {
  background: rgba(248, 250, 252, 0.12);
  transform: translateY(-1px);
}

.cinema-profile {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.55rem 1.25rem;
  border-radius: 9999px;
  background: rgba(248, 250, 252, 0.12);
  transition: background 200ms ease;
}

.cinema-profile:hover {
  background: rgba(248, 250, 252, 0.2);
}
</style>
