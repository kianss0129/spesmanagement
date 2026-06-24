<script setup>
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  selectedTab: String,
  reports: {
    type: Array,
    default: () => [],
  },
  reporting: {
    type: Object,
    default: () => ({
      summary: {},
      charts: {},
      reports: {},
      insights: [],
      filters: {},
    }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  loading: Boolean,
  formatDate: {
    type: Function,
    default: (date) => date,
  },
})

const emit = defineEmits(['update:filters', 'refresh'])

const localFilters = ref({
  start_date: props.filters.start_date || '',
  end_date: props.filters.end_date || '',
  employer_id: props.filters.employer_id || '',
  school_id: props.filters.school_id || '',
  category: props.filters.category || '',
  status: props.filters.status || '',
  batch_id: props.filters.batch_id || '',
  job_listing_id: props.filters.job_listing_id || '',
})

const selectedReport = ref('company')
const chartCanvas = ref({})
const chartInstances = new Map()

const reportTabs = [
  { key: 'company', label: 'Company Report' },
  { key: 'school', label: 'School Report' },
  { key: 'category', label: 'Category Report' },
  { key: 'application', label: 'Application Report' },
  { key: 'attendance', label: 'Attendance Report' },
  { key: 'daily_report', label: 'Daily Report Report' },
  { key: 'employer_participation', label: 'Employer Participation' },
  { key: 'jobs_posted_per_employer', label: 'Jobs Posted Per Employer' },
]

const chartDefinitions = [
  ['beneficiaries_per_company', 'Beneficiaries Per Company', 'bar'],
  ['beneficiaries_per_school', 'Beneficiaries Per School', 'bar'],
  ['beneficiary_categories', 'Beneficiary Categories', 'pie'],
  ['application_status_distribution', 'Application Status Distribution', 'pie'],
  ['applications_per_month', 'Applications Per Month', 'line'],
  ['completed_beneficiaries_per_month', 'Completed Beneficiaries Per Month', 'line'],
  ['dtr_status_summary', 'DTR Status Summary', 'bar'],
  ['daily_report_status_summary', 'Daily Report Status Summary', 'bar'],
  ['jobs_posted_per_employer', 'Jobs Posted Per Employer', 'bar'],
  ['top_participating_employers', 'Top Participating Employers', 'bar'],
  ['top_schools_with_most_beneficiaries', 'Top Schools With Most Beneficiaries', 'bar'],
]

const reportColumnOrder = {
  jobs_posted_per_employer: [
    'company_name',
    'job_title',
    'location',
    'type',
    'slots',
    'closing_date',
    'date_posted',
  ],
}

const summaryCards = computed(() => {
  const summary = props.reporting?.summary || {}
  return [
    ['total_applicants', 'Total Applicants'],
    ['approved_beneficiaries', 'Approved Beneficiaries'],
    ['participating_employers', 'Participating Employers'],
    ['ongoing_beneficiaries', 'Ongoing Beneficiaries'],
    ['completed_beneficiaries', 'Completed Beneficiaries'],
    ['dtr_submitted', 'DTR Submitted'],
    ['daily_reports_submitted', 'Daily Reports'],
  ].map(([key, label]) => ({ key, label, value: Number(summary[key] || 0).toLocaleString() }))
})

const activeRows = computed(() => {
  const value = props.reporting?.reports?.[selectedReport.value]
  if (Array.isArray(value)) return value
  if (value && typeof value === 'object') return [value]
  return []
})

const activeColumns = computed(() => {
  const row = activeRows.value[0]
  if (!row) return []
  if (reportColumnOrder[selectedReport.value]) return reportColumnOrder[selectedReport.value]
  return Object.keys(row)
})

const filterOptions = computed(() => props.reporting?.filters || {})
const filteredJobOptions = computed(() => {
  const jobs = filterOptions.value.jobs || []
  const employerId = String(localFilters.value.employer_id || '')

  if (!employerId) return jobs

  return jobs.filter((job) => String(job.employer_id) === employerId)
})

watch(() => props.filters, (value) => {
  localFilters.value = { ...localFilters.value, ...(value || {}) }
}, { deep: true })

watch(() => localFilters.value.employer_id, () => {
  const selectedJobId = String(localFilters.value.job_listing_id || '')
  if (!selectedJobId) return

  const jobStillAvailable = filteredJobOptions.value.some((job) => String(job.id) === selectedJobId)
  if (!jobStillAvailable) localFilters.value.job_listing_id = ''
})

