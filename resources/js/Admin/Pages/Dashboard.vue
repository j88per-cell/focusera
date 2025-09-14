<script setup>
import StatCard from '@admin/Components/StatCard.vue'
import ChartCard from '@admin/Components/ChartCard.vue'
import PopularServices from '@admin/Components/PopularServices.vue'
import RecentActivity from '@admin/Components/RecentActivity.vue'

import AdminLayout from '@admin/Layouts/AdminLayout.vue'

// mock data (will come from backend later)
const stats = {
  visitors: 12543,
  sales: 45230,
  bookings: 28,
  pendingBookings: 5,
  clients: 156
}
</script>

<template>
  <AdminLayout>
    <main class="p-6">
      <!-- Stats -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <StatCard title="Total Visitors" :value="stats.visitors.toLocaleString()" subtitle="+12% from last month" color="bg-blue-100">
          <template #icon>
            <!-- your svg -->
          </template>
        </StatCard>
        <!-- Repeat for Sales, Bookings, Clients -->
      </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <ChartCard title="Website Visitors">
          <div ref="visitorsChart"></div>
        </ChartCard>
        <ChartCard title="Monthly Revenue">
          <div ref="revenueChart"></div>
        </ChartCard>
      </div>

      <!-- Bottom -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <ChartCard title="Booking Types" height="h-48">
          <div ref="bookingTypesChart"></div>
        </ChartCard>
        <PopularServices :services="popularServices" />
        <RecentActivity :items="recentActivity" />
      </div>
    </main>
  </AdminLayout>
</template>
