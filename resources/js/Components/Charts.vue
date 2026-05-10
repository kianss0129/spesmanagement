<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Applicants -->
    <div v-if="showApplicantsChart" class="bg-white p-4 rounded shadow h-full flex flex-col">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Applicants by School</h2>
        <button
          v-if="canExport"
          @click="$emit('export-applicants')"
          class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs"
        >
          Export Applicants
        </button>
      </div>
      <div class="flex-1">
        <canvas :id="applicantsChartId" class="w-full h-[280px]"></canvas>
      </div>
    </div>

    <!-- Employers -->
    <div v-if="showEmployersChart" class="bg-white p-4 rounded shadow h-full flex flex-col">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Top Hiring Employers</h2>
        <button
          v-if="canExport"
          @click="$emit('export-employers')"
          class="bg-green-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs"
        >
          Export Employers
        </button>
      </div>
      <div class="flex-1">
        <canvas :id="employersChartId" class="w-full h-[280px]"></canvas>
      </div>
    </div>

    <!-- Performance -->
    <div class="bg-white p-4 rounded shadow h-full flex flex-col">
      <h2 class="text-lg font-bold mb-4">Performance Trends</h2>
      <div class="flex-1">
        <canvas id="performanceChart" class="w-full h-[280px]"></canvas>
      </div>
    </div>

   
    <!-- Growth -->
    <div v-if="showGrowthChart" class="bg-white p-4 rounded shadow h-full flex flex-col">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold">User Growth</h2>
          <div class="text-sm text-gray-500">{{ filterLabel }}</div>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <select v-model="localDateFilter" class="border px-3 py-2 rounded text-sm">
            <option
              v-for="option in filterOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <div v-if="localDateFilter === 'custom'" class="flex flex-col sm:flex-row gap-2 items-center">
            <input
              type="date"
              v-model="localCustomRange.start"
              class="border px-3 py-2 rounded text-sm"
            />
            <input
              type="date"
              v-model="localCustomRange.end"
              class="border px-3 py-2 rounded text-sm"
            />
            <button
              type="button"
              @click="applyCustomRange"
              class="bg-blue-600 text-white rounded px-3 py-2 text-sm hover:bg-blue-700"
            >
              Apply
            </button>
          </div>
        </div>
      </div>
      <div class="flex-1">
        <canvas id="growthChart" class="w-full h-[280px]"></canvas>
      </div>
    </div>

    <!-- PESO Applications -->
    <div v-if="showPesoChart" class="bg-white p-4 rounded shadow h-full flex flex-col">
      <h2 class="text-lg font-bold mb-4">PESO Applications</h2>
      <div class="flex-1">
        <canvas id="pesoChart" class="w-full h-[280px]"></canvas>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

// PROPS
const props = defineProps({
  applicants: Object,
  employers: Object,
  performance: Object,
  completion: Object,
  attendance: Object,
  stats: Object,
  chartStats: Object,
  dateFilter: { type: String, default: 'last_7_days' },
  customRange: { type: Object, default: () => ({ start: '', end: '' }) },
  showApplicantsChart: Boolean,
  showEmployersChart: Boolean,
  showGrowthChart: Boolean,
  showPesoChart: Boolean,
  canExport: Boolean
})

const emit = defineEmits(['update:dateFilter', 'update:customRange'])
const localDateFilter = ref(props.dateFilter || 'last_7_days')
const localCustomRange = ref({
  start: props.customRange?.start || '',
  end: props.customRange?.end || ''
})

const filterOptions = [
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'last_3_days', label: 'Last 3 Days' },
  { value: 'this_week', label: 'This Week' },
  { value: 'last_week', label: 'Last Week' },
  { value: 'this_month', label: 'This Month' },
  { value: 'last_month', label: 'Last Month' },
  { value: 'ytd', label: 'YTD' },
  { value: 'custom', label: 'Custom Range' }
]

const filterLabels = {
  today: 'Today',
  yesterday: 'Yesterday',
  last_3_days: 'Last 3 Days',
  this_week: 'This Week',
  last_week: 'Last Week',
  this_month: 'This Month',
  last_month: 'Last Month',
  ytd: 'Year to Date',
  custom: 'Custom Range'
}

const filterLabel = computed(() => {
  if (localDateFilter.value === 'custom') {
    if (localCustomRange.value.start && localCustomRange.value.end) {
      return `Custom range: ${localCustomRange.value.start} – ${localCustomRange.value.end}`
    }
    return 'Custom range'
  }

  return filterLabels[localDateFilter.value]
    ? `Showing ${filterLabels[localDateFilter.value]}`
    : 'Showing last 7 days'
})

watch(() => props.dateFilter, (value) => {
  if (value && value !== localDateFilter.value) {
    localDateFilter.value = value
  }
})

watch(() => props.customRange, (value) => {
  if (value) {
    localCustomRange.value = {
      start: value.start || '',
      end: value.end || ''
    }
  }
})

