<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import HeroRotator from '../../Components/HeroRotator.vue';
  import FeaturedGalleries from '../../Components/FeaturedGalleries.vue';
  import LatestNews from '../../Components/LatestNews.vue';
  import { computed, ref } from 'vue';
  import { usePage } from '@inertiajs/vue3'

  const page = usePage()

  const heroImages = ref([
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Wedding Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Portrait Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Landscape Photography' },
  ])

  const featuredGalleries = computed(() => page.props?.featuredGalleries || [])
  const latestNews = computed(() => page.props?.newsPosts || [])

  const openGallery = (id) => {
    if (id) window.location.href = `/galleries/${id}`
  }
</script>
<template>
  <ThemeLayout>
    <HeroRotator :images="heroImages" />
    <FeaturedGalleries v-if="featuredGalleries.length" :items="featuredGalleries" @open="openGallery" />
    <LatestNews v-if="latestNews.length" :items="latestNews" />
  </ThemeLayout>
</template>
