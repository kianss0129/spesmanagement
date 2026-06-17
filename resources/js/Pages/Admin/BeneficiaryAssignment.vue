<template>
  <div class="min-h-screen bg-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">

      <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50"
        @click="goBack"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
      </button>

      <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Matching</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Placement</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Match approved beneficiaries with employers and available SPES job opportunities.
        </p>
      </header>

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="card in summaryCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
          <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
        </div>
      </section>

      <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-3 lg:grid-cols-5">
          <input
            v-model="filters.search"
            type="search"
            placeholder="Search name or email..."
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
            @input="debouncedRefresh"
          >
          <input
            v-model="skillText"
            type="search"
            placeholder="Skill"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
            @input="updateSkillFilter"
          >
          <select v-model="filters.category" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none" @change="refreshBeneficiaries">
            <option value="">All Categories</option>
            <option value="student">Student</option>
            <option value="out_of_school_youth">Out-of-school youth</option>
            <option value="dependent_of_displaced_worker">Dependent of Displaced Worker</option>
          </select>
          <select v-model="filters.employment_status" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none" @change="refreshBeneficiaries">
            <option value="">All Statuses</option>
            <option value="unemployed">Awaiting Assignment</option>
            <option value="underemployed">For Matching</option>
            <option value="assigned">Assigned</option>
            <option value="completed">Completed</option>
          </select>
          <input
            v-model="filters.location"
            type="search"
            placeholder="Location"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
            @input="debouncedRefresh"
          >
        </div>

        <div class="mt-4 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
          <div>
            <label class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Suggested job matching</label>
            <select
              v-model="selectedJobId"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
              @change="getMatchingSuggestions"
            >
              <option value="">Choose a job to show suggestions</option>
              <option v-for="job in availableJobs" :key="job.id" :value="job.id">
                {{ job.title }} - {{ job.employer?.company_name || employerNameById(job.employer_id) }} ({{ job.slots || 0 }} slots)
              </option>
            </select>
          </div>
          <button
            type="button"
            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            @click="resetFilters"
          >
            Reset Filters
          </button>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[1.1fr_0.8fr_1fr_1fr_1.2fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
          <span>Name</span>
          <span>Category</span>
          <span>Skills</span>
          <span>Preferred Location</span>
          <span>Suggested Employer / Job</span>
          <span>Action</span>
        </div>

        <div v-if="loading" class="px-5 py-12 text-center text-sm text-slate-500">Loading beneficiaries...</div>

        <template v-else>
          <div
            v-for="beneficiary in beneficiaries"
            :key="beneficiary.id"
            class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.1fr_0.8fr_1fr_1fr_1.2fr_1fr] xl:items-center"
          >
            <div>
              <p class="font-semibold text-slate-900">{{ beneficiaryName(beneficiary) }}</p>
              <p class="mt-1 text-xs text-slate-500">{{ beneficiary.email || 'No email' }}</p>
            </div>
            <p class="text-sm capitalize text-slate-700">{{ category(beneficiary) }}</p>
            <p class="text-sm text-slate-700">{{ skills(beneficiary) }}</p>
            <p class="text-sm text-slate-700">{{ preferredLocation(beneficiary) }}</p>
            <div class="text-sm text-slate-700">
              <p class="font-semibold">{{ suggestedJob(beneficiary).employer }}</p>
              <p class="mt-1 text-xs text-slate-500">{{ suggestedJob(beneficiary).title }}</p>
              <p v-if="suggestedJob(beneficiary).match_score !== null" class="mt-2 w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">
                {{ suggestedJob(beneficiary).match_score }}% match
              </p>
            </div>
            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
                @click="quickAssign(beneficiary)"
              >
                Assign
              </button>
              <button
                type="button"
                class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                @click="viewBeneficiaryProfile(beneficiary.id)"
              >
                View Profile
              </button>
            </div>
          </div>
        </template>

        <div v-if="beneficiaries.length === 0 && !loading" class="px-5 py-12 text-center">
          <p class="text-sm font-semibold text-slate-700">No beneficiaries found.</p>
          <p class="mt-1 text-sm text-slate-500">Try adjusting skill, category, status, or location filters.</p>
        </div>
      </section>

      <div v-if="totalPages > 1" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === 1" @click="previousPage">Previous</button>
        <span class="text-sm text-slate-600">Page {{ currentPage }} of {{ totalPages }}</span>
        <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === totalPages" @click="nextPage">Next</button>
      </div>
    </div>

    <BeneficiaryProfileModal
      v-if="showProfileModal"
      :beneficiary-id="selectedBeneficiaryForProfile"
      @close="showProfileModal = false"
      @assign="handleProfileAssignment"
    />

    <div v-if="showAssignmentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Assign Beneficiary</h3>
        <p class="mt-2 text-sm text-slate-600">
          Assigning <span class="font-semibold">{{ beneficiaryName(selectedBeneficiaryForAssignment) }}</span>
        </p>

        <div class="mt-5 space-y-4">
          <div>
            <label class="text-sm font-semibold text-slate-700">Employer</label>
            <select
              v-model="assignmentForm.employer_id"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-green-500 focus:outline-none"
              @change="handleEmployerChange"
            >
              <option value="">Choose employer</option>
              <option v-for="employer in employers" :key="employer.id" :value="employer.id">{{ employer.company_name }}</option>
            </select>
          </div>
          <div>
            <label class="text-sm font-semibold text-slate-700">Job</label>
            <select
              v-model="assignmentForm.job_listing_id"
              class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-green-500 focus:outline-none"
              :disabled="!assignmentForm.employer_id || availableJobsForSelectedEmployer.length === 0"
            >
              <option value="">Choose job</option>
              <option v-for="job in availableJobsForSelectedEmployer" :key="job.id" :value="job.id">
                {{ job.title }} ({{ job.slots || 0 }} slots)
              </option>
            </select>
            <p v-if="assignmentForm.employer_id && availableJobsForSelectedEmployer.length === 0" class="mt-2 text-sm text-amber-700">
              No available job slots for this employer.
            </p>
          </div>

          <div v-if="assignmentError" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ assignmentError }}
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2">
          <button
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50"
            :disabled="assignmentSubmitting"
            @click="showAssignmentModal = false"
          >
            Cancel
          </button>
          <button
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
            :disabled="assignmentSubmitting || !assignmentForm.employer_id || !assignmentForm.job_listing_id"
            @click="submitAssignment"
          >
            {{ assignmentSubmitting ? 'Assigning...' : 'Confirm Assignment' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import BeneficiaryProfileModal from '@/Components/Admin/BeneficiaryProfileModal.vue'

function goBack() {
  if (window.history.length > 1) {
    window.history.back()
  } else {
    window.location.href = '/dashboard'
  }
}

const beneficiaries = ref([])
const matchingSuggestions = ref([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const selectedJobId = ref('')
const availableJobs = ref([])
const employers = ref([])
const skillText = ref('')
let refreshTimer = null

const filters = ref({
  skills: [],
  employment_status: '',
  location: '',
  category: '',
  search: '',
  exclude_assigned: true,
})

const showAssignmentModal = ref(false)
const showProfileModal = ref(false)
const selectedBeneficiaryForAssignment = ref(null)
const selectedBeneficiaryForProfile = ref(null)
const assignmentError = ref('')
const assignmentSubmitting = ref(false)
const assignmentForm = ref({
  employer_id: '',
  job_listing_id: '',
})

const queryParams = computed(() => {
  const params = new URLSearchParams()
  if (filters.value.skills.length > 0) params.append('skills', filters.value.skills.join(','))
  if (filters.value.employment_status) params.append('employment_status', filters.value.employment_status)
  if (filters.value.location) params.append('location', filters.value.location)
  if (filters.value.category) params.append('category', filters.value.category)
  if (filters.value.search) params.append('search', filters.value.search)
  if (filters.value.exclude_assigned) params.append('exclude_assigned', 'true')
  params.append('page', currentPage.value)
  return params.toString()
})

const availableJobsForSelectedEmployer = computed(() => {
  if (!assignmentForm.value.employer_id) return availableJobs.value
  return jobsForEmployer(assignmentForm.value.employer_id)
})

const summaryCards = computed(() => {
  const assigned = beneficiaries.value.filter((beneficiary) => String(beneficiary.employment_status || '').toLowerCase() === 'assigned').length
  const completed = beneficiaries.value.filter((beneficiary) => String(beneficiary.employment_status || beneficiary.status || '').toLowerCase().includes('completed')).length
  const openSlotEmployers = new Set(availableJobs.value.filter((job) => Number(job.slots || 0) > 0).map((job) => job.employer_id)).size

  return [
    { label: 'Awaiting Assignment', value: Math.max(beneficiaries.value.length - assigned - completed, 0), description: 'Beneficiaries ready for placement.' },
    { label: 'Assigned', value: assigned, description: 'Beneficiaries with active placement.' },
    { label: 'Employers with Open Slots', value: openSlotEmployers, description: 'Employers with available job slots.' },
    { label: 'Completed', value: completed, description: 'Beneficiaries marked completed.' },
  ]
})

async function refreshBeneficiaries() {
  loading.value = true
  try {
    const response = await fetch(`/admin/beneficiaries?${queryParams.value}`)
    const data = await response.json()
    beneficiaries.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch (error) {
    console.error('Error loading beneficiaries:', error)
  } finally {
    loading.value = false
  }
}

function debouncedRefresh() {
  clearTimeout(refreshTimer)
  refreshTimer = setTimeout(() => {
    currentPage.value = 1
    refreshBeneficiaries()
  }, 250)
}

function updateSkillFilter() {
  filters.value.skills = skillText.value ? [skillText.value] : []
  debouncedRefresh()
}

function resetFilters() {
  skillText.value = ''
  filters.value = {
    skills: [],
    employment_status: '',
    location: '',
    category: '',
    search: '',
    exclude_assigned: true,
  }
  currentPage.value = 1
  refreshBeneficiaries()
}

async function getMatchingSuggestions() {
  if (!selectedJobId.value) {
    matchingSuggestions.value = []
    return
  }

  try {
    const response = await fetch(`/admin/jobs/${selectedJobId.value}/matching-beneficiaries`)
    const data = await response.json()
    matchingSuggestions.value = data.suggestions || []
  } catch (error) {
    console.error('Error getting matching suggestions:', error)
    matchingSuggestions.value = []
  }
}

async function loadAvailableJobs() {
  try {
    const response = await fetch('/admin/jobs')
    const data = await response.json()
    availableJobs.value = (Array.isArray(data) ? data : data.data || []).map(normalizeJob).filter(isJobAvailable)
  } catch (error) {
    console.error('Error loading jobs:', error)
  }
}

async function loadEmployers() {
  try {
    const response = await fetch('/admin/employers')
    const data = await response.json()
    employers.value = (Array.isArray(data) ? data : data.data || []).map(normalizeEmployer)
  } catch (error) {
    console.error('Error loading employers:', error)
  }
}

function quickAssign(beneficiary) {
  selectedBeneficiaryForAssignment.value = beneficiary
  const suggestion = suggestedJob(beneficiary)
  assignmentError.value = ''
  assignmentSubmitting.value = false
  assignmentForm.value.employer_id = suggestion.employer_id || ''
  assignmentForm.value.job_listing_id = suggestion.job_id || ''
  keepSelectedJobIfAvailable()
  showAssignmentModal.value = true
}

function viewBeneficiaryProfile(beneficiaryId) {
  selectedBeneficiaryForProfile.value = beneficiaryId
  showProfileModal.value = true
}

function handleProfileAssignment() {
  showProfileModal.value = false
  refreshBeneficiaries()
}

async function submitAssignment() {
  assignmentError.value = ''
  assignmentSubmitting.value = true

  try {
    await axios.post(`/admin/beneficiaries/${selectedBeneficiaryForAssignment.value.id}/assign`, {
      employer_id: assignmentForm.value.employer_id,
      job_listing_id: assignmentForm.value.job_listing_id,
    })

    showAssignmentModal.value = false
    refreshBeneficiaries()
  } catch (error) {
    if (import.meta.env.DEV) {
      console.error(error.response?.data)
    }

    assignmentError.value = assignmentErrorMessage(error)
  } finally {
    assignmentSubmitting.value = false
  }
}

function previousPage() {
  if (currentPage.value > 1) {
    currentPage.value--
    refreshBeneficiaries()
  }
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    refreshBeneficiaries()
  }
}

function beneficiaryName(beneficiary) {
  return [beneficiary?.first_name, beneficiary?.last_name].filter(Boolean).join(' ') || beneficiary?.name || 'Unnamed beneficiary'
}

function category(beneficiary) {
  const value = beneficiary?.category || beneficiary?.beneficiary_type || beneficiary?.user?.beneficiary_type || beneficiary?.education_level || 'student'
  const normalized = String(value).toLowerCase().replace(/\s+/g, '_')

  if (normalized === 'student') return 'Student'
  if (normalized === 'osy' || normalized === 'out_of_school_youth') return 'Out-of-School Youth'
  if (normalized === 'dependent' || normalized === 'dependent_of_displaced_worker') return 'Dependent of Displaced Worker'

  return value
}

function skills(beneficiary) {
  if (Array.isArray(beneficiary?.skills)) {
    const skillNames = beneficiary.skills
      .map((skill) => typeof skill === 'string' ? skill : skill?.name)
      .filter(Boolean)

    return skillNames.length ? skillNames.join(', ') : 'Not specified'
  }

  return beneficiary?.skills || beneficiary?.skill || 'Not specified'
}

function assignmentErrorMessage(error) {
  const responseData = error.response?.data
  const validationErrors = responseData?.errors || responseData?.error

  if (typeof responseData?.message === 'string') return responseData.message
  if (typeof validationErrors === 'string') return validationErrors

  if (validationErrors && typeof validationErrors === 'object') {
    return Object.values(validationErrors)
      .flat()
      .filter(Boolean)
      .join('\n') || 'Error assigning beneficiary. Please try again.'
  }

  return 'Error assigning beneficiary. Please try again.'
}

function preferredLocation(beneficiary) {
  return [beneficiary?.barangay, beneficiary?.city].filter(Boolean).join(', ') || beneficiary?.preferred_location || 'Not specified'
}

function employerNameById(id) {
  return employers.value.find((employer) => Number(employer.id) === Number(id))?.company_name || 'Employer'
}

function handleEmployerChange() {
  assignmentError.value = ''
  keepSelectedJobIfAvailable()
}

function keepSelectedJobIfAvailable() {
  const jobs = jobsForEmployer(assignmentForm.value.employer_id)
  const selectedJobStillAvailable = jobs.some((job) => Number(job.id) === Number(assignmentForm.value.job_listing_id))

  if (!selectedJobStillAvailable) {
    assignmentForm.value.job_listing_id = ''
  }
}

function normalizeEmployer(employer) {
  const employerJobs = employer.available_jobs || employer.job_listings || employer.jobListings || employer.jobs || []

  return {
    ...employer,
    available_jobs: employerJobs.map((job) => normalizeJob({ ...job, employer_id: job.employer_id || employer.id })).filter(isJobAvailable),
  }
}

function normalizeJob(job) {
  if (!job?.id) return null

  return {
    ...job,
    id: job.id,
    title: job.title || job.job_title || 'Untitled job',
    slots: Number(job.available_slots ?? job.slots ?? 0),
    available_slots: Number(job.available_slots ?? job.slots ?? 0),
    closing_date: job.closing_date || null,
    employer_id: job.employer_id || job.employer?.id || '',
  }
}

function isJobAvailable(job) {
  if (!job || Number(job.available_slots ?? job.slots ?? 0) <= 0) return false
  if (!job.closing_date) return true

  return String(job.closing_date).slice(0, 10) >= new Date().toISOString().slice(0, 10)
}

function jobsForEmployer(employerId) {
  const employer = employers.value.find((item) => Number(item.id) === Number(employerId))
  const employerJobs = employer?.available_jobs || []
  const globalJobs = availableJobs.value.filter((job) => Number(job.employer_id) === Number(employerId))
  const jobsById = new Map()

  ;[...employerJobs, ...globalJobs]
    .map(normalizeJob)
    .filter(isJobAvailable)
    .forEach((job) => jobsById.set(Number(job.id), job))

  return Array.from(jobsById.values())
}

function suggestedJob(beneficiary) {
  const matchedSuggestion = matchingSuggestions.value.find((item) => Number(item.id) === Number(beneficiary?.id) || Number(item.beneficiary?.id) === Number(beneficiary?.id))
  const selectedJob = availableJobs.value.find((job) => Number(job.id) === Number(selectedJobId.value))
  const job = selectedJob || availableJobs.value[0] || {}

  return {
    title: matchedSuggestion?.job_title || job.title || 'No suggested job',
    employer: matchedSuggestion?.employer_name || job.employer?.company_name || employerNameById(job.employer_id) || 'No suggested employer',
    employer_id: job.employer_id || matchedSuggestion?.employer_id || '',
    job_id: job.id || matchedSuggestion?.job_id || '',
    match_score: matchedSuggestion?.match_score ?? null,
  }
}

onMounted(() => {
  refreshBeneficiaries()
  loadAvailableJobs()
  loadEmployers()
})
</script>
