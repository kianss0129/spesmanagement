<script setup>
import { computed, onMounted, ref, watch } from 'vue'

const props = defineProps({
  selectedTab: String,
  sortedApplications: { type: Array, default: () => [] },
  applications: { type: Array, default: () => [] },
  exams: { type: Array, default: () => [] },
  examForm: { type: Object, required: true },
  minDateTime: String,
  isAssignedApplication: Function,
  hasScheduledExam: Function,
  selectExamApplicant: Function,
  getSelectedExamApplicantName: Function,
  clearExamSelection: Function,
  scheduleExam: Function,
  updateExamResult: Function,
  formatDate: Function,
  batchHistory: { type: Array, default: () => [] },
  selectBatch: Function,
  selectBatchCustom: Function,
  loadData: Function,
})

const searchQuery = ref('')
const showHistoryModal = ref(false)
const examType = ref('Qualification Examination')
const batchError = ref('')

const {
  hasScheduledExam = () => false,
  selectExamApplicant = () => {},
  clearExamSelection = () => {},
  scheduleExam = () => {},
  updateExamResult = () => {},
  formatDate = (date) => date,
  loadData = () => {},
} = props

const safeExams = computed(() => props.exams || [])
const safeBatchHistory = computed(() => props.batchHistory || [])
const safeApplications = computed(() => props.applications || [])
const safeSortedApplications = computed(() => props.sortedApplications || props.applications || [])
const selectedIds = computed(() => props.examForm?.application_ids || [])

const normalizeStatus = (value) => String(value || '').toLowerCase().replace(/\s+/g, '_')
const getApplicationKey = (app) => app.application_id || app.id
const getApplicantName = (app) => app?.beneficiary_name || app?.applicant_name || app?.name || `Applicant #${getApplicationKey(app)}`
const getInitial = (name) => String(name || 'A').trim().charAt(0).toUpperCase() || 'A'

const getExamResultForApplication = (app) => {
  const applicationId = getApplicationKey(app)
  const exam = safeExams.value.find((item) => Number(item.application_id) === Number(applicationId))
  return normalizeStatus(app.exam_result || app.exam?.result || exam?.result)
}

const isExamEligible = (app) => {
  const status = normalizeStatus(app?.status)
  const result = getExamResultForApplication(app)
  if (status !== 'for_exam') return false
  if (['passed', 'failed'].includes(result)) return false
  if (hasScheduledExam(getApplicationKey(app))) return false
  return true
}

const eligibleApplications = computed(() => safeSortedApplications.value.filter(isExamEligible))

const selectedApplicants = computed(() => {
  return safeSortedApplications.value.filter((app) =>
    selectedIds.value.some((id) => Number(id) === Number(getApplicationKey(app)))
  )
})

const selectedCount = computed(() => selectedApplicants.value.length)
const scheduledExams = computed(() => safeExams.value.filter((e) => normalizeStatus(e.status) === 'scheduled'))
const passedExams = computed(() => safeExams.value.filter((e) => normalizeStatus(e.result) === 'passed'))
const failedExams = computed(() => safeExams.value.filter((e) => normalizeStatus(e.result) === 'failed'))
const pendingResultExams = computed(() => safeExams.value.filter((e) => !['passed', 'failed'].includes(normalizeStatus(e.result))))

const statCards = computed(() => [
  { label: 'Total Applicants', value: safeApplications.value.length, detail: 'All exam applicants', tone: 'border-slate-200 bg-white text-slate-900' },
  { label: 'Scheduled', value: scheduledExams.value.length, detail: 'Upcoming examinations', tone: 'border-blue-200 bg-blue-50 text-blue-900' },
  { label: 'Passed', value: passedExams.value.length, detail: 'Successful applicants', tone: 'border-emerald-200 bg-emerald-50 text-emerald-900' },
  { label: 'Failed', value: failedExams.value.length, detail: 'Did not pass', tone: 'border-red-200 bg-red-50 text-red-900' },
  { label: 'Pending Results', value: pendingResultExams.value.length, detail: 'Awaiting evaluation', tone: 'border-amber-200 bg-amber-50 text-amber-900' },
])

