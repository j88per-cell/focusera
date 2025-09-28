<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

const props = defineProps({ order: Object });

const page = usePage();
const publicBaseUrl = computed(() => page.props?.site?.storage?.public_base_url || '/storage');

function photoThumbSrc(photo) {
  if (!photo) return '';
  if (photo.thumb_url) return photo.thumb_url;
  if (photo.path_thumb) return normalize(photo.path_thumb);
  if (photo.path_web) return normalize(photo.path_web);
  return '';
}

function normalize(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:') || path.startsWith('/')) {
    return path;
  }
  if (path.startsWith('storage/')) return '/' + path.replace(/^\/+/, '');
  const base = publicBaseUrl.value || '';
  if (!base) return '/' + path.replace(/^\/+/, '');
  return `${base.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}
</script>

<template>
  <AdminLayout>
    <div class="max-w-5xl mx-auto p-6 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold">
          Order #{{ order.id }}
        </h1>
        <a
          href="/admin/orders"
          class="text-sm text-accent hover:underline"
        >Back</a>
      </div>

      <div class="bg-white rounded border p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <div class="text-xs text-gray-500">
              Status
            </div>
            <div class="font-medium">
              {{ order.status }}
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-500">
              Total
            </div>
            <div class="font-medium">
              {{ order.currency }} {{ Number(order.grand_total).toFixed(2) }}
            </div>
          </div>
          <div>
            <div class="text-xs text-gray-500">
              External ID
            </div>
            <div class="font-medium">
              {{ order.external_id || '—' }}
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded border p-4">
        <h2 class="text-lg font-medium mb-2">
          Items
        </h2>
        <table class="min-w-full text-sm">
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2 px-3">
                Photo
              </th>
              <th class="py-2 px-3">
                Product
              </th>
              <th class="py-2 px-3">
                Qty
              </th>
              <th class="py-2 px-3">
                Unit
              </th>
              <th class="py-2 px-3">
                Total
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="it in order.items"
              :key="it.id"
              class="border-b last:border-b-0"
            >
              <td class="py-2 px-3">
                <div class="flex items-center gap-2">
                  <img
                    v-if="it.photo"
                    :src="photoThumbSrc(it.photo)"
                    class="w-12 h-12 object-cover rounded"
                  >
                  <div class="text-xs text-gray-600">
                    #{{ it.photo_id || '—' }}
                  </div>
                </div>
              </td>
              <td class="py-2 px-3">
                {{ it.product_code || '—' }}<div class="text-xs text-gray-500">
                  {{ it.variant || '' }}
                </div>
              </td>
              <td class="py-2 px-3">
                {{ it.quantity }}
              </td>
              <td class="py-2 px-3">
                {{ order.currency }} {{ Number(it.unit_price).toFixed(2) }}
              </td>
              <td class="py-2 px-3">
                {{ order.currency }} {{ Number(it.total_price).toFixed(2) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="bg-white rounded border p-4">
        <h2 class="text-lg font-medium mb-2">
          Invoices
        </h2>
        <div
          v-if="!order.invoices || !order.invoices.length"
          class="text-sm text-gray-500"
        >
          No invoices.
        </div>
        <table
          v-else
          class="min-w-full text-sm"
        >
          <thead>
            <tr class="text-left text-gray-500 border-b">
              <th class="py-2 px-3">
                Number
              </th>
              <th class="py-2 px-3">
                Status
              </th>
              <th class="py-2 px-3">
                Total
              </th>
              <th class="py-2 px-3">
                Issued
              </th>
              <th class="py-2 px-3">
                Paid
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="inv in order.invoices"
              :key="inv.id"
              class="border-b last:border-b-0"
            >
              <td class="py-2 px-3">
                {{ inv.number || '—' }}
              </td>
              <td class="py-2 px-3">
                {{ inv.status }}
              </td>
              <td class="py-2 px-3">
                {{ inv.currency }} {{ Number(inv.amount_total).toFixed(2) }}
              </td>
              <td class="py-2 px-3">
                {{ inv.issued_at ? new Date(inv.issued_at).toLocaleString() : '—' }}
              </td>
              <td class="py-2 px-3">
                {{ inv.paid_at ? new Date(inv.paid_at).toLocaleString() : '—' }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AdminLayout>
</template>
