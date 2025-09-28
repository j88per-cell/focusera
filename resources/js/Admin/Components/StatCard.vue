<script setup>
defineProps({
  title: String,
  value: [String, Number],
  subtitle: String,
  icon: String,   // optional SVG path/class
  color: String   // tailwind color for bg/text
});
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600">
          {{ title }}
        </p>
        <p class="text-3xl font-bold text-gray-900">
          {{ value }}
        </p>
        <p
          v-if="subtitle"
          class="text-sm mt-1"
        >
          {{ subtitle }}
        </p>
      </div>
      <div :class="['p-3 rounded-full', color]">
        <slot name="icon" />
      </div>
    </div>
  </div>
</template>
