<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Onboarding Verification - {{ beneficiary.user.name }}</h1>
      <p class="text-gray-500 mt-1">Review beneficiary onboarding submissions</p>
    </div>

    <!-- Status Badge -->
    <div v-if="approval_status" class="mb-6 p-4 rounded-lg" :class="getStatusClass(approval_status)">
      <p class="font-semibold">Status: {{ formatStatus(approval_status) }}</p>
      <p v-if="rejection_reason" class="text-sm mt-2">Reason: {{ rejection_reason }}</p>
    </div>

    <!-- Beneficiary Information -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Beneficiary Information</h2>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-gray-600 text-sm">Name</p>
          <p class="font-semibold">{{ beneficiary.user.name }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Email</p>
          <p class="font-semibold">{{ beneficiary.user.email }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Phone</p>
          <p class="font-semibold">{{ beneficiary.phone || 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">School</p>
          <p class="font-semibold">{{ beneficiary.school?.name || 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Submission Date</p>
          <p class="font-semibold">{{ formatDate(submission_date) }}</p>
        </div>
      </div>
    </div>

    <!-- Documents -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Submitted Documents</h2>
      <div v-if="!documents || documents.length === 0" class="text-gray-500">
        <p>No documents submitted</p>
      </div>
      <div v-else class="space-y-3">
        <div v-for="(doc, index) in documents" :key="index" class="border rounded-lg p-4 flex items-center justify-between">
          <div class="flex-1">
            <p class="font-semibold">{{ doc.type || doc.name || 'Document' }}</p>
            <p class="text-sm text-gray-600">{{ doc.description || '' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ doc.uploaded_at ? 'Uploaded: ' + formatDate(doc.uploaded_at) : (doc.created_at ? 'Uploaded: ' + formatDate(doc.created_at) : '') }}</p>
          </div>
          <button 
            v-if="doc.path || doc.url"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
            @click="viewDocument(doc)"
          >
            View
          </button>
        </div>
      </div>
    </div>

    <!-- Approval/Rejection Actions -->
    <div v-if="!approval_status || approval_status === 'pending'" class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">Verification Actions</h2>
      <div class="space-y-4">
        <button
          class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-semibold"
          @click="approveBeneficiary"
        >
          Approve Beneficiary
        </button>

        <div class="border rounded-lg p-4">
          <label class="block text-sm font-semibold mb-2">Reject with Reason (optional)</label>
          <textarea
            v-model="rejectionReason"
            class="w-full border rounded px-3 py-2 mb-2 text-sm"
            rows="3"
            placeholder="Enter reason for rejection..."
          ></textarea>
          <button
            class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold"
            @click="rejectBeneficiary"
            :disabled="!rejectionReason.trim()"
          >
            Reject Beneficiary
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  beneficiary: {
    type: Object,
    required: true
  },
  documents: {
    type: Array,
    default: () => []
  },
  submission_date: {
    type: String,
    default: null
  },
  approval_status: {
    type: String,
    default: 'pending'
  },
  rejection_reason: {
    type: String,
    default: null
  }
})

const rejectionReason = ref('')

const formatStatus = (status) => {
  const statusMap = {
    'pending': 'Pending Review',
    'approved': 'Approved',
    'rejected': 'Rejected'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    'pending': 'bg-yellow-100 border border-yellow-300',
    'approved': 'bg-green-100 border border-green-300',
    'rejected': 'bg-red-100 border border-red-300'
  }
  return classMap[status] || 'bg-gray-100'
}

const formatDate = (date) => {
  if (!date) return 'Not submitted'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const viewDocument = (doc) => {
  if (doc.path || doc.url) {
    window.open(doc.path || doc.url, '_blank')
  }
}

const approveBeneficiary = () => {
  if (confirm('Are you sure you want to approve this beneficiary?')) {
    router.post(
      route('peso.beneficiaries.approve', { id: props.beneficiary.id }),
      {},
      {
        onSuccess: () => {
          // Page will reload
        }
      }
    )
  }
}

const rejectBeneficiary = () => {
  if (!rejectionReason.value.trim()) {
    alert('Please provide a rejection reason')
    return
  }

  if (confirm('Are you sure you want to reject this beneficiary?')) {
    router.post(
      route('peso.beneficiaries.reject', { id: props.beneficiary.id }),
      { rejection_reason: rejectionReason.value },
      {
        onSuccess: () => {
          // Page will reload
        }
      }
    )
  }
}
</script>
