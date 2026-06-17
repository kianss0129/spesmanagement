<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Applicants (Admin/PESO Admin only) -->
    <div v-if="showApplicantsChart && !isPesoUser" class="bg-white p-4 rounded shadow h-full flex flex-col">
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

    <!-- Employers (Admin/PESO Admin only) -->
    <div v-if="showEmployersChart && !isPesoUser" class="bg-white p-4 rounded shadow h-full flex flex-col">
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

    <!-- Performance Trends (Admin only) -->
    <div v-if="showPerformanceChart && !isPesoUser" class="bg-white p-4 rounded shadow h-full flex flex-col">
      <h2 class="text-lg font-bold mb-4">Performance Trends</h2>
      <div class="flex-1">
        <canvas id="performanceChart" class="w-full h-[280px]"></canvas>
      </div>
    </div>

    <!-- User Growth Chart (Admin only) -->
    <div v-if="showGrowthChart && !isPesoUser" class="bg-white p-4 rounded shadow h-full flex flex-col">

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold">User Growth</h2>
          <div class="text-sm text-gray-500">{{ filterLabel }}</div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
          <select v-model="localDateFilter" class="border px-3 py-2 rounded text-sm">
            <option v-for="option in filterOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>

          <div v-if="localDateFilter === 'custom'" class="flex flex-col sm:flex-row gap-2 items-center">
            <input type="date" v-model="localCustomRange.start" class="border px-3 py-2 rounded text-sm" />
            <input type="date" v-model="localCustomRange.end" class="border px-3 py-2 rounded text-sm" />

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

    <!-- PESO Applications Pie Chart (Admin only) -->
    <div v-if="showPesoChart && !isPesoUser" class="bg-white p-4 rounded shadow h-full flex flex-col">
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

/* ================= PROPS ================= */
const props = defineProps({
  applicants: Object,
  employers: Object,
  performance: Object,
  stats: Object,
  chartStats: Object,

  dateFilter: { type: String, default: 'last_7_days' },
  customRange: { type: Object, default: () => ({ start: '', end: '' }) },

  showApplicantsChart: Boolean,
  showEmployersChart: Boolean,
  showPerformanceChart: Boolean,
  showGrowthChart: Boolean,
  showPesoChart: Boolean,

  canExport: Boolean,
  user: Object
})

const emit = defineEmits(['update:dateFilter', 'update:customRange'])

/* ================= ROLE CHECK ================= */
const isPesoUser = computed(() =>
  props.user?.roles?.includes('PESO')
)

/* ================= LOCAL STATE ================= */
const localDateFilter = ref(props.dateFilter)
const localCustomRange = ref({
  start: props.customRange?.start || '',
  end: props.customRange?.end || ''
})

/* ================= FILTERS ================= */
const filterOptions = [
  { value: 'today', label: 'Today' },
  { value: 'yesterday', label: 'Yesterday' },
  { value: 'last_3_days', label: 'Last 3 Days' },
  { value: 'this_week', label: 'This Week' },
  { value: 'last_week', label: 'Last Week' },
  { value: 'this_month', label: 'This Month' },
  { value: 'last_month', label: 'Last Month' },
  { value: 'ytd', label: 'Year to Date' },
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
    return `Custom range: ${localCustomRange.value.start} – ${localCustomRange.value.end}`
  }
  return filterLabels[localDateFilter.value] || ''
})

/* ================= WATCHERS ================= */
watch(localDateFilter, (val) => {
  emit('update:dateFilter', val)
})

watch(() => props.dateFilter, (val) => {
  localDateFilter.value = val
})

watch(() => props.customRange, (val) => {
  localCustomRange.value = val || { start: '', end: '' }
})

function applyCustomRange() {
  emit('update:customRange', localCustomRange.value)
  emit('update:dateFilter', 'custom')
}

/* ================= IDS ================= */
const applicantsChartId = ref('applicants-' + Math.random())
const employersChartId = ref('employers-' + Math.random())

/* ================= CHARTS ================= */
let applicantsChart, employersChart, performanceChart, growthChart, pesoChart

/* ================= APPLICANTS ================= */
function renderApplicantsChart() {
  const ctx = document.getElementById(applicantsChartId.value)?.getContext('2d')
  if (!ctx) return
  applicantsChart?.destroy()

  applicantsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.applicants?.labels || [],
      datasets: [{
        label: 'Applications',
        data: props.applicants?.data || [],
        backgroundColor: 'rgba(54,162,235,0.6)'
      }]
    }
  })
}

/* ================= EMPLOYERS ================= */
function renderEmployersChart() {
  const ctx = document.getElementById(employersChartId.value)?.getContext('2d')
  if (!ctx) return
  employersChart?.destroy()

  employersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.employers?.labels || [],
      datasets: [{
        label: 'Hires',
        data: props.employers?.data || [],
        backgroundColor: 'rgba(255,99,132,0.6)'
      }]
    }
  })
}

/* ================= PERFORMANCE ================= */
function renderPerformanceChart() {
  const ctx = document.getElementById('performanceChart')?.getContext('2d')
  if (!ctx) return
  performanceChart?.destroy()

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
    }
  })
}

/* ================= GROWTH ================= */
function renderGrowthChart() {
  const ctx = document.getElementById('growthChart')?.getContext('2d')
  if (!ctx) return
  growthChart?.destroy()

  growthChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.chartStats?.chart_dates || [],
      datasets: [{
        label: 'Users',
        data: props.chartStats?.users_growth || [],
        borderColor: 'rgba(37,99,235,0.9)',
        backgroundColor: 'rgba(37,99,235,0.2)'
      }]
    }
  })
}

/* ================= PESO ================= */
function renderPesoChart() {
  const ctx = document.getElementById('pesoChart')?.getContext('2d')
  if (!ctx) return
  pesoChart?.destroy()

  pesoChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: props.chartStats?.applications_by_peso?.map(p => p.name) || [],
      datasets: [{
        data: props.chartStats?.applications_by_peso?.map(p => p.count) || [],
        backgroundColor: [
          'rgba(99,102,241,0.7)',
          'rgba(16,185,129,0.7)',
          'rgba(244,63,94,0.7)',
          'rgba(249,115,22,0.7)'
        ]
      }]
    }
  })
}

/* ================= MOUNT ================= */
onMounted(() => {
  renderApplicantsChart()
  renderEmployersChart()

  if (!isPesoUser.value) {
    renderPerformanceChart()
    renderGrowthChart()
    renderPesoChart()
  }
})

/* ================= WATCH ================= */
watch(() => props.applicants, renderApplicantsChart, { deep: true })
watch(() => props.employers, renderEmployersChart, { deep: true })
watch(() => props.performance, renderPerformanceChart, { deep: true })
watch(() => props.chartStats, () => {
  renderGrowthChart()
  renderPesoChart()
}, { deep: true })
</script>