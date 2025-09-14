<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import HeroRotator from '../../Components/HeroRotator.vue';
  import FeaturedGalleries from '../../Components/FeaturedGalleries.vue';
  import LatestNews from '../../Components/LatestNews.vue';
  import { ref } from 'vue';

  const heroImages = ref([
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Wedding Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Portrait Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Landscape Photography' },
  ])

  const featuredGalleries = ref([
    { id: 1, title: 'Summer Weddings 2024', description: 'Beautiful outdoor ceremonies captured in golden hour light',
      thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 45, date: 'June 2024' },
    { id: 2, title: 'Urban Portraits', description: 'Contemporary portrait sessions in city environments',
      thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 32, date: 'May 2024' },
    { id: 3, title: 'Nature & Landscapes', description: 'Breathtaking natural scenes and scenic vistas',
      thumbnail: '/placeholder.svg?height=300&width=400', photoCount: 28, date: 'April 2024' },
  ])

  const latestNews = ref([
    { id: 1, title: 'New Studio Opening Downtown', excerpt: "We're excited to announce...",
      featuredImage: '/placeholder.svg?height=200&width=300', category: 'Studio News', publishDate: 'Dec 15, 2024',
      author: { name: 'Sarah Johnson', avatar: '/placeholder.svg?height=40&width=40' } },
    { id: 2, title: '5 Tips for Perfect Wedding Photos', excerpt: 'Learn the essential techniques...',
      featuredImage: '/placeholder.svg?height=200&width=300', category: 'Photography Tips', publishDate: 'Dec 12, 2024',
      author: { name: 'Mike Chen', avatar: '/placeholder.svg?height=40&width=40' } },
    { id: 3, title: 'Award-Winning Portrait Series', excerpt: 'Our recent portrait series...',
      featuredImage: '/placeholder.svg?height=200&width=300', category: 'Awards', publishDate: 'Dec 10, 2024',
      author: { name: 'Sarah Johnson', avatar: '/placeholder.svg?height=40&width=40' } },
  ])

  const openGallery = (id) => console.log('open gallery', id);
  const openArticle = (id) => console.log('open article', id);
</script>
<template>
  <ThemeLayout>
    <HeroRotator :images="heroImages" />
    <FeaturedGalleries :items="featuredGalleries" @open="openGallery" />
    <LatestNews :items="latestNews" @open="openArticle" />
  </ThemeLayout>
</template>
