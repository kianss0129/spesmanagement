<template>
  <div class="min-h-screen bg-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <button
              type="button"
              class="mb-4 inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              @click="goBack"
            >
              Back
            </button>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Review Center</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Beneficiaries Applications</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
              Review beneficiary SPES applications, check requirement completeness, and take the next verification action.
            </p>
          </div>
        </div>
      </header>

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
          v-for="card in summaryCards"
          :key="card.label"
          class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
        >
          <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
          <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
        </div>
      </section>

      <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="filter in filters"
              :key="filter.value"
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-semibold transition"
              :class="activeFilter === filter.value ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
              @click="activeFilter = filter.value"
            >
              {{ filter.label }}
            </button>
          </div>

          <div class="text-sm text-slate-500">
            Showing {{ filteredBeneficiaries.length }} of {{ beneficiaries.length }} records
          </div>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[1.2fr_1.1fr_0.8fr_1fr_1fr_1fr_1.4fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:grid">
          <span>Name</span>
          <span>Email</span>
          <span>Category</span>
          <span>Submitted</span>
          <span>Requirements</span>
          <span>Status</span>
          <span>Actions</span>
        </div>

        <div
          v-for="beneficiary in filteredBeneficiaries"
          :key="beneficiary.id"
          class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 lg:grid-cols-[1.2fr_1.1fr_0.8fr_1fr_1fr_1fr_1.4fr] lg:items-center"
        >
          <div>
            <p class="font-semibold text-slate-900">{{ beneficiaryName(beneficiary) }}</p>
            <p class="mt-1 text-xs text-slate-500 lg:hidden">{{ beneficiary.user?.email || beneficiary.email || 'No email' }}</p>
          </div>

          <p class="hidden break-words text-sm text-slate-600 lg:block">{{ beneficiary.user?.email || beneficiary.email || 'No email' }}</p>
          <p class="text-sm capitalize text-slate-700">{{ beneficiary.user?.beneficiary_type || beneficiary.category || 'Student' }}</p>
          <p class="text-sm text-slate-600">{{ formatDate(beneficiary.onboarding_completed_at || beneficiary.created_at) }}</p>

          <span
            class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
            :class="requirementStatus(beneficiary).class"
          >
            {{ requirementStatus(beneficiary).label }}
          </span>

          <span
            class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
            :class="statusClass(beneficiary.approval_status || beneficiary.status)"
          >
            {{ formatStatus(beneficiary.approval_status || beneficiary.status) }}
          </span>

          <div class="flex flex-wrap gap-2">
            <Link
              :href="route('peso.beneficiaries.applications', { beneficiary: beneficiary.id })"
              class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
            >
              Review
            </Link>

            <button
              v-if="canShowDecisionActions(beneficiary)"
              type="button"
              class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
              @click="openApproveDialog(beneficiary.id)"
            >
              Approve
            </button>

            <button
              v-if="canShowDecisionActions(beneficiary)"
              type="button"
              class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100"
              @click="openCorrectionDialog(beneficiary.id)"
            >
              Request Correction
            </button>

            <button
              v-if="canShowDecisionActions(beneficiary)"
              type="button"
              class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
              @click="openRejectDialog(beneficiary.id)"
            >
              Reject
            </button>
          </div>
        </div>

        <div v-if="filteredBeneficiaries.length === 0" class="px-5 py-12 text-center">
          <p class="text-sm font-semibold text-slate-700">No applications found.</p>
          <p class="mt-1 text-sm text-slate-500">Try another filter or wait for new submissions.</p>
        </div>
      </section>
    </div>

    <div v-if="showApproveDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Approve Application</h3>
        <p class="mt-2 text-sm text-slate-600">Approve this beneficiary application and move it to the next SPES processing step.</p>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showApproveDialog = false">Cancel</button>
          <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700" @click="confirmApprove">Approve</button>
        </div>
      </div>
    </div>

    <div v-if="showRejectDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">{{ rejectMode === 'correction' ? 'Request Correction' : 'Reject Application' }}</h3>
        <p class="mt-2 text-sm text-slate-600">
          {{ rejectMode === 'correction' ? 'Enter the document or information correction needed from the applicant.' : 'Enter the reason for rejection.' }}
        </p>
        <textarea
          v-model="rejectionReason"
          class="mt-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
          rows="4"
          :placeholder="rejectMode === 'correction' ? 'Example: Please replace the unclear school ID upload.' : 'Reason for rejection...'"
        ></textarea>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showRejectDialog = false">Cancel</button>
          <button
            class="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50"
            :class="rejectMode === 'correction' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-red-600 hover:bg-red-700'"
            :disabled="!rejectionReason.trim()"
            @click="confirmReject"
          >
            {{ rejectMode === 'correction' ? 'Send Correction' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  beneficiaries: {
    type: Array,
    required: true,
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
})

const activeFilter = ref('all')
const showApproveDialog = ref(false)
const showRejectDialog = ref(false)
const rejectionReason = ref('')
const rejectMode = ref('reject')
const selectedId = ref(null)

const beneficiaries = computed(() => props.beneficiaries || [])

const filters = [
  { label: 'All', value: 'all' },
  { label: 'Pending', value: 'pending' },
  { label: 'Needs Correction', value: 'needs_correction' },
  { label: 'Incomplete', value: 'incomplete' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
]

const filteredBeneficiaries = computed(() => {
  if (activeFilter.value === 'all') return beneficiaries.value

  if (activeFilter.value === 'incomplete') {
    return beneficiaries.value.filter((beneficiary) => requirementStatus(beneficiary).value === 'incomplete')
  }

  return beneficiaries.value.filter((beneficiary) => {
    const status = String(beneficiary.approval_status || beneficiary.status || '').toLowerCase()
    return status === activeFilter.value
  })
})

const summaryCards = computed(() => {
  const pending = beneficiaries.value.filter((item) => formatStatusValue(item) === 'pending').length
  const incomplete = beneficiaries.value.filter((item) => requirementStatus(item).value === 'incomplete').length
  const approved = beneficiaries.value.filter((item) => formatStatusValue(item) === 'approved').length
  const needsCorrection = beneficiaries.value.filter((item) => formatStatusValue(item) === 'needs_correction').length
  const rejected = beneficiaries.value.filter((item) => formatStatusValue(item) === 'rejected').length

  return [
    { label: 'Pending', value: pending, description: 'Applications awaiting CPESO review.' },
    { label: 'Incomplete Requirements', value: incomplete, description: 'Applications with missing or unavailable documents.' },
    { label: 'Needs Correction', value: needsCorrection, description: 'Applications returned for applicant updates.' },
    { label: 'Approved', value: approved, description: 'Approved applications in the current list.' },
  ]
})

function beneficiaryName(beneficiary) {
  return (
    beneficiary.user?.name ||
    [beneficiary.first_name, beneficiary.middle_name, beneficiary.last_name].filter(Boolean).join(' ') ||
    'Unnamed applicant'
  )
}

function formatStatusValue(record) {
  return String(record.approval_status || record.status || 'pending').toLowerCase()
}

function canShowDecisionActions(beneficiary) {
  return props.canApprove && formatStatusValue(beneficiary) !== 'approved'
}

function formatStatus(status) {
  return {
    pending: 'Pending',
    needs_correction: 'Needs Correction',
    incomplete: 'Incomplete',
    approved: 'Approved',
    rejected: 'Rejected',
  }[String(status || 'pending').toLowerCase()] || status
}

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-800',
    needs_correction: 'bg-orange-100 text-orange-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    incomplete: 'bg-orange-100 text-orange-800',
  }[String(status || 'pending').toLowerCase()] || 'bg-slate-100 text-slate-700'
}

function requirementStatus(beneficiary) {
  const docs = beneficiary.documents
  const hasDocuments = Array.isArray(docs) ? docs.length > 0 : Boolean(docs)
  const unavailable = Array.isArray(docs) && docs.some((doc) => doc?.exists === false)

  if (!hasDocuments || unavailable) {
    return { value: 'incomplete', label: 'Incomplete', class: 'bg-orange-100 text-orange-800' }
  }

  return { value: 'submitted', label: 'Submitted', class: 'bg-blue-100 text-blue-800' }
}

function formatDate(date) {
  if (!date) return 'Not submitted'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date

  return parsed.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function goBack() {
  window.history.back()
}

function openApproveDialog(id) {
  selectedId.value = id
  showApproveDialog.value = true
}

function openRejectDialog(id) {
  selectedId.value = id
  rejectMode.value = 'reject'
  rejectionReason.value = ''
  showRejectDialog.value = true
}

function openCorrectionDialog(id) {
  selectedId.value = id
  rejectMode.value = 'correction'
  rejectionReason.value = ''
  showRejectDialog.value = true
}

function confirmApprove() {
  router.post(route('peso.beneficiaries.approve', { id: selectedId.value }), {}, {
    onSuccess: () => {
      showApproveDialog.value = false
      router.reload()
    },
  })
}

function confirmReject() {
  const routeName = rejectMode.value === 'correction'
    ? 'peso.beneficiaries.requestCorrection'
    : 'peso.beneficiaries.reject'
  const payload = rejectMode.value === 'correction'
    ? { correction_remarks: rejectionReason.value }
    : { rejection_reason: rejectionReason.value }

  router.post(route(routeName, { id: selectedId.value }), payload, {
    onSuccess: () => {
      showRejectDialog.value = false
      router.reload()
    },
  })
}
</script>
