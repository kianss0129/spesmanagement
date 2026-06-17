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
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Application Verification</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">{{ applicantName }}</h1>
            <p class="mt-2 text-sm text-slate-600">Review applicant information, submitted requirements, and CPESO verification remarks.</p>
          </div>

          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(approval_status)">
            {{ formatStatus(approval_status) }}
          </span>
        </div>
      </header>

      <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <div class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Applicant Information</h2>
                <p class="mt-1 text-sm text-slate-500">Basic details used for SPES eligibility review.</p>
              </div>
              <p class="text-sm text-slate-500">Submitted: {{ formatDate(submission_date || beneficiary?.submitted_at || beneficiary?.onboarding_completed_at) }}</p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              <InfoItem label="Full Name" :value="applicantName" />
              <InfoItem label="Email" :value="beneficiary?.email || beneficiary?.user?.email" />
              <InfoItem label="Phone" :value="beneficiary?.phone || beneficiary?.contact_number" />
              <InfoItem label="Category" :value="formatCategory(beneficiary?.category || beneficiary_type || beneficiary?.user?.beneficiary_type)" />
              <InfoItem label="Birth Date" :value="beneficiary?.birth_date || beneficiary?.birthdate" />
              <InfoItem label="Age" :value="beneficiary?.age" />
              <InfoItem label="Gender" :value="beneficiary?.gender || beneficiary?.sex" />
              <InfoItem label="Civil Status" :value="beneficiary?.civil_status" />
              <InfoItem label="Place of Birth" :value="beneficiary?.place_of_birth" />
              <InfoItem label="Citizenship" :value="beneficiary?.citizenship" />
              <InfoItem label="Family Income" :value="beneficiary?.family_income" />

              <!-- Student-specific fields -->
              <template v-if="beneficiaryCategory === 'student'">
                <InfoItem label="School" :value="beneficiary?.school_name || beneficiary?.school?.name" />
                <InfoItem label="School Address" :value="beneficiary?.school_address" />
                <InfoItem label="Education Level" :value="beneficiary?.education_level" />
                <InfoItem label="School Year" :value="beneficiary?.school_year" />
                <InfoItem label="Year Level" :value="beneficiary?.year_level" />
                <InfoItem label="Course / Strand" :value="beneficiary?.course || beneficiary?.program" />
              </template>

              <!-- OSY-specific fields -->
              <template v-if="beneficiaryCategory === 'osy'">
                <InfoItem label="Last School Attended" :value="beneficiary?.last_school_attended" />
                <InfoItem label="Highest Attainment" :value="beneficiary?.highest_attainment" />
                <InfoItem label="Year Last Attended" :value="beneficiary?.year_last_attended" />
              </template>

              <!-- Dependent-specific fields -->
              <template v-if="beneficiaryCategory === 'dependent'">
                <InfoItem label="Parent / Guardian" :value="beneficiary?.parent_guardian_name || beneficiary?.parent_name" />
                <InfoItem label="Relationship" :value="beneficiary?.relationship" />
                <InfoItem label="Reason for Displacement" :value="beneficiary?.displacement_reason" />
                <InfoItem label="Former Employer" :value="beneficiary?.former_employer" />
                <InfoItem label="Displacement Date" :value="beneficiary?.displacement_date" />
              </template>

              <InfoItem label="Skills" :value="formattedSkills" />
              <InfoItem label="Address" :value="completeAddress" class="sm:col-span-2 lg:col-span-3" />
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Requirement Checklist</h2>
                <p class="mt-1 text-sm text-slate-500">Check uploaded documents before approving the application.</p>
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
                  <p class="font-semibold text-slate-900">{{ doc.name || doc.label || `Requirement ${index + 1}` }}</p>
                  <p class="mt-1 text-xs text-slate-500">{{ doc.uploaded_at ? `Uploaded ${formatDate(doc.uploaded_at)}` : 'Upload date not available' }}</p>
                  <p v-if="doc.description" class="mt-1 text-sm text-slate-600">{{ doc.description }}</p>
                  <p v-if="doc.remarks" class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-800">CPESO remarks: {{ doc.remarks }}</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                  <span v-if="doc.resubmitted" class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">Resubmitted</span>
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="documentStatus(doc).class">
                    {{ documentStatus(doc).label }}
                  </span>
                  <button
                    v-if="isDocumentAvailable(doc)"
                    type="button"
                    class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                    @click="openModal(doc)"
                  >
                    View
                  </button>
                </div>
              </div>

              <div v-if="safeDocuments.length === 0" class="rounded-lg bg-slate-50 p-6 text-center text-sm text-slate-500">
                No requirements were submitted with this application.
              </div>
            </div>
          </section>
        </div>

        <aside class="space-y-6">
          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Document Status</h2>
            <div class="mt-4 grid grid-cols-2 gap-3">
              <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Available</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ availableDocumentCount }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Missing</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ missingDocumentCount }}</p>
              </div>
            </div>
          </section>

          <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">CPESO Remarks</h2>
            <p class="mt-2 text-sm text-slate-600">
              Use remarks to record correction instructions or rejection reasons.
            </p>
            <textarea
              v-model="reviewRemarks"
              rows="5"
              class="mt-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="Write remarks or correction instructions..."
            ></textarea>
            <p v-if="rejection_reason" class="mt-3 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700">
              Previous remark: {{ rejection_reason }}
            </p>
          </section>

          <section v-if="latestApplication" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="flex flex-col gap-3 border-b border-slate-200 pb-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">Completion Review</h2>
                <p class="mt-1 text-sm text-slate-500">Final CPESO validation before official completion.</p>
              </div>
              <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="completionStatusClass(latestApplication.status)">
                {{ formatWorkflowStatus(latestApplication.status) }}
              </span>
            </div>

            <div class="mt-4 space-y-3">
              <div
                v-for="item in readinessItems"
                :key="item.label"
                class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2"
              >
                <span class="text-sm font-semibold text-slate-700">{{ item.label }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="item.ok ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'">
                  {{ item.ok ? 'Found' : 'Not found' }}
                </span>
              </div>
            </div>

            <textarea
              v-if="canApproveCompletion"
              v-model="completionRemarks"
              rows="3"
              class="mt-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="Optional completion remarks..."
            ></textarea>

            <button
              v-if="canApproveCompletion"
              type="button"
              class="mt-4 w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="approvingCompletion"
              @click="approveCompletion"
            >
              {{ approvingCompletion ? 'Approving...' : 'Approve Completion' }}
            </button>

            <p v-else class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600">
              Completion approval is available when the application is submitted for CPESO completion review.
            </p>
          </section>

          <section v-if="canVerify" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-lg font-bold text-slate-900">Review Actions</h2>
            <div class="mt-4 grid gap-3">
              <button
                type="button"
                class="rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700"
                @click="showApproveModal = true"
              >
                Approve
              </button>
              <button
                type="button"
                class="rounded-lg bg-amber-600 px-4 py-3 text-sm font-semibold text-white hover:bg-amber-700 disabled:opacity-50"
                :disabled="!reviewRemarks.trim()"
                @click="requestCorrection"
              >
                Request Correction
              </button>
              <button
                type="button"
                class="rounded-lg bg-red-600 px-4 py-3 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
                :disabled="!reviewRemarks.trim()"
                @click="showRejectModal = true"
              >
                Reject
              </button>
            </div>
          </section>
        </aside>
      </section>
    </div>

    <Teleport to="body">
      <div v-if="modalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closeModal">
        <div class="relative h-[90vh] w-full max-w-5xl rounded-lg bg-white p-4 shadow-xl">
          <button type="button" class="absolute right-3 top-3 z-10 rounded-lg bg-slate-900 px-3 py-1 text-sm font-semibold text-white" @click="closeModal">Close</button>
          <h3 class="mb-3 pr-20 text-lg font-bold text-slate-900">{{ currentDocument.name || 'Document Preview' }}</h3>
          <iframe v-if="isPDF(currentDocumentUrl)" :src="currentDocumentUrl" class="h-[calc(90vh-6rem)] w-full rounded-lg border"></iframe>
          <img v-else-if="isImage(currentDocumentUrl)" :src="currentDocumentUrl" class="mx-auto max-h-[calc(90vh-6rem)] rounded-lg object-contain" />
          <div v-else class="flex h-[calc(90vh-6rem)] items-center justify-center rounded-lg bg-slate-50 text-center">
            <a :href="currentDocumentUrl" target="_blank" rel="noopener noreferrer" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">Open Document</a>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showApproveModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="text-lg font-bold text-slate-900">Approve Application</h3>
          <p class="mt-2 text-sm text-slate-600">Approve this applicant for SPES processing.</p>
          <div class="mt-6 flex justify-end gap-2">
            <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showApproveModal = false">Cancel</button>
            <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700" @click="confirmApprove">Approve</button>
          </div>
        </div>
      </div>
    </Teleport>

    <Teleport to="body">
      <div v-if="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
          <h3 class="text-lg font-bold text-slate-900">Reject Application</h3>
          <p class="mt-2 text-sm text-slate-600">This will save the CPESO remarks as the rejection reason.</p>
          <div class="mt-6 flex justify-end gap-2">
            <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showRejectModal = false">Cancel</button>
            <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="confirmReject">Reject</button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  beneficiary: Object,
  documents: {
    type: Array,
    default: () => [],
  },
  submission_date: String,
  approval_status: String,
  rejection_reason: String,
  beneficiary_type: String,
  latest_application: {
    type: Object,
    default: null,
  },
  completion_readiness: {
    type: Object,
    default: () => ({}),
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
        h('p', { class: 'mt-1 break-words text-sm font-semibold text-slate-900' }, displayValue(itemProps.value)),
      ])
  },
})

