<template>
  <div class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-6" @click="$emit('close')">
    <div class="relative max-w-5xl max-h-full w-full flex flex-col items-center" @click.stop>
      <button @click="$emit('close')" class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl font-bold">×</button>

      <img
        :src="photo.url"
        :alt="photo.title"
        class="max-w-full max-h-[80vh] object-contain border-8 border-white rounded-lg shadow-2xl bg-white"
      />

      <div class="w-full mt-5 text-white">
        <div class="bg-black/60 rounded-md p-4">
          <h3 class="text-lg font-semibold">{{ photo.title || 'Photo' }}</h3>
          <div v-if="photo.exif && Object.keys(photo.exif).length" class="mt-3 text-sm text-gray-200 flex flex-wrap gap-x-6 gap-y-2">
            <span v-if="photo.exif.camera">Camera: {{ photo.exif.camera }}</span>
            <span v-if="photo.exif.lens">Lens: {{ photo.exif.lens }}</span>
            <span v-if="photo.exif.aperture">Aperture: {{ photo.exif.aperture }}</span>
            <span v-if="photo.exif.shutter">Shutter: {{ photo.exif.shutter }}</span>
            <span v-if="photo.exif.iso">ISO: {{ photo.exif.iso }}</span>
            <span v-if="photo.exif.focal">Focal: {{ photo.exif.focal }}</span>
            <span v-if="photo.exif.datetime">Date: {{ photo.exif.datetime }}</span>
            <span v-if="photo.exif.photographer">Photographer: {{ photo.exif.photographer }}</span>
            <span v-if="photo.exif.latitude && photo.exif.longitude">Location: {{ Number(photo.exif.latitude).toFixed(6) }}, {{ Number(photo.exif.longitude).toFixed(6) }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  photo: Object
})
</script>
