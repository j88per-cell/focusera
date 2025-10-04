<template>
  <div
    class="fixed inset-0 bg-slate-950/90 backdrop-blur-lg flex items-center justify-center z-50 p-6"
    @click="$emit('close')"
  >
    <div
      class="relative max-w-6xl max-h-full w-full flex flex-col items-center"
      @click.stop
    >
      <button
        class="absolute top-4 right-4 text-white/80 hover:text-white text-2xl font-bold"
        @click="$emit('close')"
      >
        ×
      </button>

      <img
        :src="photo.url"
        :alt="photo.title"
        class="max-w-full max-h-[80vh] object-contain rounded-[28px] shadow-[0_40px_80px_rgba(0,0,0,0.6)] bg-slate-900/50 border border-white/10"
      >

      <!-- Bottom info/action bar -->
      <div class="w-full mt-6 text-white">
        <div class="bg-slate-900/70 border border-white/10 rounded-2xl p-5">
          <div class="flex items-center justify-between gap-4">
            <div>
              <h3 class="text-xl font-semibold tracking-[0.2em] uppercase text-white/80">
                {{ photo.title || 'Photo' }}
              </h3>
              <div class="text-sm text-white/60 space-y-1 mt-3">
                <p
                  v-if="photo.attribution"
                  class="text-white/60"
                >
                  Attribution: {{ photo.attribution }}
                </p>
                <p
                  v-if="photo.notes"
                  class="text-white/60 whitespace-pre-line"
                >
                  {{ photo.notes }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <slot
                name="actions"
                :photo="photo"
              />
            </div>
          </div>
          <div
            v-if="showExif && photo.exif && Object.keys(photo.exif).length"
            class="mt-4 text-xs uppercase tracking-[0.3em] text-white/50 flex flex-wrap gap-x-6 gap-y-2"
          >
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
  photo: Object,
  showExif: { type: Boolean, default: true },
});
</script>
