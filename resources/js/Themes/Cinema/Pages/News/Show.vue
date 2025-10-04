<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';

const props = defineProps({
  post: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <ThemeLayout>
    <article class="cinema-shell">
      <div class="cinema-article">
        <header class="mb-12 text-center">
          <p class="text-xs uppercase tracking-[0.5em] text-white/50">Studio Dispatch</p>
          <h1 class="mt-4 text-4xl font-semibold text-white">
            {{ props.post.title }}
          </h1>
          <p class="mt-4 text-xs uppercase tracking-[0.4em] text-white/40">
            {{ props.post.published_at ? new Date(props.post.published_at).toLocaleString() : '' }}
          </p>
        </header>

        <section
          class="cinema-body"
          v-html="props.post.body"
        />
      </div>
    </article>
  </ThemeLayout>
</template>

<style scoped>
.cinema-shell {
  min-height: calc(100vh - 6rem);
  display: flex;
  justify-content: center;
  padding: 4rem 1.5rem 6rem;
  background: radial-gradient(circle at top, rgba(30, 41, 59, 0.45), rgba(5, 6, 8, 0.95));
}

.cinema-article {
  width: min(840px, 100%);
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.88), rgba(15, 23, 42, 0.6));
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 28px;
  padding: clamp(2.5rem, 5vw, 3.5rem);
  backdrop-filter: blur(12px);
  color: #f8fafc;
}

.cinema-body :deep(p) {
  margin-bottom: 1.5rem;
  color: rgba(248, 250, 252, 0.82);
  line-height: 1.8;
}

.cinema-body :deep(h2),
.cinema-body :deep(h3) {
  margin-top: 2.5rem;
  margin-bottom: 1.25rem;
  color: #f8fafc;
  font-weight: 600;
}

.cinema-body :deep(img) {
  border-radius: 18px;
  margin: 2.5rem auto;
  max-width: 100%;
  box-shadow: 0 20px 45px rgba(15, 23, 42, 0.55);
}

.cinema-body :deep(blockquote) {
  border-left: 3px solid rgba(148, 163, 184, 0.5);
  margin: 2rem 0;
  padding-left: 1.5rem;
  color: rgba(248, 250, 252, 0.7);
  font-style: italic;
}
</style>
