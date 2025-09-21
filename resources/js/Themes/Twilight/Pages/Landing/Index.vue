<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import { ref, computed } from 'vue';
  import { usePage } from '@inertiajs/vue3'
  import FeaturedGalleries from '@/Components/FeaturedGalleries.vue';

  const page = usePage()
  const showFeatured = computed(() => Boolean(page.props?.features?.featured_galleries))
  const showNews = computed(() => Boolean(page.props?.features?.news))

  // Lightweight mock list for Featured Galleries (kept per request)
  const featuredGalleries = ref([
    { id: 1, title: 'Summer Weddings', description: 'Golden hour ceremonies', thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 45, date: 'Jun 2024' },
    { id: 2, title: 'Urban Portraits', description: 'City vibes', thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 32, date: 'May 2024' },
    { id: 3, title: 'Nature & Landscapes', description: 'Scenic vistas', thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 28, date: 'Apr 2024' },
  ])
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
    
    <section v-if="showFeatured" class="mt-6">
      <FeaturedGalleries :items="featuredGalleries" @open="id => (window.location.href = `/galleries/${id}`)" />
    </section>

    <section v-if="showNews" class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h3 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Latest News</h3>
        <p class="text-muted-foreground mb-6">Read updates and articles from the studio</p>
        <a href="/news" class="inline-block px-6 py-3 rounded-lg bg-primary text-primary-foreground hover:opacity-90">View Articles</a>
      </div>
    </section>
  </ThemeLayout>
</template>
