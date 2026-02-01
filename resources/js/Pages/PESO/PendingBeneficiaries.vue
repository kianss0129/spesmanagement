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
            <!-- VIEW ONBOARDING -->
            <Link
              :href="route('peso.beneficiaries.applications', { beneficiary: b.id })"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
            >
              View Onboarding
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
              @click="openRejectDialog(b.id)"
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

    <!-- Rejection Reason Dialog -->
    <div v-if="showRejectDialog" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Reject Beneficiary</h3>
        <textarea
          v-model="rejectionReason"
          class="w-full border rounded px-3 py-2 mb-4"
          rows="4"
          placeholder="Enter reason for rejection..."
        ></textarea>
        <div class="flex gap-2 justify-end">
          <button
            @click="showRejectDialog = false"
            class="px-4 py-2 border rounded hover:bg-gray-100"
          >
            Cancel
          </button>
          <button
            @click="confirmReject"
            :disabled="!rejectionReason.trim()"
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 disabled:opacity-50"
          >
            Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

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

const showRejectDialog = ref(false)
const rejectionReason = ref('')
let rejectId = null

// Approve beneficiary
const approve = (id) => {
  if (confirm('Approve this beneficiary?')) {
    router.post(route('peso.beneficiaries.approve', { id }), {}, {
      onSuccess: () => router.reload()
    })
  }
}

// Open rejection dialog
const openRejectDialog = (id) => {
  rejectId = id
  rejectionReason.value = ''
  showRejectDialog.value = true
}

// Confirm rejection
const confirmReject = () => {
  if (!rejectionReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  router.post(
    route('peso.beneficiaries.reject', { id: rejectId }),
    { rejection_reason: rejectionReason.value },
    {
      onSuccess: () => {
        showRejectDialog.value = false
        router.reload()
      }
    }
  )
}
</script>

<style scoped>
table th,
table td {
  text-align: left;
}
</style>
