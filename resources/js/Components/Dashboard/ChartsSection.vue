<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Applicants -->
    <div
      v-if="showApplicantsChart && !isPesoUser"
      class="bg-white/90 backdrop-blur-lg border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col"
    >
      <div class="flex items-center justify-between mb-5">
        <div>
          <h2 class="text-xl font-bold text-gray-800">
            Applicants by School
          </h2>
          <p class="text-sm text-gray-500">
            School application overview
          </p>
        </div>

        <button
          v-if="canExport"
          @click="$emit('export-applicants')"
          class="bg-gradient-to-r from-green-500 to-green-600 hover:scale-105 hover:shadow-lg text-white px-4 py-2 rounded-xl text-sm transition-all duration-300"
        >
          Export
        </button>
      </div>

      <div class="bg-gray-50 rounded-2xl p-4 flex-1">
        <canvas :id="applicantsChartId" class="w-full h-[300px]"></canvas>
      </div>
    </div>

    <!-- Employers -->
    <div
      v-if="showEmployersChart && !isPesoUser"
      class="bg-white/90 backdrop-blur-lg border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col"
    >
      <div class="flex items-center justify-between mb-5">
        <div>
          <h2 class="text-xl font-bold text-gray-800">
            Top Hiring Employers
          </h2>
          <p class="text-sm text-gray-500">
            Most active employers
          </p>
        </div>

        <button
          v-if="canExport"
          @click="$emit('export-employers')"
          class="bg-gradient-to-r from-blue-500 to-blue-600 hover:scale-105 hover:shadow-lg text-white px-4 py-2 rounded-xl text-sm transition-all duration-300"
        >
          Export
        </button>
      </div>

      <div class="bg-gray-50 rounded-2xl p-4 flex-1">
        <canvas :id="employersChartId" class="w-full h-[300px]"></canvas>
      </div>
    </div>

    <!-- Performance -->
    <div
      v-if="showPerformanceChart && !isPesoUser"
      class="bg-white/90 backdrop-blur-lg border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col"
    >
      <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-800">
          Performance Trends
        </h2>
        <p class="text-sm text-gray-500">
          System performance analytics
        </p>
      </div>

      <div class="bg-gray-50 rounded-2xl p-4 flex-1">
        <canvas id="performanceChart" class="w-full h-[300px]"></canvas>
      </div>
    </div>

    <!-- Growth -->
    <div
      v-if="showGrowthChart && !isPesoUser"
      class="bg-white/90 backdrop-blur-lg border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col"
    >
      <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between mb-5">

        <div>
          <h2 class="text-xl font-bold text-gray-800">
            User Growth
          </h2>

          <div
            class="inline-flex items-center mt-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold"
          >
            {{ filterLabel }}
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">

          <select
            v-model="localDateFilter"
            class="border border-gray-200 bg-white rounded-xl px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 outline-none shadow-sm"
          >
            <option
              v-for="option in filterOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <div
            v-if="localDateFilter === 'custom'"
            class="flex flex-col sm:flex-row gap-2"
          >
            <input
              type="date"
              v-model="localCustomRange.start"
              class="border border-gray-200 rounded-xl px-3 py-2 text-sm shadow-sm"
            />

            <input
              type="date"
              v-model="localCustomRange.end"
              class="border border-gray-200 rounded-xl px-3 py-2 text-sm shadow-sm"
            />

            <button
              type="button"
              @click="applyCustomRange"
              class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl px-4 py-2 text-sm hover:scale-105 transition-all duration-300"
            >
              Apply
            </button>
          </div>
        </div>
      </div>

      <div class="bg-gray-50 rounded-2xl p-4 flex-1">
        <canvas id="growthChart" class="w-full h-[300px]"></canvas>
      </div>
    </div>

    <!-- PESO -->
    <div
      v-if="showPesoChart && !isPesoUser"
      class="bg-white/90 backdrop-blur-lg border border-gray-100 rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 p-6 flex flex-col"
    >
      <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-800">
          PESO Applications
        </h2>

        <p class="text-sm text-gray-500">
          Application distribution
        </p>
      </div>

      <div class="bg-gray-50 rounded-2xl p-4 flex-1">
        <canvas id="pesoChart" class="w-full h-[300px]"></canvas>
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

