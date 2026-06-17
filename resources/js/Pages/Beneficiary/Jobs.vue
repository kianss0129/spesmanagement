<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const jobs = ref([])
const assignedPlacement = ref(null)
const selectedJob = ref(null)
const loading = ref(true)

const placementStatus = computed(() => {
  if (assignedPlacement.value?.company) return 'Assigned'
  if (jobs.value.length > 0) return 'For matching'
  return 'Not yet assigned'
})

const statusDescription = computed(() => {
  if (placementStatus.value === 'Assigned') {
    return 'You have an assigned SPES placement. Review the employer and work details below.'
  }

  if (placementStatus.value === 'For matching') {
    return 'CPESO is matching beneficiaries with available opportunities. You may review job posts while waiting.'
  }

  return 'You are not yet assigned to an employer. Available opportunities will appear here once posted.'
})

const nextAction = computed(() => {
  if (placementStatus.value === 'Assigned') {
    return 'Coordinate with your assigned employer and monitor your schedule and DTR requirements.'
  }

  if (availableJobs.value.length > 0) {
    return 'Review available SPES opportunities and open a job card for details.'
  }

  return 'Keep your requirements updated and wait for CPESO placement updates.'
})

const availableJobs = computed(() =>
  jobs.value
    .filter((job) => jobStatus(job) === 'Open')
    .sort((a, b) => new Date(a.closing_date || '2999-12-31') - new Date(b.closing_date || '2999-12-31'))
)

const statusClasses = computed(() => ({
  'Not yet assigned': 'bg-slate-100 text-slate-700 border-slate-200',
  'For matching': 'bg-amber-100 text-amber-800 border-amber-200',
  Assigned: 'bg-green-100 text-green-800 border-green-200',
  Completed: 'bg-blue-100 text-blue-800 border-blue-200',
}))

function jobStatus(job) {
  const slots = Number(job.slots) || 0
  const assigned = Number(job.assigned_count) || 0
  const closingDate = job.closing_date ? new Date(job.closing_date) : null

  if (slots > 0 && assigned >= slots) return 'Full'
  if (closingDate && closingDate < new Date()) return 'Closed'
  return 'Open'
}

function availableSlots(job) {
  const slots = Number(job.slots) || 0
  const assigned = Number(job.assigned_count) || 0
  return Math.max(slots - assigned, 0)
}

function employerName(job) {
  return job.employer?.company_name || job.employer?.name || job.employer_name || 'Employer'
}

function skillList(job) {
  if (Array.isArray(job.skills)) {
    return job.skills.map((skill) => skill.name || skill).filter(Boolean)
  }

  if (typeof job.required_skills === 'string') {
    return job.required_skills.split(',').map((skill) => skill.trim()).filter(Boolean)
  }

  return []
}