watch(() => props.reporting?.charts, () => {
  if (props.selectedTab === 'reports') renderCharts()
}, { deep: true })

watch(() => props.selectedTab, (tab) => {
  if (tab === 'reports') renderCharts()
})

onBeforeUnmount(() => {
  chartInstances.forEach((chart) => chart.destroy())
  chartInstances.clear()
})

function setChartRef(key, el) {
  if (el) chartCanvas.value[key] = el
}

function applyFilters() {
  emit('update:filters', { ...localFilters.value })
}

function clearFilters() {
  localFilters.value = {
    start_date: '',
    end_date: '',
    employer_id: '',
    school_id: '',
    category: '',
    status: '',
    batch_id: '',
    job_listing_id: '',
  }
  applyFilters()
}

function renderCharts() {
  nextTick(() => {
    chartDefinitions.forEach(([key, title, type]) => {
      const canvas = chartCanvas.value[key]
      const dataset = props.reporting?.charts?.[key] || { labels: [], data: [] }
      if (!canvas) return

      chartInstances.get(key)?.destroy()

      chartInstances.set(key, new Chart(canvas, {
        type,
        data: {
          labels: dataset.labels || [],
          datasets: [{
            label: title,
            data: dataset.data || [],
            backgroundColor: type === 'pie'
              ? ['#2563eb', '#16a34a', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2', '#64748b']
              : 'rgba(37, 99, 235, 0.72)',
            borderColor: type === 'line' ? '#2563eb' : 'rgba(37, 99, 235, 0.9)',
            borderWidth: type === 'line' ? 3 : 1,
            fill: type === 'line',
            tension: 0.35,
            borderRadius: type === 'bar' ? 8 : 0,
          }],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: type === 'pie' },
          },
          scales: type === 'pie' ? {} : {
            x: { grid: { display: false }, ticks: { color: '#64748b' } },
            y: { beginAtZero: true, ticks: { precision: 0, color: '#64748b' }, grid: { color: '#e2e8f0' } },
          },
        },
      }))
    })
  })
}

function formatLabel(key) {
  return String(key)
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (letter) => letter.toUpperCase())
}

function formatValue(value, key = '') {
  if (value === null || value === undefined || value === '') return 'N/A'
  if (typeof value === 'boolean') return value ? 'Yes' : 'No'
  if (key.includes('date') || key.includes('_at')) return props.formatDate(value)
  if (typeof value === 'number') return Number.isInteger(value) ? value.toLocaleString() : value.toLocaleString(undefined, { maximumFractionDigits: 2 })
  return value
}