const filteredApplicants = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()
  return eligibleApplications.value.filter((app) => {
    if (!query) return true
    const searchable = [getApplicantName(app), app.category, app.beneficiary_type, app.school_name, app.school].filter(Boolean).join(' ').toLowerCase()
    return searchable.includes(query)
  })
})

const upcomingExams = computed(() => {
  return [...safeExams.value].sort((a, b) => new Date(a.exam_date || 0) - new Date(b.exam_date || 0))
})

function isSelected(app) {
  return selectedIds.value.some((id) => Number(id) === Number(getApplicationKey(app)))
}

function toggleApplicant(app) {
  const id = getApplicationKey(app)
  if (isSelected(app)) {
    props.examForm.application_ids = selectedIds.value.filter((item) => Number(item) !== Number(id))
    props.examForm.batch_size = props.examForm.application_ids.length || 1
    return
  }
  selectExamApplicant(app)
}

function removeApplicant(app) {
  props.examForm.application_ids = selectedIds.value.filter((id) => Number(id) !== Number(getApplicationKey(app)))
  props.examForm.batch_size = props.examForm.application_ids.length || 1
}

function selectAllEligible() {
  props.examForm.application_ids = eligibleApplications.value.map((app) => getApplicationKey(app))
  props.examForm.batch_size = props.examForm.application_ids.length || 1
}

function selectBatchBySize() {
  const size = Number(props.examForm?.batch_size || 0)
  if (size <= 0) { props.examForm.application_ids = []; return }
  props.examForm.application_ids = eligibleApplications.value.slice(0, size).map((app) => getApplicationKey(app))
  props.examForm.batch_size = props.examForm.application_ids.length
}

function clearSelection() {
  clearExamSelection()
}

function formatCategory(value) {
  const cat = String(value || '').toLowerCase()
  if (cat === 'student') return 'Student'
  if (cat === 'osy') return 'OSY'
  if (cat === 'dependent') return 'Dependent of Displaced Worker'
  return value || ''
}

function getApplicantDetails(app) {
  const category = formatCategory(app?.category || app?.beneficiary_type)
  const status = String(app?.status || '').toUpperCase().replace(/_/g, ' ')
  if (category && status) return `${category} • ${status}`
  if (category) return category
  if (status) return status
  return ''
}

function resultClass(result) {
  return { passed: 'bg-emerald-50 text-emerald-700 ring-emerald-200', failed: 'bg-red-50 text-red-700 ring-red-200', pending: 'bg-amber-50 text-amber-700 ring-amber-200' }[normalizeStatus(result)] || 'bg-slate-50 text-slate-600 ring-slate-200'
}

function statusClass(status) {
  return { scheduled: 'bg-blue-50 text-blue-700 ring-blue-200', completed: 'bg-slate-50 text-slate-700 ring-slate-200' }[normalizeStatus(status)] || 'bg-slate-50 text-slate-600 ring-slate-200'
}

function getTimeLabel(exam) {
  const parts = []
  if (exam.start_time) parts.push(exam.start_time)
  if (exam.end_time) parts.push(exam.end_time)
  return parts.length ? parts.join(' – ') : 'Time not set'
}

watch(() => props.selectedTab, (newTab) => { if (newTab === 'exam' && loadData) loadData() })
onMounted(() => { if (props.selectedTab === 'exam' && loadData) loadData() })
</script>

