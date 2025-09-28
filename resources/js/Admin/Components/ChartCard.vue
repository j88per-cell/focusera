<script setup>
defineProps({
  title: String,
  height: {
    type: String,
    default: 'h-64'
  }
});
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
      {{ title }}
    </h3>
    <div :class="height">
      <slot />
    </div>
  </div>
</template>