const reviewRemarks = ref(props.rejection_reason || '')
const modalOpen = ref(false)
const currentDocument = ref({})
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const approvingCompletion = ref(false)
const completionRemarks = ref('')

const safeDocuments = computed(() => Array.isArray(props.documents) ? props.documents : [])
const availableDocumentCount = computed(() => safeDocuments.value.filter(isDocumentAvailable).length)
const missingDocumentCount = computed(() => Math.max(safeDocuments.value.length - availableDocumentCount.value, 0))
const canVerify = computed(() => {
  const status = String(props.approval_status || 'pending').toLowerCase()
  return status === 'pending' || status === 'needs_correction'
})
const latestApplication = ref(props.latest_application)
const completionReadiness = ref(props.completion_readiness || {})
const canApproveCompletion = computed(() => latestApplication.value?.status === 'completion_review')
const readinessItems = computed(() => [
  { label: 'DTR records', ok: Boolean(completionReadiness.value.has_dtr) },
  { label: 'Approved Daily Reports', ok: Boolean(completionReadiness.value.has_approved_daily_reports) },
  { label: 'Employer rating', ok: Boolean(completionReadiness.value.has_employer_rating) },
  { label: 'Certificate uploaded', ok: Boolean(completionReadiness.value.has_certificate) },
])

