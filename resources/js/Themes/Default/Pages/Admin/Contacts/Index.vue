<script setup>
import AdminLayout from '../../../Layouts/app.layout.vue';

const props = defineProps({ messages: Object });
</script>

<template>
  <AdminLayout>
    <div class="max-w-6xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Contact Messages</h1>
      </div>
      <div class="bg-white shadow rounded-lg divide-y">
        <div v-for="m in messages.data" :key="m.id" class="px-6 py-4 flex items-center justify-between">
          <div>
            <div class="font-medium">{{ m.name }} <span class="text-gray-500">&lt;{{ m.email }}&gt;</span></div>
            <div class="text-xs text-gray-500">{{ m.subject || '(no subject)' }} • {{ new Date(m.created_at).toLocaleString() }} • {{ m.status }}</div>
          </div>
          <div class="flex items-center gap-3">
            <a :href="`/admin/contacts/${m.id}`" class="text-sm text-accent hover:underline">Open</a>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

