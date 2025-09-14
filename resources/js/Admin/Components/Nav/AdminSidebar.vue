<script setup>
defineProps({
  sidebarOpen: {
    type: Boolean,
    default: false
  }
})
</script>

<template>
  <aside
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
  >
    <div class="flex items-center justify-center h-16 bg-gray-900">
      <h1 class="text-xl font-bold text-white">PhotoStudio</h1>
    </div>

    <nav class="mt-8">
      <div class="px-4 space-y-2">
        <a href="/dashboard" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
          </svg>
          Dashboard
        </a>

        <a href="/galleries" class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-gray-100">
          <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v16a2 2 0 002 2z"/>
          </svg>
          Galleries
        </a>
      </div>
    </nav>
  </aside>
</template>
