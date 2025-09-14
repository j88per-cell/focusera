<script setup>
const props = defineProps({ items: { type: Array, required: true } })
const emit = defineEmits(['open'])
</script>

<template>
  <section class="py-16 bg-background">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h3 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Latest News</h3>
        <p class="text-muted-foreground text-lg max-w-2xl mx-auto">
          Stay updated with our latest projects and photography insights
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <article v-for="a in props.items" :key="a.id"
                 class="bg-card rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow cursor-pointer"
                 @click="$emit('open', a.id)">
          <img :src="a.featuredImage" :alt="a.title" class="w-full h-48 object-cover" />
          <div class="p-6">
            <div class="flex items-center text-sm text-muted-foreground mb-3">
              <span>{{ a.category }}</span><span class="mx-2">•</span><span>{{ a.publishDate }}</span>
            </div>
            <h4 class="text-xl font-semibold text-accent mb-3 hover:text-accent/80 transition-colors">
              {{ a.title }}
            </h4>
            <p class="text-muted-foreground mb-4">{{ a.excerpt }}</p>
            <div class="flex items-center">
              <img :src="a.author.avatar" :alt="a.author.name" class="w-8 h-8 rounded-full mr-3" />
              <span class="text-sm text-muted-foreground">{{ a.author.name }}</span>
            </div>
          </div>
        </article>
      </div>

      <div class="text-center mt-12">
        <button class="bg-primary hover:bg-primary/90 text-primary-foreground px-6 py-3 rounded-lg font-semibold transition-colors">
          View All Articles
        </button>
      </div>
    </div>
  </section>
</template>
