<script setup>
import AdminLayout from '../../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ posts: Object });

function edit(id) { window.location.href = `/admin/news/${id}/edit`; }
function create() { window.location.href = `/admin/news/create`; }
</script>

<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">News</h1>
        <button @click="create" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">New Post</button>
      </div>
      <div class="bg-white shadow rounded-lg divide-y">
        <div v-for="p in posts.data" :key="p.id" class="px-6 py-4 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ p.title }}</div>
            <div class="text-xs text-gray-500">{{ p.published_at ? new Date(p.published_at).toLocaleString() : 'Draft' }}</div>
          </div>
          <div class="flex items-center gap-3">
            <a :href="`/news/${p.slug}`" class="text-sm text-accent hover:underline">View</a>
            <button @click="edit(p.id)" class="text-sm text-gray-600 hover:underline">Edit</button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

