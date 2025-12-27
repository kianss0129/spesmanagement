<template>
  <div class="relative p-6 min-h-screen bg-gray-50">

    <!-- Logout Button in Top Right -->
    <div class="absolute top-6 right-6">
      <form @submit.prevent="logout" method="POST">
        <button type="submit"
          class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition">
          Logout
        </button>
      </form>
    </div>

    <div class="max-w-6xl mx-auto space-y-6">

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Total Users</div>
          <div class="text-2xl font-bold">{{ stats.users }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Total Beneficiaries</div>
          <div class="text-2xl font-bold">{{ stats.beneficiaries }}</div>
        </div>
        <div class="bg-white p-4 rounded shadow">
          <div class="text-gray-500">Total Employers</div>
          <div class="text-2xl font-bold">{{ stats.employers }}</div>
        </div>
      </div>

      <!-- Growth Chart -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">User Growth Last 7 Days</h2>
        <canvas id="growthChart"></canvas>
      </div>

      <!-- Applications by PESO -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Applications Assigned by PESO</h2>
        <canvas id="pesoChart"></canvas>
      </div>

      <!-- Latest Users Table -->
      <div class="bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Latest Users</h2>
        <table class="w-full table-auto">
          <thead>
            <tr class="bg-gray-100">
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Name</th>
              <th class="px-4 py-2 text-left">Email</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in stats.latest_users" :key="user.id" class="border-b">
              <td class="px-4 py-2">{{ user.id }}</td>
              <td class="px-4 py-2">{{ user.name }}</td>
              <td class="px-4 py-2">{{ user.email }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Quick Links -->
      <div class="bg-white p-4 rounded shadow flex space-x-4">
        <router-link to="/admin/roles"
          class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">
          Manage Roles
        </router-link>
        <router-link to="/admin/dashboard"
          class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700">
          Refresh Dashboard
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { Chart, registerables } from 'chart.js'
import { Inertia } from '@inertiajs/inertia'

Chart.register(...registerables)

const stats = ref({
  users: 0,
  beneficiaries: 0,
  employers: 0,
  latest_users: [],
  chart_dates: [],
  users_growth: [],
  beneficiaries_growth: [],
  employers_growth: [],
  applications_by_peso: []
})

async function logout() {
  Inertia.post('/logout')
}

onMounted(async () => {
  try {
    const res = await axios.get('/admin/stats')
    stats.value = res.data

    // User Growth Chart
    const ctxGrowth = document.getElementById('growthChart').getContext('2d')
    new Chart(ctxGrowth, {
      type: 'line',
      data: {
        labels: stats.value.chart_dates,
        datasets: [
          { label: 'Users', data: stats.value.users_growth, borderColor: '#3B82F6', fill: false },
          { label: 'Beneficiaries', data: stats.value.beneficiaries_growth, borderColor: '#10B981', fill: false },
          { label: 'Employers', data: stats.value.employers_growth, borderColor: '#F59E0B', fill: false }
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'top' } } }
    })

    // Applications by PESO Chart
    const ctxPeso = document.getElementById('pesoChart').getContext('2d')
    new Chart(ctxPeso, {
      type: 'bar',
      data: {
        labels: stats.value.applications_by_peso.map(item => `PESO ${item.peso_id}`),
        datasets: [{
          label: 'Applications',
          data: stats.value.applications_by_peso.map(item => item.total),
          backgroundColor: '#6366F1'
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    })

  } catch (e) {
    console.error('Failed to load admin stats', e)
  }
})
</script>