function exportCsv(extension = 'csv') {
  const rows = activeRows.value
  const columns = activeColumns.value
  const reportName = reportTabs.find((tab) => tab.key === selectedReport.value)?.label || 'Report'
  const csvRows = [
    columns.map(formatLabel),
    ...rows.map((row) => columns.map((column) => csvCell(formatValue(row[column], column)))),
  ]
  const csv = csvRows.map((row) => row.join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = `${reportName.toLowerCase().replace(/\s+/g, '-')}.${extension}`
  link.click()
  URL.revokeObjectURL(url)
}

function csvCell(value) {
  const text = String(value ?? '')
  return `"${text.replace(/"/g, '""')}"`
}

function printReport() {
  window.print()
}

function hasChartData(key) {
  const data = props.reporting?.charts?.[key]?.data || []
  return data.some((value) => Number(value) > 0)
}
</script>

<template>
  <section v-if="selectedTab === 'reports'" class="print-report-area space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Reporting Center</p>
          <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Generate Reports</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
            Monitor SPES implementation, beneficiary progress, employer participation, attendance, and daily report compliance using live database records.
          </p>
        </div>

        <div class="print-hide flex flex-wrap gap-2">
          <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="exportCsv('csv')">
            Excel
          </button>
          <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="printReport">
            PDF
          </button>
          <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700" @click="printReport">
            Print
          </button>
        </div>
      </div>
    </header>

    <section class="print-hide rounded-lg border border-slate-200 bg-white p-5 shadow-sm print:hidden">
      <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
        <input v-model="localFilters.start_date" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <input v-model="localFilters.end_date" type="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select v-model="localFilters.employer_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Employers</option>
          <option v-for="employer in filterOptions.employers || []" :key="employer.id" :value="employer.id">{{ employer.name }}</option>
        </select>
        <select v-model="localFilters.school_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Schools</option>
          <option v-for="school in filterOptions.schools || []" :key="school.id" :value="school.id">{{ school.name }}</option>
        </select>
        <select v-model="localFilters.category" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Categories</option>
          <option v-for="category in filterOptions.categories || []" :key="category" :value="category">{{ category }}</option>
        </select>
        <select v-model="localFilters.status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Statuses</option>
          <option v-for="status in filterOptions.statuses || []" :key="status" :value="status">{{ formatLabel(status) }}</option>
        </select>
        <select v-model="localFilters.job_listing_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Jobs</option>
          <option v-for="job in filteredJobOptions" :key="job.id" :value="job.id">{{ job.label || job.title }}</option>
        </select>
      </div>
      <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <select v-model="localFilters.batch_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none sm:w-72">
          <option value="">All Program Batches</option>
          <option v-for="batch in filterOptions.batches || []" :key="batch.id" :value="batch.id">{{ batch.name }}</option>
        </select>
        <div class="flex gap-2">
          <button type="button" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="clearFilters">
            Clear
          </button>
          <button type="button" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-60" :disabled="loading" @click="applyFilters">
            {{ loading ? 'Loading...' : 'Apply Filters' }}
          </button>
        </div>
      </div>
    </section>

    <section class="print-summary-grid grid gap-4 sm:grid-cols-2 lg:grid-cols-4 2xl:grid-cols-7">
      <div v-for="card in summaryCards" :key="card.key" class="print-avoid-break rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ card.label }}</p>
        <p class="mt-2 text-2xl font-bold text-slate-900">{{ card.value }}</p>
      </div>
    </section>

    <section class="print-avoid-break print-report-card rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <div class="print-hide flex flex-wrap gap-2 print:hidden">
        <button
          v-for="tab in reportTabs"
          :key="tab.key"
          type="button"
          class="rounded-lg px-3 py-2 text-sm font-semibold transition"
          :class="selectedReport === tab.key ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="selectedReport = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>

      <div class="print-table-wrap mt-5 overflow-x-auto">
        <table class="print-report-table min-w-full divide-y divide-slate-200 text-sm">
          <thead class="bg-slate-50">
            <tr>
              <th v-for="column in activeColumns" :key="column" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
                {{ formatLabel(column) }}
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr v-for="(row, index) in activeRows" :key="index">
              <td v-for="column in activeColumns" :key="column" class="px-4 py-3 text-slate-700">
                {{ formatValue(row[column], column) }}
              </td>
            </tr>
          </tbody>
        </table>

        <div v-if="activeRows.length === 0" class="rounded-lg bg-slate-50 p-8 text-center">
          <p class="text-sm font-semibold text-slate-700">No report data found.</p>
          <p class="mt-1 text-sm text-slate-500">Adjust the filters or add records to generate this report.</p>
        </div>
      </div>
    </section>

    <section class="print-chart-grid grid gap-6 xl:grid-cols-2">
      <div
        v-for="[key, title] in chartDefinitions"
        :key="key"
        class="print-chart-card rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
      >
        <div class="mb-4 flex items-start justify-between gap-4">
          <div>
            <h2 class="text-base font-bold text-slate-900">{{ title }}</h2>
            <p class="mt-1 text-sm text-slate-500">Based on the current report filters.</p>
          </div>
        </div>
        <div class="print-chart-box h-72 rounded-lg bg-slate-50 p-3">
          <canvas :ref="(el) => setChartRef(key, el)" class="h-full w-full"></canvas>
          <div v-if="!hasChartData(key)" class="-mt-72 flex h-72 items-center justify-center text-sm text-slate-500">
            No chart data available.
          </div>
        </div>
      </div>
    </section>

    <section class="print-avoid-break print-report-card rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <h2 class="text-lg font-bold text-slate-900">Insights</h2>
      <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
        <div v-for="insight in reporting.insights || []" :key="insight.label" class="print-avoid-break rounded-lg bg-slate-50 p-4">
          <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">{{ insight.label }}</p>
          <p class="mt-2 text-lg font-bold text-slate-900">{{ insight.value }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ insight.meta }}</p>
        </div>
      </div>
    </section>
  </section>
</template>
