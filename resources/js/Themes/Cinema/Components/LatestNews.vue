<script setup>
const props = defineProps({ items: { type: Array, required: true } });

const fallbackImage = '/placeholder.svg?height=200&width=300';
const fallbackAvatar = '/placeholder.svg?height=40&width=40';

const imageFor = (item) => item.featuredImage || item.thumbnail || fallbackImage;
const categoryFor = (item) => item.category || 'News';
const dateFor = (item) => item.publishDate || '';
const urlFor = (item) => item.url || (item.slug ? `/news/${item.slug}` : '/news');
const authorFor = (item) => item.author?.name || 'Studio Team';
const avatarFor = (item) => item.author?.avatar || fallbackAvatar;
</script>

<template>
  <section class="cinema-section">
    <div class="cinema-frame">
      <div class="text-center mb-12">
        <p class="text-xs uppercase tracking-[0.5em] text-white/50">Studio Dispatch</p>
        <h3 class="mt-4 text-4xl font-semibold text-white">Latest News</h3>
        <p class="mt-3 text-sm text-white/60 max-w-2xl mx-auto">
          Stay updated with our latest projects and photography insights.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <article
          v-for="item in props.items"
          :key="item.id"
          class="overflow-hidden rounded-[28px] border border-white/10 bg-slate-900/50 shadow-lg shadow-black/30 transition-transform duration-300 hover:-translate-y-1"
        >
          <a
            :href="urlFor(item)"
            class="block"
          > 
            <img
              :src="imageFor(item)"
              :alt="item.title"
              class="w-full h-52 object-cover"
            >
            <div class="p-6">
              <div class="flex items-center text-xs uppercase tracking-[0.35em] text-white/45 mb-4">
                <span>{{ categoryFor(item) }}</span>
                <span
                  v-if="dateFor(item)"
                  class="mx-2"
                >•</span>
                <span v-if="dateFor(item)">{{ dateFor(item) }}</span>
              </div>
              <h4 class="text-2xl font-semibold text-white mb-3 hover:text-white/70 transition-colors">
                {{ item.title }}
              </h4>
              <p class="text-white/65 leading-relaxed mb-5">{{ item.excerpt }}</p>
              <div class="flex items-center">
                <img
                  :src="avatarFor(item)"
                  :alt="authorFor(item)"
                  class="w-9 h-9 rounded-full mr-3 border border-white/20"
                >
                <span class="text-sm text-white/50">{{ authorFor(item) }}</span>
              </div>
            </div>
          </a>
        </article>
      </div>

      <div class="text-center mt-12">
        <a
          href="/news"
          class="inline-block rounded-full bg-white/15 px-6 py-3 text-xs font-semibold uppercase tracking-[0.4em] text-white transition hover:bg-white/25"
        >
          View All Articles
        </a>
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
