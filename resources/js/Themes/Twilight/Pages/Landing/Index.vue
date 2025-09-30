<script setup>
  import ThemeLayout from '../../Layouts/app.layout.vue';
  import { computed } from 'vue';
  import { usePage } from '@inertiajs/vue3';
  import FeaturedGalleries from '@/Components/FeaturedGalleries.vue';
  import LatestNews from '@/Components/LatestNews.vue';

  const page = usePage();

  const siteSettings = computed(() => page.props?.site ?? {});

  const defaultHeroCopy = {
    title: 'Photography, In Twilight',
    subtitle: 'Explore galleries and stories captured after golden hour.',
  };

  const firstFilledString = (...candidates) => {
    for (const value of candidates) {
      if (typeof value === 'string') {
        const trimmed = value.trim();
        if (trimmed.length) return trimmed;
      }
    }
    return undefined;
  };

  const defaultHeroImages = [
    { src: '/placeholder.svg?height=600&width=1400', alt: defaultHeroCopy.title },
  ];

  const landingSettings = computed(() => {
    const site = siteSettings.value;
    const candidates = [site.landing, site.landing_page, site.landingPage, site.general];
    for (const candidate of candidates) {
      if (candidate && typeof candidate === 'object') return candidate;
    }
    return {};
  });

  const heroTitle = computed(() => {
    const landing = landingSettings.value ?? {};
    const general = siteSettings.value?.general ?? {};
    const value = firstFilledString(
      landing.hero_title,
      landing.landing_page_title,
      landing.topic,
      landing.heading,
      general.landing_page_title,
      general.topic,
      general.heading,
      general.site_name,
    );
    return value ?? defaultHeroCopy.title;
  });

  const heroSubtitle = computed(() => {
    const landing = landingSettings.value ?? {};
    const general = siteSettings.value?.general ?? {};
    const value = firstFilledString(
      landing.hero_subtitle,
      landing.landing_page_text,
      landing.subtopic,
      landing.subtitle,
      general.landing_page_text,
      general.subtopic,
      general.subtitle,
      general.tagline,
    );
    return value ?? defaultHeroCopy.subtitle;
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

  const heroBackground = computed(() => heroImages.value[0] ?? null);

  const showFeatured = computed(() => Boolean(page.props?.features?.featured_galleries));
  const showNews = computed(() => Boolean(page.props?.features?.news));
  const featuredGalleries = computed(() => page.props?.featuredGalleries || []);
  const latestNews = computed(() => page.props?.newsPosts || []);
</script>
<template>
  <ThemeLayout>
    <section class="relative overflow-hidden bg-gradient-to-b from-transparent to-black/10">
      <div
        v-if="heroBackground"
        class="absolute inset-0"
      >
        <img
          :src="heroBackground.src"
          :alt="heroBackground.alt"
          class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-black/60" />
      </div>
      <div class="relative z-10 max-w-6xl mx-auto px-6 py-20 text-center text-white">
        <h1 class="text-4xl md:text-6xl font-extrabold mb-4 text-balance">
          {{ heroTitle }}
        </h1>
        <p class="text-lg md:text-xl mb-8 text-pretty">
          {{ heroSubtitle }}
        </p>
        <div class="flex items-center justify-center gap-4">
          <a
            href="/galleries"
            class="px-6 py-3 rounded-lg bg-primary text-primary-foreground hover:opacity-90"
          >Browse Galleries</a>
          <a
            href="/news"
            class="px-6 py-3 rounded-lg border border-border hover:bg-muted"
          >Latest News</a>
        </div>
      </div>
    </section>
    
    <section
      v-if="showFeatured && featuredGalleries.length"
      class="mt-6"
    >
      <FeaturedGalleries :items="featuredGalleries" />
    </section>

    <LatestNews
      v-if="showNews && latestNews.length"
      :items="latestNews"
    />
  </ThemeLayout>
</template>
