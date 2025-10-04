<script setup>
import ThemeLayout from '../../Layouts/app.layout.vue';

const props = defineProps({
  posts: {
    type: Object,
    default: () => ({}),
  },
});
</script>

<template>
  <ThemeLayout>
    <section class="cinema-shell">
      <div class="cinema-frame">
        <header class="mb-10 text-center">
          <p class="text-xs uppercase tracking-[0.5em] text-white/60">Studio Dispatch</p>
          <h1 class="mt-3 text-4xl font-semibold text-white">Latest News</h1>
        </header>

        <div
          v-if="!props.posts?.data?.length"
          class="text-center text-white/50"
        >
          Nothing to share just yet.
        </div>

        <div
          v-else
          class="grid gap-6"
        >
          <article
            v-for="post in props.posts.data"
            :key="post.id"
            class="cinema-card"
          >
            <div class="flex flex-col gap-2">
              <h2 class="text-2xl font-semibold text-white">
                <a
                  :href="`/news/${post.slug}`"
                  class="hover:text-white/70 transition"
                >{{ post.title }}</a>
              </h2>
              <p class="text-xs uppercase tracking-[0.4em] text-white/40">
                {{ post.published_at ? new Date(post.published_at).toLocaleDateString() : '' }}
              </p>
            </div>

            <p
              v-if="post.excerpt"
              class="text-white/70 leading-relaxed"
            >
              {{ post.excerpt }}
            </p>

            <div class="flex items-center justify-between pt-4">
              <span class="text-sm text-white/50">{{ post.author?.name ?? 'Studio Team' }}</span>
              <a
                :href="`/news/${post.slug}`"
                class="text-xs uppercase tracking-[0.4em] text-white/60 hover:text-white"
              >Read Article</a>
            </div>
          </article>
        </div>
      </div>
    </section>
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

.cinema-frame {
  width: min(960px, 100%);
  display: flex;
  flex-direction: column;
}

.cinema-card {
  background: linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.55));
  border: 1px solid rgba(148, 163, 184, 0.25);
  border-radius: 24px;
  padding: 2.25rem;
  backdrop-filter: blur(12px);
  transition: transform 200ms ease, border-color 200ms ease;
}

.cinema-card:hover {
  transform: translateY(-4px);
  border-color: rgba(148, 163, 184, 0.45);
}
</style>
