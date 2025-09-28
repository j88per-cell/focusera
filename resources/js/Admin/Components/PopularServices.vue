<script setup>
defineProps({
  services: {
    type: Array,
    default: () => []
  }
});
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
      Popular Services
    </h3>
    <div class="space-y-3">
      <div
        v-for="service in services"
        :key="service.name"
        class="flex items-center justify-between"
      >
        <span class="text-sm font-medium text-gray-700">{{ service.name }}</span>
        <div class="flex items-center">
          <div class="w-20 bg-gray-200 rounded-full h-2 mr-2">
            <div
              class="bg-blue-600 h-2 rounded-full"
              :style="{ width: service.percentage + '%' }"
            />
          </div>
          <span class="text-sm text-gray-600">{{ service.percentage }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>
