<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({ items: { type: Array, required: true } });
const page = usePage();
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage');

function normalizeSrc(src) {
  if (!src) return '';
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src;
  if (src.startsWith('storage/')) return `/${src}`;
  return joinPublicBase(src);
}

function joinPublicBase(path) {
  const base = publicBaseUrl.value || '';
  if (!base) return '/' + path.replace(/^\/+/, '');
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}

function galleryHref(gallery) {
  if (!gallery) return '/galleries';

  const directUrl = typeof gallery.url === 'string' && gallery.url.trim().length ? gallery.url.trim() : null;
  if (directUrl) return directUrl;

  const slug = typeof gallery.slug === 'string' && gallery.slug.trim().length ? gallery.slug.trim() : null;
  if (slug) return `/galleries/${slug}`;

  const id = gallery.id ?? gallery.gallery_id ?? null;
  if (id) return `/galleries/${id}`;

  return '/galleries';
}
</script>

<template>
  <section
    id="galleries"
    class="cinema-section"
  >
    <div class="cinema-frame">
      <div class="text-center mb-12">
        <p class="text-xs uppercase tracking-[0.5em] text-white/50">Highlights</p>
        <h3 class="mt-4 text-4xl font-semibold text-white">Featured Galleries</h3>
        <p class="mt-3 text-white/60 text-sm max-w-2xl mx-auto">
          Discover our most captivating photography collections.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <Link
          v-for="g in props.items"
          :key="g.id ?? g.slug ?? g.title"
          :href="galleryHref(g)"
          class="group block focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-white/60 focus-visible:ring-offset-slate-900"
        >
          <article class="relative overflow-hidden rounded-[28px] border border-white/10 bg-slate-900/50 shadow-xl shadow-black/30 transition-transform duration-300 hover:-translate-y-1">
            <div class="relative overflow-hidden h-64 flex items-center justify-center">
              <template v-if="normalizeSrc(g.thumbnail)">
                <img
                  :src="normalizeSrc(g.thumbnail)"
                  :alt="g.title"
                  class="w-full h-full object-cover transition-transform duration-[600ms] group-hover:scale-105"
                >
              </template>
              <template v-else>
                <svg
                  class="w-16 h-16 text-white/50"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="1.5"
                    d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"
                  />
                </svg>
              </template>
              <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end">
                <div class="w-full p-6">
                  <span class="text-xs uppercase tracking-[0.4em] text-white/60">View Gallery</span>
                </div>
              </div>
            </div>
            <div class="p-6">
              <h4 class="text-2xl font-semibold text-white mb-2">
                {{ g.title }}
              </h4>
              <p class="text-white/60 leading-relaxed min-h-[3rem]">
                {{ g.description }}
              </p>
              <div class="mt-5 flex items-center text-xs uppercase tracking-[0.35em] text-white/45">
                <span>{{ g.photoCount }} photos</span>
                <span class="mx-2">•</span>
                <span>{{ g.date }}</span>
              </div>
            </div>
          </article>
        </Link>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cinema-section {
  padding: 4rem 1.5rem 6rem;
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.35), rgba(5, 6, 8, 0.95));
}

.cinema-frame {
  width: min(1100px, 100%);
  margin: 0 auto;
}
</style>
