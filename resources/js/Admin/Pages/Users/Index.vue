<script setup>
import { computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@admin/Layouts/AdminLayout.vue'

const props = defineProps({
  users: { type: Array, default: () => [] },
  roles: { type: Array, default: () => [] },
})

const roleMap = computed(() => {
  const map = {}
  for (const role of props.roles) {
    map[role.id] = role.label
  }
  return map
})

const defaultRole = computed(() => props.roles?.[0]?.id || '')

const form = useForm({
  email: '',
  name: '',
  role_id: defaultRole.value,
})

function invite() {
  form.post('/admin/users', {
    preserveScroll: true,
    onSuccess: () => {
      form.reset('email', 'name')
      form.role_id = defaultRole.value
    },
  })
}

const page = usePage()
const flashStatus = computed(() => page.props?.flash?.status || '')
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto p-6 space-y-6">
      <div>
        <h1 class="text-2xl font-semibold">Users</h1>
        <p class="text-sm text-gray-500">Invite teammates by email and assign roles.</p>
      </div>

      <div class="bg-white border rounded-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Invite User</h2>
        <form @submit.prevent="invite" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" type="email" required class="mt-1 w-full rounded-md border-gray-300" placeholder="person@example.com" />
            <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Name</label>
            <input v-model="form.name" type="text" class="mt-1 w-full rounded-md border-gray-300" placeholder="Optional" />
            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Role</label>
            <select v-model="form.role_id" required class="mt-1 w-full rounded-md border-gray-300">
              <option v-for="role in props.roles" :key="role.id" :value="role.id">{{ role.label }}</option>
            </select>
            <p v-if="form.errors.role_id" class="text-sm text-red-600 mt-1">{{ form.errors.role_id }}</p>
          </div>
          <div class="flex items-center gap-3">
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
              {{ form.processing ? 'Saving…' : 'Send Invite' }}
            </button>
            <span v-if="flashStatus" class="text-sm text-green-600">{{ flashStatus }}</span>
          </div>
        </form>
      </div>

      <div class="bg-white border rounded-lg overflow-hidden">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2 px-3">Name</th>
              <th class="py-2 px-3">Email</th>
              <th class="py-2 px-3">Role</th>
              <th class="py-2 px-3">Invited</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in props.users" :key="user.id" class="border-b last:border-b-0">
              <td class="py-2 px-3">{{ user.name }}</td>
              <td class="py-2 px-3">{{ user.email }}</td>
              <td class="py-2 px-3">{{ roleMap[user.role_id] || user.role_id }}</td>
              <td class="py-2 px-3">{{ new Date(user.created_at).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
        <div v-if="!props.users.length" class="p-4 text-sm text-gray-500">No users yet.</div>
      </div>
    </div>
  </AdminLayout>
</template>
