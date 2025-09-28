<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({ items: { type: Array, required: true } })
const emit = defineEmits(['open'])
const page = usePage()
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage')

function normalizeSrc(src) {
  if (!src) return ''
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src
  if (src.startsWith('storage/')) return `/${src}`
  return joinPublicBase(src)
}

function joinPublicBase(path) {
  const base = publicBaseUrl.value || ''
  if (!base) return '/' + path.replace(/^\/+/, '')
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`
}
</script>

<template>
  <section id="galleries" class="py-16 bg-muted">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <h3 class="text-3xl md:text-4xl font-bold text-foreground mb-4">Featured Galleries</h3>
        <p class="text-muted-foreground text-lg max-w-2xl mx-auto">Discover our most captivating photography collections</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div v-for="g in props.items" :key="g.id" class="group cursor-pointer" @click="$emit('open', g.id)">
          <div class="bg-card rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
            <div class="relative overflow-hidden h-64 flex items-center justify-center bg-muted">
              <template v-if="normalizeSrc(g.thumbnail)">
                <img :src="normalizeSrc(g.thumbnail)" :alt="g.title" class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300" />
              </template>
              <template v-else>
                <svg class="w-16 h-16 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                </svg>
              </template>
              <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-all duration-300 flex items-center justify-center">
                <span class="text-white font-semibold opacity-0 group-hover:opacity-100 transition-opacity">View Gallery</span>
              </div>
            </div>
            <div class="p-6">
              <h4 class="text-xl font-semibold text-card-foreground mb-2">{{ g.title }}</h4>
              <p class="text-muted-foreground">{{ g.description }}</p>
              <div class="mt-4 flex items-center text-sm text-muted-foreground">
                <span>{{ g.photoCount }} photos</span>
                <span class="mx-2">•</span>
                <span>{{ g.date }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>
