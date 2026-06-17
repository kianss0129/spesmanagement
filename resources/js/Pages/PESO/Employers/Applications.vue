<template>
  <div class="min-h-screen bg-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <button
              type="button"
              class="mb-4 inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              @click="goBack"
            >
              Back
            </button>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Employer Verification</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ companyName }}</h1>
            <p class="mt-2 text-sm text-slate-600">Review company information, SPES participation details, and required documents.</p>
          </div>

          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(approval_status)">
            {{ formatStatus(approval_status) }}
          </span>
        </div>
      </header>

      <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-200 pb-4">
              <h2 class="text-lg font-bold text-slate-900">Company Information</h2>
              <p class="mt-1 text-sm text-slate-500">Core employer details for CPESO verification.</p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <InfoItem label="Company Name" :value="companyName" />
              <InfoItem label="Email" :value="companyEmail" />
              <InfoItem label="Contact Person" :value="contactPersonName" />
              <InfoItem label="Business Trade Name" :value="details?.business_trade_name" />
              <InfoItem label="Nature of Business" :value="details?.nature_of_business" />
              <InfoItem label="Industry Type" :value="details?.industry_type" />
              <InfoItem label="Sector" :value="details?.sector" />
              <InfoItem label="Employees" :value="details?.number_of_employees" />
              <InfoItem label="Submitted" :value="formatDate(submission_date || employer?.created_at)" />
              <InfoItem label="Address" :value="completeAddress" class="sm:col-span-2 lg:col-span-3" />
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="border-b border-slate-200 pb-4">
              <h2 class="text-lg font-bold text-slate-900">SPES Participation</h2>
              <p class="mt-1 text-sm text-slate-500">Review slots, work schedule, assignment plans, and proposed opportunities.</p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
              <InfoItem label="Previous SPES Participation" :value="spesParticipation?.previous_participation" />
              <InfoItem label="Years Participated" :value="spesParticipation?.years_participated" />
              <InfoItem label="Preferred Beneficiaries" :value="spesParticipation?.preferred_beneficiaries" />
              <InfoItem label="Available SPES Slots" :value="spesParticipation?.available_spes_slots" />
              <InfoItem label="Preferred Department" :value="spesParticipation?.preferred_department" />
              <InfoItem label="Employment Period" :value="spesParticipation?.employment_period" />
              <InfoItem label="Position / Task Title" :value="employmentOpportunities?.position_title" />
              <InfoItem label="Number of Vacancies" :value="employmentOpportunities?.number_of_vacancies" />
              <InfoItem label="Minimum Qualification" :value="employmentOpportunities?.minimum_qualification" />
              <InfoItem label="Assigned Department" :value="employmentOpportunities?.assigned_department" />
              <InfoItem label="Work Schedule" :value="employmentOpportunities?.work_schedule || spesParticipation?.work_schedules" class="sm:col-span-2" />
              <InfoItem label="Work Assignments" :value="spesParticipation?.work_assignments" class="sm:col-span-2" />
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Required Documents</h2>
                <p class="mt-1 text-sm text-slate-500">Open each document before approving employer participation.</p>
              </div>
              <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                {{ availableDocumentCount }} of {{ safeDocuments.length }} available
              </span>
            </div>

            <div class="mt-5 space-y-3">
              <div
                v-for="(doc, index) in safeDocuments"
                :key="doc.id || doc.name || index"
                class="flex flex-col gap-3 rounded-lg border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between"
              >
                <div>
                  <p class="font-semibold text-slate-900">{{ doc.name || doc.label || `Document ${index + 1}` }}</p>
                  <p v-if="doc.uploaded_at" class="mt-1 text-xs text-slate-500">Uploaded {{ formatDate(doc.uploaded_at) }}</p>
                  <p v-if="doc.original_name" class="mt-1 text-xs text-slate-500">File: {{ doc.original_name }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="isDocumentAvailable(doc) ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800'">
                    {{ isDocumentAvailable(doc) ? 'Uploaded' : 'Missing' }}
                  </span>
                  <button
                    v-if="isDocumentAvailable(doc)"
                    type="button"
                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                    @click="viewDocument(doc)"
                  >
                    View
                  </button>
                </div>
              </div>

              <div v-if="safeDocuments.length === 0" class="rounded-lg bg-slate-50 p-6 text-center text-sm text-slate-500">
                No documents were submitted with this employer application.
              </div>
            </div>
          </section>
        </div>

        <aside class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Contacts</h2>
            <div class="mt-4 space-y-4">
              <ContactBlock title="Authorized Representative" :name="authorizedRepresentativeName" :person="authorizedRepresentative" />
              <ContactBlock title="Contact Person" :name="contactPersonName" :person="contactPerson" />
              <ContactBlock title="Finance Officer" :name="financeOfficerName" :person="financeOfficer" />
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Review Summary</h2>
            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Documents</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ availableDocumentCount }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Slots</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ spesParticipation?.available_spes_slots || employmentOpportunities?.number_of_vacancies || 0 }}</p>
              </div>
            </div>
            <p v-if="rejection_reason" class="mt-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
              Previous remark: {{ rejection_reason }}
            </p>
          </section>

          <section v-if="canVerify" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Review Actions</h2>
            <div class="mt-4 grid gap-3">
              <button
                type="button"
                class="rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
                :disabled="isApproving || isRejecting"
                @click="showApproveModal = true"
              >
                Approve
              </button>
              <button
                type="button"
                class="rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                :disabled="isApproving || isRejecting"
                @click="showRejectModal = true"
              >
                Reject
              </button>
            </div>
          </section>
        </aside>
      </section>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closeModal">
      <div class="relative h-[90vh] w-full max-w-6xl rounded-lg bg-white p-4 shadow-xl">
        <button type="button" class="absolute right-3 top-3 z-10 rounded-lg bg-slate-900 px-3 py-1 text-sm font-semibold text-white" @click="closeModal">Close</button>
        <h3 class="mb-3 pr-20 text-lg font-bold text-slate-900">{{ selectedDocumentName || 'Document Preview' }}</h3>
        <iframe v-if="isPdf" :src="selectedDocument" class="h-[calc(90vh-6rem)] w-full rounded-lg border"></iframe>
        <img v-else-if="isImage" :src="selectedDocument" class="mx-auto max-h-[calc(90vh-6rem)] rounded-lg object-contain" />
        <div v-else class="flex h-[calc(90vh-6rem)] items-center justify-center rounded-lg bg-slate-50 text-center">
          <a :href="selectedDocument" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Open Document</a>
        </div>
      </div>
    </div>

    <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeApproveModal">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Approve Employer</h3>
        <p class="mt-2 text-sm text-slate-600">Approve this employer for SPES participation.</p>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" :disabled="isApproving" @click="closeApproveModal">Cancel</button>
          <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50" :disabled="isApproving" @click="confirmApprove">
            {{ isApproving ? 'Approving...' : 'Approve' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeRejectModal">
      <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Reject Employer</h3>
        <textarea
          v-model="rejectionReason"
          rows="4"
          class="mt-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
          placeholder="Reason for rejection..."
        ></textarea>
        <p v-if="rejectError" class="mt-2 text-sm text-red-600">{{ rejectError }}</p>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" :disabled="isRejecting" @click="closeRejectModal">Cancel</button>
          <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50" :disabled="isRejecting || !rejectionReason.trim()" @click="confirmReject">
            {{ isRejecting ? 'Submitting...' : 'Reject' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  employer: {
    type: Object,
    required: true,
  },
  documents: {
    type: Array,
    default: () => [],
  },
  submission_date: {
    type: String,
    default: null,
  },
  company_details: {
    type: Object,
    default: () => ({}),
  },
  approval_status: {
    type: String,
    default: null,
  },
  rejection_reason: {
    type: String,
    default: null,
  },
})

const InfoItem = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { default: null },
  },
  setup(itemProps) {
    return () =>
      h('div', {}, [
        h('p', { class: 'text-xs font-semibold uppercase tracking-[0.12em] text-slate-500' }, itemProps.label),
        h('p', { class: 'mt-1 break-words text-sm font-semibold text-slate-900 whitespace-pre-line' }, displayValue(itemProps.value)),
      ])
  },
})

