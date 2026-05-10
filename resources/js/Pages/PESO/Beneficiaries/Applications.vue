<template>
  <div class="p-6">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between gap-4">
      <button
        @click="goBack"
        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        ← Back
      </button>
      <div class="flex-1 min-w-0">
        <h1 class="text-2xl font-bold truncate">Verification of Applicants- {{ beneficiary.user.name }}</h1>
        <p class="text-gray-500 mt-1">Review beneficiary onboarding submissions</p>
      </div>
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
          <p class="text-gray-600 text-sm">Category</p>
          <p class="font-semibold capitalize">{{ beneficiary_type || 'student' }}</p>
        </div>
        <div v-if="beneficiary_type === 'student'">
          <p class="text-gray-600 text-sm">School</p>
          <p class="font-semibold">{{ beneficiary.school?.name || 'Not provided' }}</p>
        </div>
        <div v-else-if="beneficiary_type === 'osy'">
          <p class="text-gray-600 text-sm">Skills</p>
          <p class="font-semibold">{{ beneficiary.skills || 'Not provided' }}</p>
        </div>
        <div v-else-if="beneficiary_type === 'dependent'">
          <p class="text-gray-600 text-sm">Parent/Guardian Name</p>
          <p class="font-semibold">{{ beneficiary.parent_name || 'Not provided' }}</p>
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
            <p class="font-semibold">{{ doc.name || 'Document' }}</p>
            <p class="text-sm text-gray-600">{{ doc.description || '' }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ doc.uploaded_at ? 'Uploaded: ' + formatDate(doc.uploaded_at) : '' }}</p>
            <p v-if="!doc.exists" class="text-xs text-red-600 mt-1">❌ File missing or deleted</p>
          </div>
          <button 
            v-if="doc.exists && doc.url"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
            @click="openModal(doc)"
          >
            View
          </button>
          <span v-else class="text-gray-400 text-sm px-3 py-1">Unavailable</span>
        </div>
      </div>
    </div>

    <!-- Approval/Rejection Actions -->
    <div v-if="!approval_status || approval_status === 'pending'" class="bg-white shadow-md rounded-lg p-6">
      <h2 class="text-xl font-bold mb-4">Verification Actions</h2>
      <div class="space-y-4">
        <button
          class="w-full bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 font-semibold"
          @click="showApproveModal = true"
        >
          Approve Beneficiary
        </button>

        <button
          class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold"
          @click="showRejectModal = true"
        >
          Reject Beneficiary
        </button>
      </div>
    </div>

    <!-- ========== DOCUMENT MODAL ========== -->
    <Teleport to="body">
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg max-w-3xl w-full p-6 relative">
          <button @click="modalOpen = false" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">✖</button>
          <h3 class="font-bold mb-4">{{ currentDocument.name }}</h3>
          
          <iframe
            v-if="isPDF(currentDocument.url)"
            :src="currentDocument.url"
            class="w-full h-[500px] border"
          ></iframe>
          
          <img v-else-if="isImage(currentDocument.url)" :src="currentDocument.url" class="w-full h-auto" />
          
          <p v-else class="text-gray-500">Preview not available. <a :href="currentDocument.url" target="_blank" class="text-blue-600 underline">Download</a></p>
        </div>
      </div>
    </Teleport>

    <!-- ========== APPROVE MODAL ========== -->
    <Teleport to="body">
      <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
          <button @click="showApproveModal = false" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">✖</button>
          <h3 class="text-lg font-bold mb-4">Approve Beneficiary</h3>
          <p class="mb-4">Are you sure you want to approve this beneficiary?</p>
          <div class="flex justify-end gap-2">
            <button class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400" @click="showApproveModal = false">Cancel</button>
            <button class="px-4 py-2 rounded bg-green-600 text-white hover:bg-green-700" @click="confirmApprove">Approve</button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ========== REJECT MODAL ========== -->
    <Teleport to="body">
      <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 relative">
          <button @click="showRejectModal = false" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">✖</button>
          <h3 class="text-lg font-bold mb-4">Reject Beneficiary</h3>
          <label class="block text-sm font-semibold mb-2">Reason for rejection</label>
          <textarea
            v-model="rejectionReason"
            rows="3"
            class="w-full border rounded px-3 py-2 mb-4"
            placeholder="Enter reason..."
          ></textarea>
          <div class="flex justify-end gap-2">
            <button class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400" @click="showRejectModal = false">Cancel</button>
            <button class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700" :disabled="!rejectionReason.trim()" @click="confirmReject">Reject</button>
          </div>
        </div>
      </div>
    </Teleport>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  beneficiary: Object,
  documents: Array,
  submission_date: String,
  approval_status: String,
  rejection_reason: String,
  beneficiary_type: String
})

const rejectionReason = ref('')
const modalOpen = ref(false)
const currentDocument = ref({})

const showApproveModal = ref(false)
const showRejectModal = ref(false)

// ========== HELPERS ==========
const formatStatus = (status) => {
  const statusMap = { pending: 'Pending Review', approved: 'Approved', rejected: 'Rejected' }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const classMap = {
    pending: 'bg-yellow-100 border border-yellow-300',
    approved: 'bg-green-100 border border-green-300',
    rejected: 'bg-red-100 border border-red-300'
  }
  return classMap[status] || 'bg-gray-100'
}

const formatDate = (date) => {
  if (!date) return 'Not submitted'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric', month: 'long', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

// ========== DOCUMENT MODAL ==========
const openModal = (doc) => {
  currentDocument.value = doc
  modalOpen.value = true
}

const isPDF = (url) => url?.toLowerCase().endsWith('.pdf')
const isImage = (url) => ['.jpg', '.jpeg', '.png', '.gif', '.bmp', '.webp'].some(ext => url?.toLowerCase().endsWith(ext))

const goBack = () => {
  window.history.back()
}

// ========== APPROVE ==========
const confirmApprove = () => {
  showApproveModal.value = false
  router.post(route('peso.beneficiaries.approve', { id: props.beneficiary.id }))
}

// ========== REJECT ==========
const confirmReject = () => {
  showRejectModal.value = false
  router.post(route('peso.beneficiaries.reject', { id: props.beneficiary.id }), { rejection_reason: rejectionReason.value })
}
</script>
