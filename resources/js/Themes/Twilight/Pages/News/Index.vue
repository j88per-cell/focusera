<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
const props = defineProps({ posts: Object });
function open(slug) { window.location.href = `/news/${slug}`; }
</script>

<template>
  <ThemeLayout>
    <div class="max-w-4xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-semibold mb-6">
        News
      </h1>
      <div
        v-if="!posts?.data?.length"
        class="text-muted-foreground"
      >
        No news yet.
      </div>
      <div
        v-else
        class="space-y-6"
      >
        <article
          v-for="p in posts.data"
          :key="p.id"
          class="bg-card rounded border border-border p-5"
        >
          <h2 class="text-xl font-semibold">
            <a
              :href="`/news/${p.slug}`"
              class="hover:underline"
            >{{ p.title }}</a>
          </h2>
          <div class="text-xs text-muted-foreground mt-1">
            {{ p.published_at ? new Date(p.published_at).toLocaleString() : '' }}
          </div>
          <p class="mt-3 text-foreground/80">
            {{ p.excerpt || '' }}
          </p>
          <div class="mt-3">
            <a
              :href="`/news/${p.slug}`"
              class="text-accent hover:underline"
            >Read more</a>
          </div>
        </article>
      </div>
    </div>
  </ThemeLayout>
</template>