const ContactBlock = defineComponent({
  props: {
    title: { type: String, required: true },
    name: { type: String, default: 'Not provided' },
    person: { type: Object, default: () => ({}) },
  },
  setup(blockProps) {
    return () =>
      h('div', { class: 'rounded-lg border border-slate-200 p-4' }, [
        h('p', { class: 'text-sm font-bold text-slate-900' }, blockProps.title),
        h('p', { class: 'mt-2 text-sm font-semibold text-slate-800' }, displayValue(blockProps.name)),
        h('p', { class: 'mt-1 text-xs text-slate-500' }, displayValue(blockProps.person?.position)),
        h('p', { class: 'mt-1 text-xs text-slate-500' }, displayValue(blockProps.person?.email)),
        h('p', { class: 'mt-1 text-xs text-slate-500' }, displayValue(blockProps.person?.mobile || blockProps.person?.mobile_number)),
      ])
  },
})

const details = computed(() => props.employer?.details || props.company_details || {})
const authorizedRepresentative = computed(() => details.value?.authorized_representative || {})
const contactPerson = computed(() => details.value?.contact_person || {})
const financeOfficer = computed(() => details.value?.finance_officer || {})
const spesParticipation = computed(() => details.value?.spes_participation || {})
const employmentOpportunities = computed(() => details.value?.employment_opportunities || {})
const safeDocuments = computed(() => Array.isArray(props.documents) ? props.documents : [])
const availableDocumentCount = computed(() => safeDocuments.value.filter(isDocumentAvailable).length)
const canVerify = computed(() => !props.approval_status || props.approval_status === 'pending')

