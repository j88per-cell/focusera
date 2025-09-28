<script setup>
const props = defineProps({ items: { type: Array, required: true } })

const fallbackImage = '/placeholder.svg?height=200&width=300'
const fallbackAvatar = '/placeholder.svg?height=40&width=40'

const imageFor = (item) => item.featuredImage || item.thumbnail || fallbackImage
const categoryFor = (item) => item.category || 'News'
const dateFor = (item) => item.publishDate || ''
const urlFor = (item) => item.url || (item.slug ? `/news/${item.slug}` : '/news')
const authorFor = (item) => item.author?.name || 'Studio Team'
const avatarFor = (item) => item.author?.avatar || fallbackAvatar
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
        <article v-for="item in props.items" :key="item.id" class="bg-card rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
          <a :href="urlFor(item)" class="block"> 
            <img :src="imageFor(item)" :alt="item.title" class="w-full h-48 object-cover" />
            <div class="p-6">
              <div class="flex items-center text-sm text-muted-foreground mb-3">
                <span>{{ categoryFor(item) }}</span>
                <span class="mx-2" v-if="dateFor(item)">•</span>
                <span v-if="dateFor(item)">{{ dateFor(item) }}</span>
              </div>
              <h4 class="text-xl font-semibold text-accent mb-3 hover:text-accent/80 transition-colors">
                {{ item.title }}
              </h4>
              <p class="text-muted-foreground mb-4">{{ item.excerpt }}</p>
              <div class="flex items-center">
                <img :src="avatarFor(item)" :alt="authorFor(item)" class="w-8 h-8 rounded-full mr-3" />
                <span class="text-sm text-muted-foreground">{{ authorFor(item) }}</span>
              </div>
            </div>
          </a>
        </article>
      </div>

      <div class="text-center mt-12">
        <a href="/news" class="inline-block bg-primary hover:bg-primary/90 text-primary-foreground px-6 py-3 rounded-lg font-semibold transition-colors">
          View All Articles
        </a>
      </div>
    </div>
  </section>
</template>
