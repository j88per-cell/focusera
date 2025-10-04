<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SiteTopNav from '../Components/SiteTopNav.vue';

const page = usePage();
const currentYear = new Date().getFullYear();
const siteSettings = computed(() => page.props?.site ?? {});
const siteName = computed(() => {
  const config = siteSettings.value || {};
  const candidate = config.general?.site_name ?? config.site_name;
  if (typeof candidate === 'string') {
    const trimmed = candidate.trim();
    if (trimmed.length) return trimmed;
  }
  return 'Focusera';
});
</script>

<template>
  <div class="min-h-screen bg-cinema-bg text-cinema-fg flex flex-col">
    <SiteTopNav />

    <main class="flex-1">
      <slot />
    </main>

    <footer class="cinema-footer py-3 text-xs tracking-[0.35em] uppercase text-white/60">
      © {{ currentYear }} {{ siteName }}
    </footer>
  </div>
</template>

<style scoped>
.bg-cinema-bg {
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.6), rgba(10, 10, 10, 0.95));
  min-height: 100vh;
}

.text-cinema-fg {
  color: #f8fafc;
}

.cinema-footer {
  border-top: 1px solid rgba(148, 163, 184, 0.2);
  text-align: center;
  letter-spacing: 0.35em;
}
</style>
