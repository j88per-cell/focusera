<template>
  <div
    class="group relative overflow-hidden rounded-lg bg-gray-100 aspect-square hover:shadow-lg transition-all duration-300"
  >
    <template v-if="hasThumb">
      <img
        :src="thumbSrc"
        :alt="gallery.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        loading="lazy"
      />
    </template>
    <template v-else>
      <div class="w-full h-full flex items-center justify-center bg-slate-800 text-slate-200">
        <svg class="w-12 h-12 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7a2 2 0 012-2h5l2 2h7a2 2 0 012 2v7a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
        </svg>
      </div>
    </template>
    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300 flex items-end">
      <div class="p-4 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        <h3 class="font-semibold text-sm">{{ gallery.title }}</h3>
        <p class="text-xs text-gray-200">
          {{ photosLabel }}<span v-if="childLabel"> • {{ childLabel }}</span>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const props = defineProps({
  gallery: Object
})

const page = usePage()
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage')

function normalizeSrc(src) {
  if (!src) return ''
  if (/^(https?:)?\/\//.test(src) || src.startsWith('data:') || src.startsWith('/')) return src
  if (src.startsWith('storage/')) return `/${src}`
  return joinPublicBase(src)
}

const thumbSrc = computed(() => {
  return normalizeSrc(props.gallery?.thumbnail || props.gallery?.path_thumb || props.gallery?.thumb_url || props.gallery?.path_web || props.gallery?.web_url)
})

const hasThumb = computed(() => Boolean(thumbSrc.value))

const photosLabel = computed(() => {
  const count = Number(props.gallery?.photos_count ?? 0)
  return `${count} photo${count === 1 ? '' : 's'}`
})

const childLabel = computed(() => {
  const count = Number(props.gallery?.children_count ?? 0)
  if (!count) return ''
  return `${count} sub-${count === 1 ? 'gallery' : 'galleries'}`
})

function joinPublicBase(path) {
  const base = publicBaseUrl.value || ''
  if (!base) return '/' + path.replace(/^\/+/, '')
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`
}
</script>
