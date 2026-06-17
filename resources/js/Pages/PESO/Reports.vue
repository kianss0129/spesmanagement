<template>
  <div class="p-6 bg-gray-50 min-h-screen">

    <h1 class="text-2xl font-bold mb-4">
      Employer Reports
    </h1>

    <div class="bg-white rounded shadow p-4 overflow-x-auto">
      <table class="w-full table-auto">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-4 py-2 text-left">Title</th>
            <th class="px-4 py-2 text-left">Body</th>
            <th class="px-4 py-2 text-left">Employer</th>
            <th class="px-4 py-2 text-left">Document</th>
            <th class="px-4 py-2 text-left">Date</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="r in reports" :key="r.id" class="border-b">
            <td class="px-4 py-2">{{ r.title }}</td>
            <td class="px-4 py-2">{{ r.body }}</td>
            <td class="px-4 py-2">{{ r.employer_name || 'Unknown Employer' }}</td>
            <td class="px-4 py-2">
              <a
                v-if="r.file_url"
                :href="r.file_url"
                target="_blank"
                rel="noopener noreferrer"
                class="text-blue-600 hover:text-blue-800 underline"
              >
                View
              </a>
              <span v-else class="text-gray-500">No file</span>
            </td>
            <td class="px-4 py-2">{{ formatDate(r.created_at) }}</td>
          </tr>

          <tr v-if="reports.length === 0">
            <td colspan="5" class="text-gray-500 px-4 py-3">
              No reports found
            </td>
          </tr>
        </tbody>

      </table>
    </div>

  </div>
</template>

<script setup>
defineProps({
  reports: Array
})

function formatDate(v) {
  if (!v) return ''
  return new Date(v).toLocaleString()
}
</script>