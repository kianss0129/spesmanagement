<template>
  <div class="p-6">

    <!-- ================= HEADER ================= -->
    <div class="mb-6 flex items-center justify-between gap-4">
      <button
        @click="goBack"
        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        ← Back
      </button>
      <div class="flex-1 min-w-0">
        <h1 class="text-2xl font-bold truncate">
          Verification of Applicants- {{ employer.user.name }}
        </h1>
        <p class="text-gray-500 mt-1">
          Review employer onboarding submissions
        </p>
      </div>
    </div>

    <!-- ================= STATUS ================= -->
    <div
      v-if="approval_status"
      class="mb-6 p-4 rounded-lg"
      :class="getStatusClass(approval_status)"
    >
      <p class="font-semibold">
        Status: {{ formatStatus(approval_status) }}
      </p>
      <p v-if="rejection_reason" class="text-sm mt-2">
        Reason: {{ rejection_reason }}
      </p>
    </div>

    <!-- ================= COMPANY DETAILS ================= -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Company Details</h2>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-gray-600 text-sm">Company Name</p>
          <p class="font-semibold">
            {{ company_details?.company_name !== 'N/A'
              ? company_details?.company_name
              : 'Not provided' }}
          </p>
        </div>

        <div>
          <p class="text-gray-600 text-sm">Contact Person</p>
          <p class="font-semibold">
            {{ company_details?.contact_person !== 'N/A'
              ? company_details?.contact_person
              : 'Not provided' }}
          </p>
        </div>

        <div>
          <p class="text-gray-600 text-sm">Email</p>
          <p class="font-semibold">
            {{ company_details?.email !== 'N/A'
              ? company_details?.email
              : 'Not provided' }}
          </p>
        </div>

        <div>
          <p class="text-gray-600 text-sm">Phone</p>
          <p class="font-semibold">
            {{ company_details?.phone !== 'N/A'
              ? company_details?.phone
              : 'Not provided' }}
          </p>
        </div>

        <div class="col-span-2">
          <p class="text-gray-600 text-sm">Address</p>
          <p class="font-semibold">
            {{ company_details?.address !== 'N/A'
              ? company_details?.address
              : 'Not provided' }}
          </p>
        </div>

        <div>
          <p class="text-gray-600 text-sm">Submission Date</p>
          <p class="font-semibold">
            {{ formatDate(submission_date) }}
          </p>
        </div>
      </div>
    </div>

    <!-- ================= DOCUMENTS ================= -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
      <h2 class="text-xl font-bold mb-4">Required Documents</h2>

      <div v-if="!documents || documents.length === 0" class="text-gray-500">
        No documents submitted
      </div>

      <div v-else class="space-y-3">
        <div
          v-for="(doc, index) in documents"
          :key="index"
          class="border rounded-lg p-4 flex justify-between items-center"
        >
          <div>
            <p class="font-semibold">
              {{ doc.name || 'Document ' + (index + 1) }}
            </p>

            <p class="text-xs text-gray-500 mt-1">
              {{ doc.uploaded_at
                ? 'Uploaded: ' + formatDate(doc.uploaded_at)
                : '' }}
            </p>

            <p v-if="!doc.exists" class="text-red-600 text-xs">
              ❌ File missing or deleted
            </p>
          </div>

          <button
            v-if="doc.exists && doc.url"
            class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm"
            @click="viewDocument(doc)"
          >
            View
          </button>
        </div>
      </div>
    </div>

    <!-- ================= ACTIONS ================= -->
    <div
      v-if="!approval_status || approval_status === 'pending'"
      class="bg-white shadow-md rounded-lg p-6"
    >
      <h2 class="text-xl font-bold mb-4">Verification Actions</h2>

      <div class="flex gap-4">
        <button
          class="w-full bg-green-600 text-white py-2 rounded hover:bg-green-700"
          @click="showApproveModal = true"
        >
          Approve Employer Onboarding
        </button>

        <button
          class="w-full bg-red-600 text-white py-2 rounded hover:bg-red-700"
          @click="showRejectModal = true"
        >
          Reject Onboarding
        </button>
      </div>
    </div>

    <!-- ================= DOCUMENT MODAL ================= -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-xl w-[90%] h-[90%] relative shadow-xl">

        <button
          class="absolute top-3 right-3 bg-red-500 text-white px-3 py-1 rounded"
          @click="closeModal"
        >✕</button>

        <div class="w-full h-full p-4">
          <iframe
            v-if="isPdf"
            :src="selectedDocument"
            class="w-full h-full rounded"
          ></iframe>

          <img
            v-else
            :src="selectedDocument"
            class="max-h-full mx-auto object-contain"
          />
        </div>

      </div>
    </div>

    <!-- ================= APPROVE MODAL ================= -->
    <div
      v-if="showApproveModal"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
      @click.self="showApproveModal = false"
    >
      <div class="bg-white rounded-xl w-[400px] p-6 shadow-xl">

        <h3 class="text-xl font-bold text-green-700 mb-4">
          Approve Employer
        </h3>

        <p class="mb-6 text-gray-600">
          Are you sure you want to approve this employer onboarding?
        </p>

        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 bg-gray-300 rounded"
            @click="showApproveModal = false">
            Cancel
          </button>

          <button
            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            @click="confirmApprove"
          >
            Confirm Approval
          </button>
        </div>

      </div>
    </div>

    <!-- ================= REJECT MODAL ================= -->
    <div
      v-if="showRejectModal"
      class="fixed inset-0 bg-black/70 flex items-center justify-center z-50"
      @click.self="showRejectModal = false"
    >
      <div class="bg-white rounded-xl w-[450px] p-6 shadow-xl">

        <h3 class="text-xl font-bold text-red-700 mb-4">
          Reject Employer
        </h3>

        <textarea
          v-model="rejectionReason"
          rows="4"
          class="w-full border rounded px-3 py-2 mb-6 text-sm"
          placeholder="Reason for rejection..."
        ></textarea>

        <div class="flex justify-end gap-3">
          <button class="px-4 py-2 bg-gray-300 rounded"
            @click="showRejectModal = false">
            Cancel
          </button>

          <button
            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            :disabled="!rejectionReason.trim()"
            @click="confirmReject"
          >
            Submit Rejection
          </button>
        </div>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  employer: Object,
  documents: Array,
  submission_date: String,
  company_details: Object,
  approval_status: String,
  rejection_reason: String
})