<template>
  <section v-if="selectedTab === 'exam'" class="w-full max-w-none space-y-8">

    <!-- SECTION 1: OVERVIEW DASHBOARD -->
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
      <div class="flex flex-col gap-6 2xl:flex-row 2xl:items-end 2xl:justify-between">
        <div class="max-w-3xl">
          <p class="text-sm font-bold uppercase tracking-wide text-blue-700">
            CPESO Examination Module
          </p>
          <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Face-to-Face Examination
          </h2>
          <p class="mt-3 text-base leading-7 text-slate-600">
            Schedule, monitor, and manage face-to-face SPES examinations and applicant results.
          </p>
        </div>

        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 2xl:max-w-4xl 2xl:grid-cols-5">
          <div
            v-for="card in statCards"
            :key="card.label"
            :class="card.tone"
            class="rounded-2xl border p-5 shadow-sm"
          >
            <p class="text-xs font-bold uppercase tracking-wide opacity-70">{{ card.label }}</p>
            <p class="mt-3 text-3xl font-black">{{ card.value }}</p>
            <p class="mt-1 text-xs font-semibold opacity-70">{{ card.detail }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- SECTION 2: SCHEDULE FORM + UPCOMING EXAMS (2 columns like Interview) -->
    <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">

      <!-- LEFT: SCHEDULER -->
      <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-6 sm:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-2xl font-black text-slate-950">Schedule Face-to-Face Examination</h3>
              <p class="mt-1 text-sm text-slate-500">Create a batch, select applicants, and set examination details.</p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700">
              {{ selectedCount }} selected
            </span>
          </div>
        </div>

        <form @submit.prevent="scheduleExam" class="space-y-6 p-6 sm:p-8">

          <!-- STEP 1: BATCH -->
          <div class="grid gap-5 lg:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Batch Name</label>
              <input
                v-model="examForm.batch_title"
                type="text"
                placeholder="Example: Exam Batch A"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
              />
            </div>
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Batch Size</label>
              <div class="grid grid-cols-[1fr_auto] gap-3">
                <input
                  v-model.number="examForm.batch_size"
                  type="number"
                  min="1"
                  placeholder="No. of applicants"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                />
                <button
                  type="button"
                  @click="selectBatchBySize"
                  :disabled="!eligibleApplications.length || !examForm.batch_size"
                  class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Apply
                </button>
              </div>
            </div>
          </div>

          <!-- STEP 2: APPLICANT SELECTOR -->
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-sm font-black text-slate-900">Applicants for Examination</p>
                <p class="mt-1 text-xs font-semibold text-slate-500">{{ eligibleApplications.length }} applicants with "For Exam" status.</p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button type="button" @click="selectAllEligible" :disabled="!eligibleApplications.length" class="rounded-xl bg-blue-600 px-4 py-2 text-xs font-black text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50">Select All</button>
                <button type="button" @click="clearSelection" :disabled="selectedCount === 0" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50">Clear</button>
              </div>
            </div>

            <!-- Selected as chips -->
            <div v-if="selectedApplicants.length" class="mt-4 flex flex-wrap gap-2">
              <button
                v-for="app in selectedApplicants"
                :key="getApplicationKey(app)"
                type="button"
                @click="removeApplicant(app)"
                class="inline-flex max-w-full items-center gap-2 rounded-full border border-blue-200 bg-white px-3 py-2 text-xs font-bold text-blue-800 shadow-sm transition hover:border-red-200 hover:text-red-600"
              >
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[11px]">{{ getInitial(getApplicantName(app)) }}</span>
                <span class="truncate">{{ getApplicantName(app) }}</span>
                <span class="text-red-400">×</span>
              </button>
            </div>

            <!-- Search -->
            <div class="mt-4">
              <input
                v-model="searchQuery"
                type="search"
                placeholder="Search by name, category, or job preference..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
              />
            </div>

            <!-- Applicant list -->
            <div class="mt-4 max-h-80 space-y-2 overflow-auto pr-1">
              <div v-if="filteredApplicants.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm font-bold text-slate-500">
                No applicants currently waiting for exam scheduling.
              </div>
              <template v-else>
                <button
                  v-for="app in filteredApplicants"
                  :key="getApplicationKey(app)"
                  type="button"
                  @click="toggleApplicant(app)"
                  :class="isSelected(app) ? 'border-blue-300 bg-blue-50' : 'border-slate-200 bg-white hover:border-blue-200'"
                  class="flex w-full items-center gap-4 rounded-2xl border p-4 text-left transition"
                >
                  <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-black text-white">{{ getInitial(getApplicantName(app)) }}</span>
                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-black text-slate-900">{{ getApplicantName(app) }}</span>
                    <span class="mt-1 block truncate text-xs font-semibold text-slate-500">{{ getApplicantDetails(app) }}</span>
                  </span>
                  <span :class="isSelected(app) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'" class="rounded-full px-3 py-1 text-xs font-black">
                    {{ isSelected(app) ? 'Selected' : 'Select' }}
                  </span>
                </button>
              </template>
            </div>
          </div>

          <!-- STEP 3: EXAMINATION DETAILS -->
          <div class="grid gap-5 lg:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Examination Title</label>
              <input v-model="examForm.exam_title" type="text" placeholder="SPES Qualification Examination" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Examination Type</label>
              <select v-model="examType" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                <option>Qualification Examination</option>
                <option>Special Examination</option>
                <option>Re-Examination</option>
              </select>
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800">Examination Venue</label>
            <input v-model="examForm.location" type="text" placeholder="CPESO Office, City Hall, Training Center, etc." required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" />
          </div>

          <div class="grid gap-5 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Examination Date</label>
              <input v-model="examForm.date" type="date" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">Start Time</label>
              <input v-model="examForm.start_time" type="time" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" />
            </div>
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">End Time</label>
              <input v-model="examForm.end_time" type="time" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100" />
            </div>
          </div>

          <!-- STEP 4: REMINDERS & INSTRUCTIONS -->
          <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="text-sm font-black text-amber-900">Examination Reminders</p>
            <ul class="mt-3 space-y-1.5 text-sm text-amber-800">
              <li>• Arrive at least 30 minutes before the examination.</li>
              <li>• Bring a valid ID and required documents.</li>
              <li>• Wear proper attire.</li>
              <li>• Mobile phones must be on silent mode.</li>
              <li>• Late arrivals may not be accommodated.</li>
            </ul>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800">Additional Instructions</label>
            <textarea v-model="examForm.instructions" rows="3" placeholder="Add specific instructions for this examination..." class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"></textarea>
          </div>

          <!-- STEP 5: SUBMIT -->
          <div class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">
              <input v-model="examForm.notify_beneficiaries" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
              Send notification to applicants
            </label>
            <button
              type="submit"
              :disabled="selectedCount === 0"
              class="inline-flex items-center justify-center gap-3 rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              Schedule Examination
            </button>
          </div>
        </form>
      </div>

      <!-- RIGHT: SCHEDULED EXAMINATIONS -->
      <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-6 sm:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-2xl font-black text-slate-950">Scheduled Examinations</h3>
              <p class="mt-1 text-sm text-slate-500">Monitor upcoming face-to-face exams and record results.</p>
            </div>
            <div class="flex gap-3">
              <span class="inline-flex w-fit items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white">{{ upcomingExams.length }} records</span>
              <button type="button" @click="showHistoryModal = true" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-100">History</button>
            </div>
          </div>
        </div>

        <div class="max-h-[980px] space-y-4 overflow-auto p-6 sm:p-8">
          <div v-if="upcomingExams.length === 0" class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-8 py-20 text-center">
            <p class="text-xl font-black text-slate-700">No examinations scheduled</p>
            <p class="mt-2 text-sm text-slate-500">New schedules will appear here after submitting the scheduler form.</p>
          </div>

          <template v-else>
            <article
              v-for="exam in upcomingExams"
              :key="exam.id"
              class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md sm:p-6"
            >
              <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-blue-600 text-center text-white shadow-sm">
                  <span class="text-xs font-bold uppercase">Exam</span>
                  <span class="text-lg font-black">{{ exam.applicant_count || exam.batch_size || 1 }}</span>
                </div>

                <div class="min-w-0 flex-1">
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                      <h4 class="truncate text-xl font-black text-slate-950">{{ exam.batch_title || exam.exam_title || 'Qualification Examination' }}</h4>
                      <p class="mt-1 truncate text-sm font-semibold text-slate-500">{{ exam.location || 'Venue to be confirmed' }}</p>
                      <p v-if="exam.batch_title" class="mt-2 text-xs font-black uppercase tracking-wide text-blue-700">{{ exam.batch_title }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <span :class="statusClass(exam.status)" class="rounded-full px-3 py-1.5 text-xs font-black uppercase ring-1">{{ exam.status || 'pending' }}</span>
                      <span v-if="exam.result && exam.result !== 'pending'" :class="resultClass(exam.result)" class="rounded-full px-3 py-1.5 text-xs font-black uppercase ring-1">{{ exam.result }}</span>
                    </div>
                  </div>

                  <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-xs font-black uppercase tracking-wide text-slate-400">Date</p>
                      <p class="mt-2 text-sm font-black text-slate-800">{{ formatDate(exam.exam_date) }}</p>
                      <p class="mt-1 text-xs font-bold text-slate-500">{{ getTimeLabel(exam) }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-xs font-black uppercase tracking-wide text-slate-400">Venue</p>
                      <p class="mt-2 text-sm font-black text-slate-800">{{ exam.location || 'To be confirmed' }}</p>
                      <p class="mt-1 text-xs font-bold text-slate-500">{{ exam.applicant_count || exam.batch_size || 1 }} applicant(s)</p>
                    </div>
                  </div>

                  <p v-if="exam.instructions" class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-6 text-slate-600">{{ exam.instructions }}</p>

                  <div class="mt-5 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:flex-wrap">
                    <template v-if="exam.status === 'scheduled' && (!exam.result || exam.result === 'pending')">
                      <button type="button" @click="updateExamResult(exam.id, 'passed')" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-emerald-700">Mark Passed</button>
                      <button type="button" @click="updateExamResult(exam.id, 'failed')" class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-red-700">Mark Failed</button>
                    </template>
                    <span v-else class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-500">Result action unavailable</span>
                  </div>
                </div>
              </div>
            </article>
          </template>
        </div>
      </div>
    </div>

    <!-- BATCH HISTORY MODAL -->
    <Teleport to="body">
      <div v-if="showHistoryModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4 backdrop-blur-sm">
        <div class="max-h-[85vh] w-full max-w-4xl overflow-auto rounded-3xl bg-white p-6 shadow-2xl">
          <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-4">
            <div>
              <h3 class="text-xl font-black text-slate-950">Examination Batch History</h3>
              <p class="text-sm text-slate-500">Previously scheduled examination batches.</p>
            </div>
            <button type="button" @click="showHistoryModal = false" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-700">Close</button>
          </div>
          <div class="mt-5 grid gap-4 md:grid-cols-2">
            <div v-if="safeBatchHistory.length === 0" class="rounded-2xl border border-dashed border-slate-300 p-8 text-center text-sm font-bold text-slate-500 md:col-span-2">No examination batches created yet.</div>
            <div v-for="batch in safeBatchHistory" v-else :key="batch.id" class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
              <p class="text-base font-black text-slate-900">{{ batch.batch_title || batch.name || 'Unnamed Batch' }}</p>
              <p class="mt-1 text-sm text-slate-600">{{ formatDate(batch.exam_date || batch.created_at) }}</p>
              <p class="mt-2 text-xs text-slate-500">{{ batch.applicant_count || batch.batch_size || '–' }} applicants</p>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </section>
</template>