const companyName = computed(() => props.employer?.company_name || props.company_details?.company_name || props.employer?.user?.name || 'Unknown employer')
const companyEmail = computed(() => props.employer?.user?.email || props.employer?.email || details.value?.company_contact?.official_company_email || 'Not provided')
const completeAddress = computed(() => {
  return [
    details.value?.house_number,
    details.value?.street,
    details.value?.barangay,
    details.value?.city,
    details.value?.province,
    details.value?.zip_code,
  ].filter(Boolean).join(', ') || props.company_details?.address || null
})

const authorizedRepresentativeName = computed(() => getFullName(authorizedRepresentative.value))
const contactPersonName = computed(() => getFullName(contactPerson.value))
const financeOfficerName = computed(() => getFullName(financeOfficer.value))

const showModal = ref(false)
const selectedDocument = ref(null)
const selectedDocumentName = ref('')
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const rejectionReason = ref('')
const rejectError = ref('')
const isApproving = ref(false)
const isRejecting = ref(false)

const isPdf = computed(() => getCleanUrl(selectedDocument.value).toLowerCase().endsWith('.pdf'))
const isImage = computed(() => /\.(png|jpg|jpeg|webp|gif)$/i.test(getCleanUrl(selectedDocument.value)))

function getFullName(person) {
  return [person?.first_name, person?.middle_name, person?.last_name, person?.suffix].filter(Boolean).join(' ') || 'Not provided'
}

function displayValue(value) {
  return value === null || value === undefined || value === '' || value === 'N/A' ? 'Not provided' : value
}

function getCleanUrl(url) {
  return String(url || '').split('?')[0].split('#')[0]
}

function getDocumentUrl(doc) {
  const rawUrl = doc?.url || doc?.path || doc?.file_url || doc?.file_path || ''
  if (!rawUrl) return null
  const value = String(rawUrl).trim()
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('/storage/')) return value
  if (value.startsWith('storage/')) return `/${value}`
  return `/storage/${value.replace(/^\/+/, '')}`
}

function isDocumentAvailable(doc) {
  return Boolean(doc && doc.exists !== false && getDocumentUrl(doc))
}

function viewDocument(doc) {
  const url = getDocumentUrl(doc)
  if (!url) return
  selectedDocument.value = url
  selectedDocumentName.value = doc?.name || doc?.label || doc?.original_name || 'Document Preview'
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedDocument.value = null
  selectedDocumentName.value = ''
}

function closeApproveModal() {
  if (isApproving.value) return
  showApproveModal.value = false
}

function closeRejectModal() {
  if (isRejecting.value) return
  showRejectModal.value = false
  rejectionReason.value = ''
  rejectError.value = ''
}

function confirmApprove() {
  if (!props.employer?.id) return
  isApproving.value = true
  router.post(route('peso.employers.approve', { id: props.employer.id }), {}, {
    preserveScroll: true,
    onSuccess: () => {
      showApproveModal.value = false
    },
    onFinish: () => {
      isApproving.value = false
    },
  })
}

function confirmReject() {
  const reason = rejectionReason.value.trim()
  if (!reason) {
    rejectError.value = 'Please enter a reason for rejection.'
    return
  }

  if (!props.employer?.id) return
  isRejecting.value = true
  rejectError.value = ''
  router.post(route('peso.employers.reject', { id: props.employer.id }), { rejection_reason: reason }, {
    preserveScroll: true,
    onSuccess: () => {
      showRejectModal.value = false
      rejectionReason.value = ''
    },
    onError: (errors) => {
      rejectError.value = errors?.rejection_reason || errors?.message || 'Unable to reject employer application.'
    },
    onFinish: () => {
      isRejecting.value = false
    },
  })
}

function goBack() {
  window.history.back()
}

function formatDate(date) {
  if (!date) return 'Not submitted'
  const parsedDate = new Date(date)
  if (Number.isNaN(parsedDate.getTime())) return 'Invalid date'
  return parsedDate.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatStatus(status) {
  return {
    pending: 'Pending Review',
    approved: 'Approved',
    rejected: 'Rejected',
  }[String(status || 'pending').toLowerCase()] || status
}

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }[String(status || 'pending').toLowerCase()] || 'bg-slate-100 text-slate-700'
}
</script>
