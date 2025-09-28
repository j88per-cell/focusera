<script setup>
import AdminLayout from '@admin/Layouts/AdminLayout.vue';
const props = defineProps({ orders: Object });
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto p-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">
          Orders
        </h1>
      </div>

      <div class="bg-white rounded border overflow-hidden">
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2 px-3">
                ID
              </th>
              <th class="py-2 px-3">
                Status
              </th>
              <th class="py-2 px-3">
                Total
              </th>
              <th class="py-2 px-3">
                External
              </th>
              <th class="py-2 px-3">
                Created
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="o in props.orders.data"
              :key="o.id"
              class="border-b last:border-b-0"
            >
              <td class="py-2 px-3">
                <a
                  class="text-indigo-600 hover:underline"
                  :href="`/admin/orders/${o.id}`"
                >#{{ o.id }}</a>
              </td>
              <td class="py-2 px-3">
                {{ o.status }}
              </td>
              <td class="py-2 px-3">
                {{ o.currency }} {{ Number(o.grand_total).toFixed(2) }}
              </td>
              <td class="py-2 px-3">
                {{ o.external_id || '' }}
              </td>
              <td class="py-2 px-3">
                {{ new Date(o.created_at).toLocaleString() }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>

