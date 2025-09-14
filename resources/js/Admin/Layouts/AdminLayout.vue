<script setup>
</script>

<template>
  <div class="min-h-screen bg-gray-50 flex">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg">
      <div class="flex items-center justify-center h-16 bg-gray-900">
        <h1 class="text-xl font-bold text-white">PhotoStudio</h1>
      </div>

      <nav class="mt-8">
        <div class="px-4 space-y-2">
          <a href="/admin/dashboard"
             class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
            </svg>
            Dashboard
          </a>

          <a href="/admin/galleries"
             class="flex items-center px-4 py-2 text-gray-600 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2-2v16a2 2 0 002 2z"/>
            </svg>
            Galleries
          </a>

          <!-- More links can go here -->
        </div>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 lg:ml-64">
      <!-- Top Navigation -->
      <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between px-6 py-4">
          <h2 class="text-xl font-semibold text-gray-800">Admin</h2>

          <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <button class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-5 5v-5zM4 19h6v-2H4v2zM4 15h8v-2H4v2zM4 11h10V9H4v2z"/>
              </svg>
            </button>

            <!-- User Menu -->
            <div class="relative">
              <button class="flex items-center p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                <img class="w-8 h-8 rounded-full" src="" alt="Profile">
                <span class="ml-2 text-sm font-medium">John Doe</span>
              </button>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
