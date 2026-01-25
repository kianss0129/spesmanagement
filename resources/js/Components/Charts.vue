<template>
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div v-if="showApplicantsChart" class="bg-white p-4 rounded shadow hover:shadow-md transition min-h-[250px]">
      <h2 class="text-lg font-bold mb-4">Applicants by School</h2>
      <canvas :id="applicantsChartId"></canvas>
    </div>

    <div v-if="showEmployersChart" class="bg-white p-4 rounded shadow hover:shadow-md transition min-h-[250px]">
      <h2 class="text-lg font-bold mb-4">Top Hiring Employers</h2>
      <canvas :id="employersChartId"></canvas>
    </div>

    <div v-if="showGrowthChart" class="bg-white p-4 rounded shadow">
      <h2 class="text-lg font-bold mb-4">User Growth (Last {{ selectedDays }} days)</h2>
      <canvas id="growthChart"></canvas>
    </div>

    <div v-if="showPesoChart" class="bg-white p-4 rounded shadow">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold">Applications Assigned by PESO</h2>
        <div v-if="canExport">
          <button @click="exportUsers" class="bg-blue-600 text-white px-3 py-2 rounded mr-2">Export Users CSV</button>
          <button @click="refresh" class="bg-gray-200 px-3 py-2 rounded">Refresh</button>
        </div>
      </div>
      <canvas id="pesoChart"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  applicants: Object,
  employers: Object,
  stats: Object,
  selectedDays: Number,
  showApplicantsChart: { type: Boolean, default: true },
  showEmployersChart: { type: Boolean, default: true },
  showGrowthChart: { type: Boolean, default: false },
  showPesoChart: { type: Boolean, default: false },
  canExport: { type: Boolean, default: false }
})

const emit = defineEmits(['exportUsers', 'refresh'])

const applicantsChartId = ref('applicantsChart-' + Math.random())
const employersChartId = ref('employersChart-' + Math.random())

let applicantsChart = null
let employersChart = null
let growthChart = null
let pesoChart = null

function renderApplicantsChart() {
  const ctx = document.getElementById(applicantsChartId.value)?.getContext('2d')
  if (!ctx) return
  if (applicantsChart) applicantsChart.destroy()
  applicantsChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.applicants.labels,
      datasets: [{
        label: 'Applications',
        data: props.applicants.data,
        backgroundColor: 'rgba(54, 162, 235, 0.5)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

function renderEmployersChart() {
  const ctx = document.getElementById(employersChartId.value)?.getContext('2d')
  if (!ctx) return
  if (employersChart) employersChart.destroy()
  employersChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: props.employers.labels,
      datasets: [{
        label: 'Hires',
        data: props.employers.data,
        backgroundColor: 'rgba(255, 99, 132, 0.5)',
        borderColor: 'rgba(255, 99, 132, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

function renderGrowthChart() {
  const ctx = document.getElementById('growthChart')?.getContext('2d')
  if (!ctx) return
  if (growthChart) growthChart.destroy()
  growthChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: props.stats.chart_dates,
      datasets: [
        {
          label: 'Users',
          data: props.stats.users_growth,
          borderColor: 'rgba(75, 192, 192, 1)',
          backgroundColor: 'rgba(75, 192, 192, 0.2)',
          tension: 0.1
        },
        {
          label: 'Beneficiaries',
          data: props.stats.beneficiaries_growth,
          borderColor: 'rgba(153, 102, 255, 1)',
          backgroundColor: 'rgba(153, 102, 255, 0.2)',
          tension: 0.1
        },
        {
          label: 'Employers',
          data: props.stats.employers_growth,
          borderColor: 'rgba(255, 159, 64, 1)',
          backgroundColor: 'rgba(255, 159, 64, 0.2)',
          tension: 0.1
        }
      ]
    },
    options: {
      responsive: true,
      scales: {
        y: { beginAtZero: true }
      }
    }
  })
}

function renderPesoChart() {
  const ctx = document.getElementById('pesoChart')?.getContext('2d')
  if (!ctx) return
  if (pesoChart) pesoChart.destroy()
  pesoChart = new Chart(ctx, {
    type: 'pie',
    data: {
      labels: props.stats.applications_by_peso?.map(p => p.name) || [],
      datasets: [{
        data: props.stats.applications_by_peso?.map(p => p.count) || [],
        backgroundColor: [
          'rgba(255, 99, 132, 0.8)',
          'rgba(54, 162, 235, 0.8)',
          'rgba(255, 205, 86, 0.8)',
          'rgba(75, 192, 192, 0.8)',
          'rgba(153, 102, 255, 0.8)'
        ]
      }]
    },
    options: {
      responsive: true
    }
  })
}

function exportUsers() {
  emit('exportUsers')
}

function refresh() {
  emit('refresh')
}

onMounted(() => {
  if (props.showApplicantsChart) renderApplicantsChart()
  if (props.showEmployersChart) renderEmployersChart()
  if (props.showGrowthChart) renderGrowthChart()
  if (props.showPesoChart) renderPesoChart()
})

watch(() => props.applicants, renderApplicantsChart, { deep: true })
watch(() => props.employers, renderEmployersChart, { deep: true })
watch(() => props.stats, () => {
  if (props.showGrowthChart) renderGrowthChart()
  if (props.showPesoChart) renderPesoChart()
}, { deep: true })
</script>