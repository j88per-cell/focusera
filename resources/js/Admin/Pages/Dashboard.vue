<script setup>
import { computed } from 'vue';
import StatCard from '@admin/Components/StatCard.vue';
import ChartCard from '@admin/Components/ChartCard.vue';
import AdminLayout from '@admin/Layouts/AdminLayout.vue';

const props = defineProps({
  analyticsEnabled: { type: Boolean, default: false },
  analytics: { type: Object, default: () => null }
});

const summary = computed(() => props.analytics?.summary || {});
const eventsByDay = computed(() => props.analytics?.events_by_day || []);
const deviceSplit = computed(() => props.analytics?.device_split || {});
const topGalleries = computed(() => props.analytics?.top_galleries || []);
const topPhotos = computed(() => props.analytics?.top_photos || []);

const totalEventsByDay = computed(() => {
  return eventsByDay.value.map(item => ({
    label: item.label,
    total: (item['page_view'] || 0) + (item['private_gallery_view'] || 0) + (item['conversion'] || 0),
    page: item['page_view'] || 0,
    private: item['private_gallery_view'] || 0,
    conversions: item['conversion'] || 0
  }));
});

const maxEventsInDay = computed(() => totalEventsByDay.value.reduce((max, item) => Math.max(max, item.total), 0));

function barHeight(value) {
  const max = maxEventsInDay.value || 1;
  const scaled = Math.round((value / max) * 100);
  return `${Math.max(scaled, value > 0 ? 6 : 0)}%`;
}

const deviceTotal = computed(() => Object.values(deviceSplit.value).reduce((sum, v) => sum + v, 0));

function devicePercentage(key) {
  const total = deviceTotal.value || 1;
  const value = deviceSplit.value[key] || 0;
  return Math.round((value / total) * 100);
}
</script>

<template>
  <AdminLayout>
    <main class="p-6 space-y-6">
      <div
        v-if="!analyticsEnabled"
        class="bg-white border rounded-lg p-6"
      >
        <h2 class="text-lg font-semibold mb-2">
          Analytics Disabled
        </h2>
        <p class="text-sm text-gray-600">
          Enable analytics in <strong>Admin → Settings → Site → analytics</strong> to start collecting privacy-friendly engagement metrics.
        </p>
      </div>

      <template v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <StatCard
            title="Sessions (30 days)"
            :value="(summary.sessions || 0).toLocaleString()"
            subtitle="Unique visitors"
          >
            <template #icon>
              <svg
                class="w-6 h-6 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              ><path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="1.5"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3"
              /></svg>
            </template>
          </StatCard>
          <StatCard
            title="Page Views"
            :value="(summary.page_views || 0).toLocaleString()"
            subtitle="Tracked views"
            color="bg-blue-100"
          />
          <StatCard
            title="Private Gallery Views"
            :value="(summary.private_views || 0).toLocaleString()"
            subtitle="Access code usage"
            color="bg-purple-100"
          />
          <StatCard
            title="Conversions"
            :value="(summary.conversions || 0).toLocaleString()"
            subtitle="Orders, downloads, etc."
            color="bg-green-100"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <ChartCard title="Events (last 14 days)">
            <div class="flex items-end gap-2 h-48">
              <div
                v-for="item in totalEventsByDay"
                :key="item.label"
                class="flex-1 flex flex-col items-center"
              >
                <div class="w-full flex flex-col justify-end h-40">
                  <div
                    class="bg-blue-500/60"
                    :style="{ height: barHeight(item.page) }"
                  />
                  <div
                    class="bg-purple-500/60"
                    :style="{ height: barHeight(item.private) }"
                  />
                  <div
                    class="bg-emerald-500/60"
                    :style="{ height: barHeight(item.conversions) }"
                  />
                </div>
                <span class="mt-2 text-xs text-gray-500">{{ item.label }}</span>
              </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">
              Stacked bars represent page views (blue), private gallery views (purple), and conversions (green).
            </p>
          </ChartCard>

          <ChartCard
            title="Device Split"
            height="h-48"
          >
            <div class="space-y-3">
              <div
                v-for="(value, key) in deviceSplit"
                :key="key"
                class="text-sm"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="capitalize">{{ key }}</span>
                  <span>{{ value }} ({{ devicePercentage(key) }}%)</span>
                </div>
                <div class="w-full h-2 bg-gray-200 rounded">
                  <div
                    class="h-2 rounded bg-indigo-500"
                    :style="{ width: `${devicePercentage(key)}%` }"
                  />
                </div>
              </div>
            </div>
          </ChartCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <ChartCard
            title="Top Galleries"
            height="h-60"
          >
            <div
              v-if="!topGalleries.length"
              class="text-sm text-gray-500"
            >
              No data yet.
            </div>
            <ul
              v-else
              class="space-y-3"
            >
              <li
                v-for="item in topGalleries"
                :key="item.id"
                class="flex items-center justify-between"
              >
                <div>
                  <p class="font-medium">
                    {{ item.label }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ item.count }} views
                  </p>
                </div>
              </li>
            </ul>
          </ChartCard>

          <ChartCard
            title="Top Photos"
            height="h-60"
          >
            <div
              v-if="!topPhotos.length"
              class="text-sm text-gray-500"
            >
              No data yet.
            </div>
            <ul
              v-else
              class="space-y-3"
            >
              <li
                v-for="item in topPhotos"
                :key="item.id"
                class="flex items-center justify-between"
              >
                <span class="font-medium">Photo #{{ item.id }}</span>
                <span class="text-sm text-gray-500">{{ item.count }} views</span>
              </li>
            </ul>
          </ChartCard>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-500">
              Bounce Rate
            </p>
            <p class="text-3xl font-semibold">
              {{ summary.bounce_rate || 0 }}%
            </p>
          </div>
          <div class="bg-white border rounded-lg p-4">
            <p class="text-sm text-gray-500">
              Avg. Session Length
            </p>
            <p class="text-3xl font-semibold">
              {{ summary.avg_session_minutes || 0 }} min
            </p>
          </div>
        </div>
      </template>
    </main>
  </AdminLayout>
</template>