/* ================= DOCUMENT MODAL ================= */
const showModal = ref(false)
const selectedDocument = ref(null)

const isPdf = computed(() =>
  selectedDocument.value?.toLowerCase().endsWith('.pdf')
)

const viewDocument = (doc) => {
  selectedDocument.value = doc.url || doc.path
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  selectedDocument.value = null
}

/* ================= ACTION MODALS ================= */
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const rejectionReason = ref('')

const confirmApprove = () => {
  router.post(
    route('peso.employers.approve', { id: props.employer.id }),
    {},
    { onSuccess: () => (showApproveModal.value = false) }
  )
}

const confirmReject = () => {
  router.post(
    route('peso.employers.reject', { id: props.employer.id }),
    { rejection_reason: rejectionReason.value },
    {
      onSuccess: () => {
        showRejectModal.value = false
        rejectionReason.value = ''
      }
    }
  )
}

/* ================= HELPERS ================= */
const goBack = () => {
  window.history.back()
}

const formatDate = (date) =>
  date ? new Date(date).toLocaleString() : 'Not submitted'

const formatStatus = (status) => ({
  pending: 'Pending Review',
  approved: 'Approved',
  rejected: 'Rejected'
}[status] || status)

const getStatusClass = (status) => ({
  pending: 'bg-yellow-100 border border-yellow-300',
  approved: 'bg-green-100 border border-green-300',
  rejected: 'bg-red-100 border border-red-300'
}[status])
</script>