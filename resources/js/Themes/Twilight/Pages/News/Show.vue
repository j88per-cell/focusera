<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';
const props = defineProps({ post: Object });
</script>

<template>
  <ThemeLayout>
    <div class="max-w-3xl mx-auto py-10 px-4">
      <h1 class="text-3xl font-semibold">{{ post.title }}</h1>
      <div class="text-xs text-muted-foreground mt-1">{{ post.published_at ? new Date(post.published_at).toLocaleString() : '' }}</div>
      <div class="prose max-w-none mt-6 prose-invert" v-html="post.body"></div>
    </div>
  </ThemeLayout>
  </template>

