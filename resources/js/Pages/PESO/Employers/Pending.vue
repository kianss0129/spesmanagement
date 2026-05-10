<template>
  <div class="p-6 min-h-screen bg-gray-50">

    <!-- TOP BAR -->
    <div class="flex items-center gap-4 mb-4">
      <!-- BACK BUTTON -->
      <button
        @click="goBack"
        class="flex items-center gap-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded"
      >
        ← Back
      </button>

      <h1 class="text-2xl font-bold">Pending Employers</h1>
    </div>

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
          <td class="p-2 border">{{ e.user?.email ?? 'No user' }}</td>
          <td class="p-2 border">{{ e.created_at }}</td>

          <td class="p-2 border space-x-2">
            <!-- VIEW ONBOARDING -->
            <Link
              :href="route('peso.employers.applications', { employer: e.id })"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
            >
              View applicant
            </Link>

            <!-- APPROVE -->
            <button
              v-if="canApprove"
              class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700"
              @click="openApproveDialog(e.id)"
            >
              Approve
            </button>

            <!-- REJECT -->
            <button
              v-if="canApprove"
              class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700"
              @click="openRejectDialog(e.id)"
            >
              Reject
            </button>
          </td>
        </tr>

        <tr v-if="employers.length === 0">
          <td colspan="4" class="p-4 text-center text-gray-500">
            No pending employers
          </td>
        </tr>
      </tbody>
    </table>

    <!-- APPROVE MODAL -->
    <div
      v-if="showApproveDialog"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h3 class="text-lg font-bold mb-4">Approve Employer</h3>

        <p class="text-gray-700 mb-6">
          Are you sure you want to approve this employer?
        </p>

        <div class="flex justify-end gap-2">
          <button
            @click="showApproveDialog = false"
            class="px-4 py-2 border rounded hover:bg-gray-100"
          >
            Cancel
          </button>

          <button
            @click="confirmApprove"
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
          >
            Approve
          </button>
        </div>
      </div>
    </div>

    <!-- REJECT MODAL -->
    <div
      v-if="showRejectDialog"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-lg p-6 w-full max-w-md shadow-lg">
        <h3 class="text-lg font-bold mb-4">Reject Employer</h3>

        <textarea
          v-model="rejectionReason"
          class="w-full border rounded px-3 py-2 mb-4"
          rows="4"
          placeholder="Enter reason for rejection (e.g., Invalid documents, Missing requirements, etc.)..."
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
  employers: {
    type: Array,
    required: true
  },
  canApprove: {
    type: Boolean,
    default: false
  }
})

/**
 * BACK BUTTON
 */
const goBack = () => {
  window.history.back()
}

/**
 * APPROVE MODAL
 */
const showApproveDialog = ref(false)
let approveId = null

const openApproveDialog = (id) => {
  approveId = id
  showApproveDialog.value = true
}

const confirmApprove = () => {
  router.post(
    route('peso.employers.approve', { id: approveId }),
    {},
    {
      onSuccess: () => {
        showApproveDialog.value = false
        router.reload()
      }
    }
  )
}

/**
 * REJECT MODAL
 */
const showRejectDialog = ref(false)
const rejectionReason = ref('')
let rejectId = null

const openRejectDialog = (id) => {
  rejectId = id
  rejectionReason.value = ''
  showRejectDialog.value = true
}

const confirmReject = () => {
  if (!rejectionReason.value.trim()) {
    alert('Please enter a reason for rejection')
    return
  }

  router.post(
    route('peso.employers.reject', { id: rejectId }),
    {
      rejection_reason: rejectionReason.value
    },
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