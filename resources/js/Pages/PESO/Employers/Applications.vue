<template>
  <div class="p-6">
    <div class="mb-6">
      <h1 class="text-2xl font-bold">Onboarding Verification - {{ employer.user.name }}</h1>
      <p class="text-gray-500 mt-1">Review employer onboarding submissions</p>
    </div>

    <!-- Status Badge -->
    <div v-if="approval_status" class="mb-6 p-4 rounded-lg" :class="getStatusClass(approval_status)">
      <p class="font-semibold">Status: {{ formatStatus(approval_status) }}</p>
      <p v-if="rejection_reason" class="text-sm mt-2">Reason: {{ rejection_reason }}</p>
    </div>

    <!-- Company Details -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Company Details</h2>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-gray-600 text-sm">Company Name</p>
          <p class="font-semibold">{{ company_details?.company_name !== 'N/A' ? company_details?.company_name : 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Contact Person</p>
          <p class="font-semibold">{{ company_details?.contact_person !== 'N/A' ? company_details?.contact_person : 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Email</p>
          <p class="font-semibold">{{ company_details?.email !== 'N/A' ? company_details?.email : 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Phone</p>
          <p class="font-semibold">{{ company_details?.phone !== 'N/A' ? company_details?.phone : 'Not provided' }}</p>
        </div>
        <div class="col-span-2">
          <p class="text-gray-600 text-sm">Address</p>
          <p class="font-semibold">{{ company_details?.address !== 'N/A' ? company_details?.address : 'Not provided' }}</p>
        </div>
        <div>
          <p class="text-gray-600 text-sm">Submission Date</p>
          <p class="font-semibold">{{ formatDate(submission_date) }}</p>
        </div>
      </div>
    </div>

    <!-- Documents -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Required Documents</h2>
      <p class="text-gray-600 text-sm mb-4">Verify the following documents:</p>
      <div v-if="!documents || documents.length === 0" class="text-gray-500">
        <p>No documents submitted</p>
      </div>
      <div v-else class="space-y-3">
        <div v-for="(doc, index) in documents" :key="index" class="border rounded-lg p-4 flex items-center justify-between">
          <div class="flex-1">
            <p class="font-semibold">{{ doc.name || 'Document ' + (index + 1) }}</p>
            <p class="text-sm text-gray-600">{{ doc.description || '' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ doc.uploaded_at ? 'Uploaded: ' + formatDate(doc.uploaded_at) : '' }}</p>
            <p v-if="!doc.exists" class="text-xs text-red-600 mt-1">❌ File missing or deleted</p>
          </div>
          <div class="flex gap-2">
            <button 
              v-if="doc.exists && doc.url"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
              @click="viewDocument(doc)"
            >
              View
            </button>
            <span v-else class="text-gray-400 text-sm">Unavailable</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Approval/Rejection Actions -->
    <div v-if="!approval_status || approval_status === 'pending'" class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">Verification Actions</h2>
      <div class="space-y-4">
        <button
          class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-semibold"
          @click="approveEmployer"
        >
          Approve Employer Onboarding
        </button>

        <div class="border rounded-lg p-4">
          <label class="block text-sm font-semibold mb-2">Reject with Reason (optional)</label>
          <textarea
            v-model="rejectionReason"
            class="w-full border rounded px-3 py-2 mb-2 text-sm"
            rows="3"
            placeholder="Enter reason for rejection (e.g., Document incomplete, Invalid DTI, etc.)..."
          ></textarea>
          <button
            class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold"
            @click="rejectEmployer"
            :disabled="!rejectionReason.trim()"
          >
            Reject Onboarding
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
  employer: {
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
  company_details: {
    type: Object,
    default: () => ({})
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
  if (doc.url) {
    window.open(doc.url, '_blank')
  } else if (doc.path) {
    window.open(doc.path, '_blank')
  }
}

const approveEmployer = () => {
  if (confirm('Are you sure you want to approve this employer onboarding?')) {
    router.post(
      route('peso.employers.approve', { id: props.employer.id }),
      {},
      {
        onSuccess: () => {
          // Page will reload
        }
      }
    )
  }
}

const rejectEmployer = () => {
  if (!rejectionReason.value.trim()) {
    alert('Please provide a rejection reason')
    return
  }

  if (confirm('Are you sure you want to reject this employer onboarding?')) {
    router.post(
      route('peso.employers.reject', { id: props.employer.id }),
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
