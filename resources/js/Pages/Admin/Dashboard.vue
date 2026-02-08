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
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">PESO Users</div>
            <div class="text-2xl font-bold">{{ stats.peso_users ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Assigned Beneficiaries</div>
            <div class="text-2xl font-bold">{{ stats.assigned_beneficiaries ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Upcoming Interviews</div>
            <div class="text-2xl font-bold">{{ stats.upcoming_interviews ?? 0 }}</div>
          </div>
        </div>

        <!-- Quick Links / Pending Approvals -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded shadow flex flex-col justify-between">
            <div>
              <div class="text-gray-500 mb-2">Pending Beneficiaries</div>
              <div class="text-2xl font-bold">{{ stats.pending_beneficiaries ?? 0 }}</div>
            </div>
            <a href="/peso/beneficiaries/pending"
              class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
              View & Approve
            </a>
          </div>

          <div class="bg-white p-4 rounded shadow flex flex-col justify-between">
            <div>
              <div class="text-gray-500 mb-2">Pending Employers</div>
              <div class="text-2xl font-bold">{{ stats.pending_employers ?? 0 }}</div>
            </div>
            <a href="/peso/employers/pending"
              class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-center">
              View & Approve
            </a>
          </div>
        </div>

        <!-- Secondary stats row -->
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="bg-white p-4 rounded shadow">
            <div class="text-gray-500">Pending Applications</div>
            <div class="text-2xl font-bold">{{ stats.pending_applications ?? 0 }}</div>
          </div>
          <div class="bg-white p-4 rounded shadow flex items-center justify-between">
            <div>
              <div class="text-gray-500">Growth Window</div>
              <div class="text-sm text-gray-700">Showing last <strong>{{ selectedDays }}</strong> days</div>
            </div>
            <div>
              <select v-model="selectedDays" @change="loadStats" class="border p-2 rounded">
                <option value="7">7 days</option>
                <option value="30">30 days</option>
                <option value="90">90 days</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Growth Chart -->
        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">User Growth (Last {{ selectedDays }} days)</h2>
          <canvas id="growthChart"></canvas>
        </div>

        <!-- Applications by PESO -->
        <div class="bg-white p-4 rounded shadow">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Applications Assigned by PESO</h2>
            <div>
              <button @click="exportUsers" class="bg-blue-600 text-white px-3 py-2 rounded mr-2">Export Users CSV</button>
              <button @click="refresh" class="bg-gray-200 px-3 py-2 rounded">Refresh</button>
            </div>
          </div>
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

        <!-- Recent Activity -->
        <div class="bg-white p-4 rounded shadow">
          <h2 class="text-lg font-bold mb-4">Recent Activity</h2>
          <ul class="text-sm space-y-2">
            <li v-if="stats.recent_activity && stats.recent_activity.length === 0" class="text-gray-500">No recent activity</li>
            <li v-for="a in stats.recent_activity" :key="a.id" class="border p-2 rounded">
              <div class="text-xs text-gray-500">{{ a.created_at }}</div>
              <div class="text-sm">{{ a.description }}</div>
              <div class="text-xs text-gray-400">By: {{ a.causer_name ?? a.causer_id ?? 'system' }}</div>
            </li>
          </ul>

          <div class="mt-4 flex space-x-2">
            <a v-if="$page.props.auth.user && $page.props.auth.user.roles && $page.props.auth.user.roles.find(r => r.name === 'Admin')"
              href="/admin/roles" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700">Manage Roles</a>
            <button @click="refresh" class="bg-gray-200 px-4 py-2 rounded">Refresh</button>
          </div>
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
    applications_by_peso: [],
    assigned_beneficiaries: 0,
    upcoming_interviews: 0,
    pending_applications: 0,
    pending_beneficiaries: 0,
    pending_employers: 0,
    recent_activity: []
  })

  const selectedDays = ref(7)

  async function logout() {
    Inertia.post('/logout')
  }

  function exportUsers(){
    window.location.href = '/admin/export-users'
  }

  function refresh(){
    loadStats()
  }

  async function loadStats(){
    try{
      const res = await axios.get('/admin/stats', { params: { days: selectedDays.value } })
      stats.value = res.data

      // update UI
      renderGrowthChart()
      renderPesoChart()
    }catch(e){
      console.error('Failed to load admin stats', e)
    }
  }

  function renderGrowthChart(){
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
  }

  function renderPesoChart(){
    const ctxPeso = document.getElementById('pesoChart')?.getContext('2d')
    if(!ctxPeso) return
    new Chart(ctxPeso, {
      type: 'bar',
      data: {
        labels: stats.value.applications_by_peso.map(item => `PESO ${item.causer_id ?? item.peso_id ?? 'N/A'}`),
        datasets: [{
          label: 'Applications',
          data: stats.value.applications_by_peso.map(item => item.total ?? 0),
          backgroundColor: '#6366F1'
        }]
      },
      options: { responsive: true, plugins: { legend: { display: false } } }
    })
  }

  onMounted(async () => {
    // Load stats and render charts
    await loadStats()
  })
  </script>
