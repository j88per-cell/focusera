<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  images: { type: Array, required: true }, // [{src,alt}, ...]
  intervalMs: { type: Number, default: 5000 },
  title: { type: String, default: "Capturing Life's Beautiful Moments" },
  subtitle: { type: String, default: "Professional photography that tells your story" }
})

const idx = ref(0)
const current = ref(props.images[0]?.src || '')
let timer = null

onMounted(() => {
  timer = setInterval(() => {
    idx.value = (idx.value + 1) % props.images.length
    current.value = props.images[idx.value].src
  }, props.intervalMs)
})
onUnmounted(() => { if (timer) clearInterval(timer) })

const scrollTo = (id) => document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' })
</script>

<template>
  <section class="relative h-96 md:h-[500px] overflow-hidden">
    <div class="absolute inset-0">
      <img :src="current" :alt="images[idx].alt" class="w-full h-full object-cover transition-opacity duration-1000" />
      <div class="absolute inset-0 bg-black/40"></div>
    </div>
    <div class="relative z-10 flex items-center justify-center h-full text-center text-white">
      <div class="max-w-4xl mx-auto px-4">
        <h2 class="text-4xl md:text-6xl font-bold mb-4 text-balance">{{ title }}</h2>
        <p class="text-xl md:text-2xl mb-8 text-pretty">{{ subtitle }}</p>
        <button class="bg-accent hover:bg-accent/90 text-accent-foreground px-8 py-3 rounded-lg text-lg font-semibold transition-colors"
                @click="scrollTo('galleries')">
          Explore Galleries
        </button>
      </div>
    </div>
  </section>
</template>