watch(localDateFilter, (value) => {
  emit('update:dateFilter', value)
  if (value !== 'custom') {
    emit('update:customRange', { start: '', end: '' })
  }
})

function applyCustomRange() {
  if (!localCustomRange.value.start || !localCustomRange.value.end) return
  if (localCustomRange.value.start > localCustomRange.value.end) return

  emit('update:customRange', {
    start: localCustomRange.value.start,
    end: localCustomRange.value.end
  })
  emit('update:dateFilter', 'custom')
}

// IDS
const applicantsChartId = ref('applicants-' + Math.random())
const employersChartId = ref('employers-' + Math.random())

// CHART INSTANCES
let applicantsChart, employersChart, performanceChart
let completionChart, attendanceChart, growthChart, pesoChart

// ================= APPLICANTS =================
function renderApplicantsChart() {
  const ctx = document.getElementById(applicantsChartId.value)?.getContext('2d')
  if (!ctx) return
  if (applicantsChart) applicantsChart.destroy()

  applicantsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.applicants?.labels || [],
      datasets: [{
        label: 'Applications',
        data: props.applicants?.data || [],
        backgroundColor: 'rgba(54,162,235,0.6)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= EMPLOYERS =================
function renderEmployersChart() {
  const ctx = document.getElementById(employersChartId.value)?.getContext('2d')
  if (!ctx) return
  if (employersChart) employersChart.destroy()

  employersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.employers?.labels || [],
      datasets: [{
        label: 'Hires',
        data: props.employers?.data || [],
        backgroundColor: 'rgba(255,99,132,0.6)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= PERFORMANCE =================
function renderPerformanceChart() {
  const ctx = document.getElementById('performanceChart')?.getContext('2d')
  if (!ctx) return
  if (performanceChart) performanceChart.destroy()

  performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.performance?.labels || [],
      datasets: (props.performance?.series || []).map(s => ({
        label: s.name,
        data: s.data,
        borderWidth: 2,
        fill: false
      }))
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= COMPLETION =================
function renderCompletionChart() {
  const ctx = document.getElementById('completionChart')?.getContext('2d')
  if (!ctx) return
  if (completionChart) completionChart.destroy()

  completionChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.completion?.labels || [],
      datasets: [{
        label: 'Completion %',
        data: props.completion?.data || [],
        backgroundColor: 'rgba(75,192,192,0.6)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= ATTENDANCE =================
function renderAttendanceChart() {
  const ctx = document.getElementById('attendanceChart')?.getContext('2d')
  if (!ctx) return
  if (attendanceChart) attendanceChart.destroy()

  attendanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.attendance?.labels || [],
      datasets: [{
        label: 'Attendance %',
        data: props.attendance?.data || [],
        backgroundColor: 'rgba(255,205,86,0.6)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= GROWTH =================
function renderGrowthChart() {
  const ctx = document.getElementById('growthChart')?.getContext('2d')
  if (!ctx) return
  if (growthChart) growthChart.destroy()

  growthChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.chartStats?.chart_dates || [],
      datasets: [{
        label: 'Users',
        data: props.chartStats?.users_growth || [],
        borderColor: 'rgba(37, 99, 235, 0.9)',
        backgroundColor: 'rgba(37, 99, 235, 0.2)',
        tension: 0.35,
        pointRadius: 4,
        pointBackgroundColor: 'rgba(37, 99, 235, 1)'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

// ================= PESO =================
function renderPesoChart() {
  const ctx = document.getElementById('pesoChart')?.getContext('2d')
  if (!ctx) return
  if (pesoChart) pesoChart.destroy()

  pesoChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: props.chartStats?.applications_by_peso?.map(p => p.name) || [],
      datasets: [{
        data: props.chartStats?.applications_by_peso?.map(p => p.count) || [],
        backgroundColor: [
          'rgba(99, 102, 241, 0.7)',
          'rgba(16, 185, 129, 0.7)',
          'rgba(244, 63, 94, 0.7)',
          'rgba(249, 115, 22, 0.7)'
        ]
      }]
    }
  })
}

// ================= MOUNT =================
onMounted(() => {
  renderApplicantsChart()
  renderEmployersChart()
  renderPerformanceChart()
  renderCompletionChart()
  renderAttendanceChart()
  if (props.showGrowthChart) renderGrowthChart()
  if (props.showPesoChart) renderPesoChart()
})

// ================= WATCH =================
watch(() => props.applicants, renderApplicantsChart, { deep: true })
watch(() => props.employers, renderEmployersChart, { deep: true })
watch(() => props.performance, renderPerformanceChart, { deep: true })
watch(() => props.completion, renderCompletionChart, { deep: true })
watch(() => props.attendance, renderAttendanceChart, { deep: true })
watch(() => props.chartStats, () => {
  renderGrowthChart()
  renderPesoChart()
}, { deep: true })
</script>