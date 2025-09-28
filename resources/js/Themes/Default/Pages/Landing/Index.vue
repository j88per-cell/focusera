<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import HeroRotator from '../../Components/HeroRotator.vue';
  import FeaturedGalleries from '../../Components/FeaturedGalleries.vue';
  import LatestNews from '../../Components/LatestNews.vue';
  import { computed, onMounted } from 'vue';
  import { usePage } from '@inertiajs/vue3';

  const page = usePage();

  const siteSettings = computed(() => page.props?.site ?? {});

  const defaultHeroImages = [
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Wedding Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Portrait Photography' },
    { src: '/placeholder.svg?height=500&width=1200', alt: 'Landscape Photography' },
  ];

  const landingSettings = computed(() => {
    const site = siteSettings.value;
    // Prefer dedicated landing bucket, fall back to general/legacy keys
    const candidates = [site.landing, site.landing_page, site.landingPage, site.general];
    for (const candidate of candidates) {
      if (candidate && typeof candidate === 'object') return candidate;
    }
	//const siteName = siteSettings.general.site_name;
    return {};
  });

  const heroTitle = computed(() => {
    const value = landingSettings.value?.hero_title
      ?? landingSettings.value?.landing_page_title
      ?? siteSettings.value?.general?.landing_page_title;
    return (typeof value === 'string' && value.trim().length) ? value.trim() : undefined;
  });

  const heroSubtitle = computed(() => {
    const value = landingSettings.value?.hero_subtitle
      ?? landingSettings.value?.landing_page_text
      ?? siteSettings.value?.general?.landing_page_text;
    return (typeof value === 'string' && value.trim().length) ? value.trim() : undefined;
  });

  const heroImages = computed(() => {
    const raw = landingSettings.value?.hero_images
      ?? landingSettings.value?.landing_hero_images
      ?? siteSettings.value?.general?.hero_images;
    if (!raw) return defaultHeroImages.map((img) => ({ ...img }));

    let parsed = raw;
    if (typeof raw === 'string') {
      try {
        parsed = JSON.parse(raw);
      } catch (_) {
        parsed = [];
      }
    }

    if (!Array.isArray(parsed)) return defaultHeroImages.map((img) => ({ ...img }));

    const normalized = parsed
      .map((item, index) => {
        if (!item || typeof item !== 'object') return null;
        const src = item.src ?? item.url ?? item.path ?? '';
        if (!src) return null;
        const alt = item.alt ?? item.label ?? item.title ?? heroTitle.value ?? `Hero image ${index + 1}`;
        return { src, alt };
      })
      .filter(Boolean);

    return normalized.length ? normalized : defaultHeroImages.map((img) => ({ ...img }));
  });

  const featuredGalleries = computed(() => page.props?.featuredGalleries || []);
  const latestNews = computed(() => page.props?.newsPosts || []);

  const openGallery = (id) => {
    if (id) window.location.href = `/galleries/${id}`;
  };
</script>
<template>
  <ThemeLayout>
    <HeroRotator
      :images="heroImages"
      :title="heroTitle"
      :subtitle="heroSubtitle"
    />
    <FeaturedGalleries
      v-if="featuredGalleries.length"
      :items="featuredGalleries"
      @open="openGallery"
    />
    <LatestNews
      v-if="latestNews.length"
      :items="latestNews"
    />
  </ThemeLayout>
</template>
