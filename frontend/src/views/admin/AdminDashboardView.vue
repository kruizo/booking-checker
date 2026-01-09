<template>
  <div class="min-h-screen bg-gray-50 p-8">
    <div class="mx-auto">
      <!-- Period Selector -->
      <div class="mb-6 flex gap-2 py-5">
        <button
          v-for="p in ['daily', 'weekly', 'yearly']"
          :key="p"
          @click="changePeriod(p as any)"
          :class="[
            'px-4 py-2 rounded-lg font-medium transition-colors',
            period === p ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100',
          ]"
        >
          {{ p.charAt(0).toUpperCase() + p.slice(1) }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div
          class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-indigo-600 border-r-transparent"
        ></div>
        <p class="mt-4 text-gray-600">Loading dashboard...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4">
        <p class="text-red-800">{{ error }}</p>
      </div>

      <!-- Dashboard Content -->
      <div v-else>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Bookings</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                  {{ statistics?.summary.total_bookings || 0 }}
                </p>
              </div>
              <div class="h-12 w-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                <svg
                  class="h-6 w-6 text-indigo-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Signups</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">
                  {{ statistics?.summary.total_signups || 0 }}
                </p>
              </div>
              <div class="h-12 w-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg
                  class="h-6 w-6 text-green-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Conflicts</p>
                <p class="text-3xl font-bold text-red-600 mt-1">
                  {{ conflicts?.summary.conflict_count || 0 }}
                </p>
              </div>
              <div class="h-12 w-12 bg-red-100 rounded-lg flex items-center justify-center">
                <svg
                  class="h-6 w-6 text-red-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Gaps Found</p>
                <p class="text-3xl font-bold text-blue-600 mt-1">
                  {{ conflicts?.summary.gap_count || 0 }}
                </p>
              </div>
              <div class="h-12 w-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg
                  class="h-6 w-6 text-blue-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 10V3L4 14h7v7l9-11h-7z"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Activity Chart -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Activity Trends</h2>
            <div v-if="statistics">
              <Line :data="activityChartData" :options="activityChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              No data available
            </div>
          </div>

          <!-- Conflicts Breakdown -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Conflicts Breakdown</h2>
            <div v-if="conflicts">
              <Bar :data="conflictsChartData" :options="conflictsChartOptions" />
            </div>
            <div v-else class="h-64 flex items-center justify-center text-gray-400">
              No data available
            </div>
          </div>
        </div>

        <!-- Detailed Conflicts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
          <!-- Overlapping Bookings -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
              Overlapping Bookings
              <span class="text-sm font-normal text-gray-500 ml-2">
                ({{ conflicts?.overlapping.length || 0 }})
              </span>
            </h2>
            <div
              v-if="conflicts && conflicts.overlapping.length > 0"
              class="space-y-4 max-h-96 overflow-y-auto"
            >
              <div
                v-for="(overlap, idx) in conflicts.overlapping"
                :key="idx"
                class="border border-orange-200 bg-orange-50 rounded-lg p-4"
              >
                <div class="flex items-start gap-3">
                  <div
                    class="h-8 w-8 bg-orange-200 rounded-full flex items-center justify-center shrink-0"
                  >
                    <svg class="h-4 w-4 text-orange-700" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-orange-900">
                      {{ overlap.overlap_type === 'partial' ? 'Partial Overlap' : 'Full Overlap' }}
                    </p>
                    <div class="mt-2 space-y-2 text-xs text-orange-800">
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Booking #{{ overlap.booking_1.id }}:</span>
                        <span
                          >{{ overlap.booking_1.user }} • {{ overlap.booking_1.date }} •
                          {{ overlap.booking_1.start_time }}-{{ overlap.booking_1.end_time }}</span
                        >
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Booking #{{ overlap.booking_2.id }}:</span>
                        <span
                          >{{ overlap.booking_2.user }} • {{ overlap.booking_2.date }} •
                          {{ overlap.booking_2.start_time }}-{{ overlap.booking_2.end_time }}</span
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-400">
              <svg
                class="h-12 w-12 mx-auto mb-2 text-gray-300"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <p>No overlapping bookings found</p>
            </div>
          </div>

          <!-- Exact Conflicts -->
          <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">
              Exact Conflicts
              <span class="text-sm font-normal text-gray-500 ml-2">
                ({{ conflicts?.conflicts.length || 0 }})
              </span>
            </h2>
            <div
              v-if="conflicts && conflicts.conflicts.length > 0"
              class="space-y-4 max-h-96 overflow-y-auto"
            >
              <div
                v-for="(conflict, idx) in conflicts.conflicts"
                :key="idx"
                class="border border-red-200 bg-red-50 rounded-lg p-4"
              >
                <div class="flex items-start gap-3">
                  <div
                    class="h-8 w-8 bg-red-200 rounded-full flex items-center justify-center shrink-0"
                  >
                    <svg class="h-4 w-4 text-red-700" fill="currentColor" viewBox="0 0 20 20">
                      <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-sm font-medium text-red-900">
                      {{
                        conflict.conflict_type === 'exact_match'
                          ? 'Exact Time Match'
                          : 'Critical Conflict'
                      }}
                    </p>
                    <div class="mt-2 space-y-2 text-xs text-red-800">
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Booking #{{ conflict.booking_1.id }}:</span>
                        <span
                          >{{ conflict.booking_1.user }} • {{ conflict.booking_1.date }} •
                          {{ conflict.booking_1.start_time }}-{{
                            conflict.booking_1.end_time
                          }}</span
                        >
                      </div>
                      <div class="flex items-center gap-2">
                        <span class="font-medium">Booking #{{ conflict.booking_2.id }}:</span>
                        <span
                          >{{ conflict.booking_2.user }} • {{ conflict.booking_2.date }} •
                          {{ conflict.booking_2.start_time }}-{{
                            conflict.booking_2.end_time
                          }}</span
                        >
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-400">
              <svg
                class="h-12 w-12 mx-auto mb-2 text-gray-300"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <p>No exact conflicts found</p>
            </div>
          </div>
        </div>

        <!-- Gaps Section -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">
            Schedule Gaps
            <span class="text-sm font-normal text-gray-500 ml-2">
              ({{ conflicts?.gaps.length || 0 }})
            </span>
          </h2>
          <div v-if="conflicts && conflicts.gaps.length > 0" class="space-y-4">
            <div
              v-for="(gap, idx) in conflicts.gaps"
              :key="idx"
              class="border border-blue-200 bg-blue-50 rounded-lg p-4"
            >
              <div class="flex items-start gap-3">
                <div
                  class="h-8 w-8 bg-blue-200 rounded-full flex items-center justify-center shrink-0"
                >
                  <svg class="h-4 w-4 text-blue-700" fill="currentColor" viewBox="0 0 20 20">
                    <path
                      fill-rule="evenodd"
                      d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-blue-900">
                      {{ gap.date }} • {{ gap.gap_start }} - {{ gap.gap_end }}
                    </p>
                    <span
                      class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full"
                    >
                      {{ formatDuration(gap.gap_duration_minutes) }}
                    </span>
                  </div>
                  <div class="mt-2 text-xs text-blue-800">
                    <p>Gap between:</p>
                    <div class="mt-1 space-y-1">
                      <p v-for="(booking, bidx) in gap.between_bookings" :key="bidx">
                        • Booking #{{ booking.id }} ({{ booking.user }})
                        <span v-if="booking.end_time">ends at {{ booking.end_time }}</span>
                        <span v-if="booking.start_time">starts at {{ booking.start_time }}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-400">
            <svg
              class="h-12 w-12 mx-auto mb-2 text-gray-300"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <p>No gaps found in schedule</p>
          </div>
        </div>

        <!-- Recent Signups -->
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Signups</h2>
          <div v-if="statistics && statistics.recent_signups.length > 0" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Name
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Email
                  </th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                    Joined
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="user in statistics.recent_signups"
                  :key="user.id"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-sm text-gray-900">{{ user.name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ user.email }}</td>
                  <td class="px-4 py-3 text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div v-else class="text-center py-8 text-gray-400">No recent signups</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Line, Bar } from 'vue-chartjs'
import {
  Chart,
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
} from 'chart.js'
import { useStats } from '@/composables/useStats'

Chart.register(
  Title,
  Tooltip,
  Legend,
  LineElement,
  BarElement,
  CategoryScale,
  LinearScale,
  PointElement,
)

const { statistics, conflicts, loading, error, fetchStatistics, fetchConflicts, formatDuration } =
  useStats()
const period = ref<'daily' | 'weekly' | 'yearly'>('daily')

const activityChartData = computed(() => {
  if (!statistics.value) return { labels: [], datasets: [] }

  const intervals = statistics.value.intervals || []
  return {
    labels: intervals.map((i) => i.date),
    datasets: [
      {
        label: 'Bookings',
        data: intervals.map((i) => i.bookings),
        borderColor: '#6366f1',
        backgroundColor: 'rgba(99, 102, 241, 0.1)',
        tension: 0.4,
        fill: true,
      },
      {
        label: 'Signups',
        data: intervals.map((i) => i.signups),
        borderColor: '#10b981',
        backgroundColor: 'rgba(16, 185, 129, 0.1)',
        tension: 0.4,
        fill: true,
      },
    ],
  }
})

const activityChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
    tooltip: {
      mode: 'index' as const,
      intersect: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
}

const conflictsChartData = computed(() => {
  if (!conflicts.value) return { labels: [], datasets: [] }

  const summary = conflicts.value.summary
  return {
    labels: ['Overlapping', 'Conflicts', 'Gaps'],
    datasets: [
      {
        label: 'Count',
        data: [summary.overlapping_count || 0, summary.conflict_count || 0, summary.gap_count || 0],
        backgroundColor: ['#f59e0b', '#ef4444', '#3b82f6'],
        borderRadius: 8,
      },
    ],
  }
})

const conflictsChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        precision: 0,
      },
    },
  },
}

function formatDate(dateStr: string) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

async function changePeriod(newPeriod: 'daily' | 'weekly' | 'yearly') {
  period.value = newPeriod
  await fetchStatistics(newPeriod)
}

onMounted(async () => {
  await Promise.all([fetchStatistics(period.value), fetchConflicts()])
})
</script>
