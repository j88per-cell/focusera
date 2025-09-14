<script setup>
defineProps({
  items: {
    type: Array,
    default: () => []
  }
})
</script>

<template>
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
    <div class="space-y-3">
      <div v-for="activity in items" :key="activity.id" class="flex items-start space-x-3">
        <div class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
        <div class="flex-1 min-w-0">
          <p class="text-sm text-gray-900">{{ activity.description }}</p>
          <p class="text-xs text-gray-500">{{ activity.time }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
