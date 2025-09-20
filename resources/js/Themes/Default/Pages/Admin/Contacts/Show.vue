<script setup>
import AdminLayout from '../../../Layouts/app.layout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ message: Object });
const form = useForm({ status: props.message.status || 'new' });
function saveStatus() { form.put(`/admin/contacts/${props.message.id}`); }
function deleteMsg() { if (confirm('Delete this message?')) { const f = useForm({}); f.delete(`/admin/contacts/${props.message.id}`); } }
</script>

<template>
  <AdminLayout>
    <div class="max-w-3xl mx-auto px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Message</h1>
        <a href="/admin/contacts" class="text-sm text-accent hover:underline">Back</a>
      </div>
      <div class="bg-white shadow rounded-lg p-6 space-y-4">
        <div>
          <div class="font-medium">From</div>
          <div>{{ message.name }} &lt;{{ message.email }}&gt;</div>
        </div>
        <div>
          <div class="font-medium">Subject</div>
          <div>{{ message.subject || '(no subject)' }}</div>
        </div>
        <div>
          <div class="font-medium">Message</div>
          <pre class="whitespace-pre-wrap text-gray-800">{{ message.message }}</pre>
        </div>
        <div class="flex items-center gap-3">
          <select v-model="form.status" class="rounded border-gray-300">
            <option value="new">New</option>
            <option value="read">Read</option>
            <option value="archived">Archived</option>
          </select>
          <button @click="saveStatus" :disabled="form.processing" class="px-3 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
          <button @click="deleteMsg" class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Delete</button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

