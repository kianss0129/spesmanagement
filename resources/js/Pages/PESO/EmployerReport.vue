<template>
  <div class="bg-white p-4 rounded shadow">
    
    <div class="flex justify-between items-center mb-4">
      <h2 class="text-lg font-bold">Employer Report</h2>

      <button
        @click="exportCSV"
        class="bg-green-600 text-white px-3 py-1 rounded text-sm"
      >
        Export CSV
      </button>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
      <table class="w-full table-auto border">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="px-3 py-2">Company</th>
            <th class="px-3 py-2">Email</th>
            <th class="px-3 py-2">Status</th>
            <th class="px-3 py-2">Assigned Beneficiaries</th>
            <th class="px-3 py-2">Created At</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="emp in employers"
            :key="emp.id"
            class="border-b hover:bg-gray-50"
          >
            <td class="px-3 py-2 font-medium">
              {{ emp.company_name }}
            </td>

            <td class="px-3 py-2">
              {{ emp.email }}
            </td>

            <td class="px-3 py-2">
              <span
                class="px-2 py-1 text-xs rounded"
                :class="emp.status === 'Approved'
                  ? 'bg-green-100 text-green-700'
                  : 'bg-yellow-100 text-yellow-700'"
              >
                {{ emp.status }}
              </span>
            </td>

            <td class="px-3 py-2">
              {{ emp.beneficiaries_count ?? 0 }}
            </td>

            <td class="px-3 py-2 text-sm text-gray-500">
              {{ formatDate(emp.created_at) }}
            </td>
          </tr>

          <tr v-if="!employers.length">
            <td colspan="5" class="text-center py-4 text-gray-500">
              No employer data found
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const employers = ref([])

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short'
  })
}

// LOAD DATA
async function loadEmployers() {
  try {
    const res = await axios.get('/peso/employers/compliance')
    employers.value = res.data
  } catch (err) {
    console.error('Failed to load employer report', err)
  }
}

// EXPORT CSV
function exportCSV() {
  let csv = 'Company,Email,Status,Beneficiaries,Created At\n'

  employers.value.forEach(e => {
    csv += `${e.company_name},${e.email},${e.status},${e.beneficiaries_count ?? 0},${e.created_at}\n`
  })

  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)

  const a = document.createElement('a')
  a.href = url
  a.download = 'employer-report.csv'
  a.click()

  window.URL.revokeObjectURL(url)
}

onMounted(loadEmployers)
</script>