function formatDate(value) {
  if (!value) return 'To be announced'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

async function loadPlacement() {
  loading.value = true

  const [jobsResult, assignedResult] = await Promise.allSettled([
    axios.get('/api/beneficiary/jobs'),
    axios.get('/api/beneficiary/assigned-employer'),
  ])

  jobs.value = Array.isArray(jobsResult.value?.data) ? jobsResult.value.data : []
  assignedPlacement.value = assignedResult.value?.data || null
  loading.value = false
}

onMounted(loadPlacement)
</script>

<template>
  <Head title="Job Placement" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <Link
        href="/beneficiary"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Dashboard
      </Link>

      <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">SPES Placement</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Job Placement</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          View available SPES opportunities and your assigned placement.
        </p>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div>
            <p class="text-sm font-semibold text-slate-600">Placement status</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
              <h2 class="text-2xl font-bold text-slate-900">{{ placementStatus }}</h2>
              <span
                class="rounded-full border px-3 py-1 text-xs font-semibold"
                :class="statusClasses[placementStatus]"
              >
                {{ placementStatus }}
              </span>
            </div>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">{{ statusDescription }}</p>
          </div>

          <div class="rounded-lg bg-blue-50 p-4 text-sm text-blue-900 lg:max-w-sm">
            <p class="font-semibold">What to do next</p>
            <p class="mt-2 leading-6">{{ nextAction }}</p>
          </div>
        </div>
      </section>

      <section v-if="assignedPlacement" class="mt-6 rounded-lg border border-green-200 bg-white shadow-sm">
        <div class="border-b border-green-100 p-5 sm:p-6">
          <h2 class="text-lg font-bold text-slate-900">Assigned Employer</h2>
          <p class="mt-1 text-sm text-slate-500">Your current SPES placement details.</p>
        </div>

        <div class="grid gap-4 p-5 sm:grid-cols-2 sm:p-6 lg:grid-cols-4">
          <div class="rounded-lg bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Employer</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.company || 'Assigned employer' }}</p>
          </div>
          <div class="rounded-lg bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Job / Position</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.job_title || 'Assigned position' }}</p>
          </div>
          <div class="rounded-lg bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Work Schedule</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.work_schedule || 'To be announced' }}</p>
          </div>
          <div class="rounded-lg bg-slate-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Work Location</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.location || 'To be announced' }}</p>
          </div>
          <div class="rounded-lg bg-slate-50 p-4 sm:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Contact Person</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.contact_person || 'To be announced' }}</p>
          </div>
          <div class="rounded-lg bg-slate-50 p-4 sm:col-span-2">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Status</p>
            <p class="mt-2 font-bold text-slate-900">{{ assignedPlacement.status || 'Assigned' }}</p>
          </div>
        </div>
      </section>

      <section v-else class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 sm:p-6">
          <h2 class="text-lg font-bold text-slate-900">Available Jobs</h2>
          <p class="mt-1 text-sm text-slate-500">Review available SPES opportunities while waiting for placement.</p>
        </div>

        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">Loading job opportunities...</div>
        <div v-else-if="availableJobs.length === 0" class="p-8 text-center text-sm text-slate-500">
          No available jobs posted yet.
        </div>

        <div v-else class="grid gap-4 p-5 sm:p-6 lg:grid-cols-2">
          <article
            v-for="job in availableJobs"
            :key="job.id"
            class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
          >
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <h3 class="text-lg font-bold text-slate-900">{{ job.title }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ employerName(job) }}</p>
              </div>
              <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800">
                {{ jobStatus(job) }}
              </span>
            </div>

            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
              <div class="rounded-lg bg-slate-50 p-3">
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Location</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ job.location || 'To be announced' }}</dd>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Slots</dt>
                <dd class="mt-1 font-medium text-slate-800">{{ availableSlots(job) }} available</dd>
              </div>
            </dl>

            <div v-if="skillList(job).length" class="mt-4 flex flex-wrap gap-2">
              <span
                v-for="skill in skillList(job)"
                :key="skill"
                class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"
              >
                {{ skill }}
              </span>
            </div>

            <p class="mt-4 line-clamp-3 text-sm leading-6 text-slate-600">
              {{ job.description || 'No description posted.' }}
            </p>

            <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <p class="text-xs text-slate-500">Closing: {{ formatDate(job.closing_date) }}</p>
              <button
                type="button"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                @click="selectedJob = job"
              >
                View
              </button>
            </div>
          </article>
        </div>
      </section>

      <div
        v-if="selectedJob"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
        @click.self="selectedJob = null"
      >
        <div class="w-full max-w-2xl rounded-lg bg-white shadow-2xl">
          <div class="flex items-start justify-between gap-4 border-b border-slate-200 p-5">
            <div>
              <p class="text-sm text-slate-500">Job opportunity</p>
              <h2 class="text-2xl font-bold text-slate-900">{{ selectedJob.title }}</h2>
              <p class="mt-1 text-sm text-slate-500">{{ employerName(selectedJob) }}</p>
            </div>
            <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-slate-700" @click="selectedJob = null">
              Close
            </button>
          </div>

          <div class="space-y-4 p-5">
            <p class="text-sm leading-6 text-slate-700">{{ selectedJob.description || 'No description posted.' }}</p>
            <div class="grid gap-3 sm:grid-cols-2">
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Location</p>
                <p class="mt-1 font-semibold text-slate-900">{{ selectedJob.location || 'To be announced' }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Slots</p>
                <p class="mt-1 font-semibold text-slate-900">{{ availableSlots(selectedJob) }} available</p>
              </div>
            </div>
            <p class="rounded-lg bg-blue-50 p-4 text-sm leading-6 text-blue-900">
              To apply or confirm interest, follow CPESO instructions or wait for matching updates in your Messages and Application page.
            </p>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