const applicantName = computed(() => {
  return (
    props.beneficiary?.user?.name ||
    [
      props.beneficiary?.first_name,
      props.beneficiary?.middle_name,
      props.beneficiary?.last_name,
      props.beneficiary?.suffix,
    ].filter(Boolean).join(' ') ||
    'Unnamed applicant'
  )
})

const completeAddress = computed(() => {
  return [
    props.beneficiary?.present_address || props.beneficiary?.address,
    props.beneficiary?.barangay,
    props.beneficiary?.city,
    props.beneficiary?.province,
  ].filter(Boolean).join(', ') || null
})

const beneficiaryCategory = computed(() => {
  return String(props.beneficiary?.category || props.beneficiary_type || props.beneficiary?.user?.beneficiary_type || 'student').toLowerCase()
})

const formattedSkills = computed(() => {
  const skills = props.beneficiary?.skills
  if (!skills) return null
  if (Array.isArray(skills)) {
    if (skills.length === 0) return null
    return skills.map(s => (typeof s === 'object' ? s.name : s)).filter(Boolean).join(', ') || null
  }
  return skills
})

function formatCategory(value) {
  const cat = String(value || '').toLowerCase()
  if (cat === 'student') return 'Student'
  if (cat === 'osy') return 'Out-of-School Youth'
  if (cat === 'dependent') return 'Dependent of Displaced Worker'
  return value || 'Not provided'
}

