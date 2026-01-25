<template>
  <div class="p-6 min-h-screen bg-gray-50">
    <h1 class="text-2xl font-bold mb-4">Pending Beneficiaries</h1>

    <table class="w-full border rounded">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">Name</th>
          <th class="p-2 border">Email</th>
          <th class="p-2 border">Submitted At</th>
          <th class="p-2 border">Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="b in beneficiaries" :key="b.id">
          <td class="p-2 border">{{ b.user?.name ?? 'N/A' }}</td>
          <td class="p-2 border">{{ b.user?.email ?? 'N/A' }}</td>
          <td class="p-2 border">{{ b.onboarding_completed_at ?? 'N/A' }}</td>

          <td class="p-2 border space-x-2">
            <!-- VIEW APPLICATIONS -->
            <Link
              :href="route('peso.beneficiaries.applications', { beneficiary: b.id })"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
            >
              View Applications
            </Link>

            <!-- APPROVE -->
            <button
              v-if="canApprove"
              class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
              @click="approve(b.id)"
            >
              Approve
            </button>

            <!-- REJECT -->
            <button
              v-if="canApprove"
              class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
              @click="reject(b.id)"
            >
              Reject
            </button>
          </td>
        </tr>

        <tr v-if="beneficiaries.length === 0">
          <td colspan="4" class="p-4 text-center text-gray-500">
            No pending beneficiaries
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js' // ✅ named import, NOT default

defineProps({
  beneficiaries: {
    type: Array,
    required: true
  },
  canApprove: {
    type: Boolean,
    default: false
  }
})

// Approve beneficiary
const approve = (id) => {
  if (confirm('Approve this beneficiary?')) {
    router.post(route('peso.beneficiaries.approve', { id }), {}, {
      onSuccess: () => router.reload()
    })
  }
}

// Reject beneficiary
const reject = (id) => {
  if (confirm('Reject this beneficiary?')) {
    router.post(route('peso.beneficiaries.reject', { id }), {}, {
      onSuccess: () => router.reload()
    })
  }
}
</script>

<style scoped>
table th,
table td {
  text-align: left;
}
</style>
