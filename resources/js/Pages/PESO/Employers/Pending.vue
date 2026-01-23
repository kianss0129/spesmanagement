<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <h1 class="text-2xl font-bold mb-4">Pending Employers</h1>

    <table class="w-full border rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">Company Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Submitted At</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="e in employers" :key="e.id">
          <td class="p-2 border">{{ e.company_name }}</td>
          
          <!-- Safe access to user email -->
          <td class="p-2 border">
            {{ e.user?.email ?? 'No user' }}
          </td>
          
          <td class="p-2 border">{{ e.created_at }}</td>
          <td class="p-2 border space-x-2">
            <button
              v-if="canApprove"
              class="bg-green-600 text-white px-3 py-1 rounded"
              @click="approve(e.id)"
            >
              Approve
            </button>
            <button
              v-if="canApprove"
              class="bg-red-600 text-white px-3 py-1 rounded"
              @click="reject(e.id)"
            >
              Reject
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'

// Props from Inertia
const props = defineProps({
  employers: {
    type: Array,
    required: true
  },
  canApprove: {
    type: Boolean,
    default: false
  }
})

// Approve an employer
const approve = (id) => {
  if (confirm('Approve this employer?')) {
    router.post(route('peso.employers.approve', id), {}, {
      onSuccess: () => router.reload()
    })
  }
}

// Reject an employer
const reject = (id) => {
  if (confirm('Reject this employer?')) {
    router.post(route('peso.employers.reject', id), {}, {
      onSuccess: () => router.reload()
    })
  }
}
</script>

<style scoped>
/* Optional: Add nice table styling */
table th, table td {
  text-align: left;
}
</style>
  