const currentDocumentUrl = computed(() => getDocumentUrl(currentDocument.value))

function displayValue(value) {
  return value === null || value === undefined || value === '' ? 'Not provided' : value
}

function getDocumentUrl(doc) {
  const rawUrl = doc?.url || doc?.path || doc?.file_url || doc?.file_path || ''
  if (!rawUrl) return ''
  const value = String(rawUrl).trim()
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('/storage/')) return value
  if (value.startsWith('storage/')) return `/${value}`
  return `/storage/${value.replace(/^\/+/, '')}`
}

function isDocumentAvailable(doc) {
  return Boolean(doc && doc.exists !== false && getDocumentUrl(doc))
}

function documentStatus(doc) {
  if (!isDocumentAvailable(doc)) {
    return { label: 'Missing', class: 'bg-red-100 text-red-800' }
  }

  const status = String(doc.status || '').toLowerCase()
  if (status === 'accepted' || status === 'approved') return { label: 'Accepted', class: 'bg-green-100 text-green-800' }
  if (status === 'rejected' || status === 'needs_correction') return { label: 'Needs Correction', class: 'bg-amber-100 text-amber-800' }

  return { label: 'Uploaded', class: 'bg-blue-100 text-blue-800' }
}

function openModal(doc) {
  currentDocument.value = doc
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  currentDocument.value = {}
}

function isPDF(url) {
  return String(url || '').split('?')[0].toLowerCase().endsWith('.pdf')
}

function isImage(url) {
  return /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(String(url || '').split('?')[0])
}

function formatStatus(status) {
  return {
    pending: 'Pending Review',
    approved: 'Approved',
    needs_correction: 'Needs Correction',
    rejected: 'Rejected',
  }[String(status || 'pending').toLowerCase()] || status
}

function formatWorkflowStatus(status) {
  const text = String(status || 'No Application').replace(/_/g, ' ')
  return text.replace(/\b\w/g, (char) => char.toUpperCase())
}

function completionStatusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value === 'completed') return 'bg-green-100 text-green-800'
  if (value === 'completion_review') return 'bg-blue-100 text-blue-800'
  if (value === 'deployed' || value === 'ongoing') return 'bg-amber-100 text-amber-800'
  return 'bg-slate-100 text-slate-700'
}

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-800',
    needs_correction: 'bg-orange-100 text-orange-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }[String(status || 'pending').toLowerCase()] || 'bg-slate-100 text-slate-700'
}

function formatDate(date) {
  if (!date) return 'Not submitted'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date

  return parsed.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function goBack() {
  window.history.back()
}

function confirmApprove() {
  showApproveModal.value = false
  router.post(route('peso.beneficiaries.approve', { id: props.beneficiary.id }))
}

function requestCorrection() {
  router.post(route('peso.beneficiaries.requestCorrection', { id: props.beneficiary.id }), {
    correction_remarks: reviewRemarks.value,
  })
}

function confirmReject() {
  showRejectModal.value = false
  router.post(route('peso.beneficiaries.reject', { id: props.beneficiary.id }), {
    rejection_reason: reviewRemarks.value,
  })
}

async function approveCompletion() {
  if (!latestApplication.value?.id) return

  approvingCompletion.value = true

  try {
    const response = await axios.post(`/peso/applications/${latestApplication.value.id}/approve-completion`, {
      remarks: completionRemarks.value || null,
    })

    latestApplication.value = {
      ...latestApplication.value,
      ...(response.data.application || {}),
      status: 'completed',
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Unable to approve completion.')
  } finally {
    approvingCompletion.value = false
  }
}
</script>