const emit = defineEmits([
  'update:dateFilter',
  'update:customRange'
])

/* ================= ROLE ================= */
const isPesoUser = computed(() =>
  props.user?.roles?.includes('PESO')
)

/* ================= STATE ================= */
const localDateFilter = ref(props.dateFilter)

const localCustomRange = ref({
  start: props.customRange?.start || '',
  end: props.customRange?.end || ''
})

/* ================= FILTER OPTIONS ================= */
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
    return `Custom: ${localCustomRange.value.start} - ${localCustomRange.value.end}`
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
  localCustomRange.value = val || {
    start: '',
    end: ''
  }
})

function applyCustomRange() {
  emit('update:customRange', localCustomRange.value)
  emit('update:dateFilter', 'custom')
}

/* ================= IDS ================= */
const applicantsChartId = ref('applicants-' + Math.random())
const employersChartId = ref('employers-' + Math.random())

/* ================= CHART INSTANCES ================= */
let applicantsChart
let employersChart
let performanceChart
let growthChart
let pesoChart

/* ================= COMMON OPTIONS ================= */
const commonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      labels: {
        color: '#374151',
        font: {
          size: 13
        }
      }
    }
  },
  scales: {
    x: {
      ticks: {
        color: '#6B7280'
      },
      grid: {
        display: false
      }
    },
    y: {
      ticks: {
        color: '#6B7280'
      },
      grid: {
        color: 'rgba(229,231,235,0.5)'
      }
    }
  }
}

/* ================= APPLICANTS ================= */
function renderApplicantsChart() {
  const ctx = document
    .getElementById(applicantsChartId.value)
    ?.getContext('2d')

  if (!ctx) return

  applicantsChart?.destroy()

  applicantsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.applicants?.labels || [],
      datasets: [{
        label: 'Applications',
        data: props.applicants?.data || [],
        backgroundColor: 'rgba(59,130,246,0.7)',
        borderRadius: 12
      }]
    },
    options: commonOptions
  })
}

/* ================= EMPLOYERS ================= */
function renderEmployersChart() {
  const ctx = document
    .getElementById(employersChartId.value)
    ?.getContext('2d')

  if (!ctx) return

  employersChart?.destroy()

  employersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.employers?.labels || [],
      datasets: [{
        label: 'Hires',
        data: props.employers?.data || [],
        backgroundColor: 'rgba(236,72,153,0.7)',
        borderRadius: 12
      }]
    },
    options: commonOptions
  })
}

/* ================= PERFORMANCE ================= */
function renderPerformanceChart() {
  const ctx = document
    .getElementById('performanceChart')
    ?.getContext('2d')

  if (!ctx) return

  performanceChart?.destroy()

  performanceChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.performance?.labels || [],
      datasets: (props.performance?.series || []).map(s => ({
        label: s.name,
        data: s.data,
        borderWidth: 3,
        tension: 0.4,
        fill: false
      }))
    },
    options: commonOptions
  })
}

/* ================= GROWTH ================= */
function renderGrowthChart() {
  const ctx = document
    .getElementById('growthChart')
    ?.getContext('2d')

  if (!ctx) return

  growthChart?.destroy()

  growthChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.chartStats?.chart_dates || [],
      datasets: [{
        label: 'Users',
        data: props.chartStats?.users_growth || [],
        borderColor: 'rgba(37,99,235,1)',
        backgroundColor: 'rgba(37,99,235,0.15)',
        fill: true,
        tension: 0.4,
        borderWidth: 3
      }]
    },
    options: commonOptions
  })
}

/* ================= PESO ================= */
function renderPesoChart() {
  const ctx = document
    .getElementById('pesoChart')
    ?.getContext('2d')

  if (!ctx) return

  pesoChart?.destroy()

  pesoChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: props.chartStats?.applications_by_peso?.map(p => p.name) || [],
      datasets: [{
        data: props.chartStats?.applications_by_peso?.map(p => p.count) || [],
        backgroundColor: [
          'rgba(99,102,241,0.8)',
          'rgba(16,185,129,0.8)',
          'rgba(244,63,94,0.8)',
          'rgba(249,115,22,0.8)'
        ],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '65%'
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