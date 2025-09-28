<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import { computed } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import FeaturedGalleries from '@/Components/FeaturedGalleries.vue';
  import LatestNews from '@/Components/LatestNews.vue';

  const page = usePage()
  const showFeatured = computed(() => Boolean(page.props?.features?.featured_galleries))
  const showNews = computed(() => Boolean(page.props?.features?.news))
  const featuredGalleries = computed(() => page.props?.featuredGalleries || [])
  const latestNews = computed(() => page.props?.newsPosts || [])
</script>
<template>
  <ThemeLayout>
    <section class="relative overflow-hidden bg-gradient-to-b from-transparent to-black/10">
      <div class="max-w-6xl mx-auto px-6 py-20 text-center">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4">Photography, In Twilight</h1>
        <p class="text-lg md:text-xl text-muted-foreground mb-8">Explore galleries and stories captured after golden hour.</p>
        <div class="flex items-center justify-center gap-4">
          <a href="/galleries" class="px-6 py-3 rounded-lg bg-primary text-primary-foreground hover:opacity-90">Browse Galleries</a>
          <a href="/news" class="px-6 py-3 rounded-lg border border-border hover:bg-muted">Latest News</a>
        </div>
      </div>
    </section>
    
    <section v-if="showFeatured && featuredGalleries.length" class="mt-6">
      <FeaturedGalleries :items="featuredGalleries" @open="id => (window.location.href = `/galleries/${id}`)" />
    </section>

    <LatestNews v-if="showNews && latestNews.length" :items="latestNews" />
  </ThemeLayout>
</template>
