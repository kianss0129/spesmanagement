<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'

const reports = ref([])
const loading = ref(true)
const reviewing = ref(false)
const selectedReport = ref(null)
const reviewRemarks = ref('')
const message = ref('')
const error = ref('')
const statusFilter = ref('all')
const search = ref('')

const filteredReports = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return reports.value.filter((report) => {
    const haystack = [
      report.beneficiary_name,
      report.title,
      report.accomplishments,
      report.status,
      report.work_date,
    ].join(' ').toLowerCase()

    const matchesSearch = !keyword || haystack.includes(keyword)
    const matchesStatus = statusFilter.value === 'all' || report.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

async function loadReports() {
  const res = await axios.get('/employer/work-outputs')
  reports.value = Array.isArray(res.data) ? res.data : []
}

function openReview(report) {
  selectedReport.value = report
  reviewRemarks.value = report.review_remarks || ''
  message.value = ''
  error.value = ''
}

function closeReview() {
  selectedReport.value = null
  reviewRemarks.value = ''
}

async function submitReview(action) {
  if (!selectedReport.value) return

  if (action !== 'approve' && !reviewRemarks.value.trim()) {
    error.value = 'Remarks are required when rejecting or requesting correction.'
    return
  }

  const endpoint = `/employer/work-outputs/${selectedReport.value.id}/${action === 'approve' ? 'approve' : 'reject'}`
  const payload = {
    review_remarks: reviewRemarks.value || null,
    status: action === 'needs_correction' ? 'needs_correction' : 'rejected',
  }

  try {
    reviewing.value = true
    error.value = ''
    await axios.patch(endpoint, payload)
    message.value = action === 'approve' ? 'Daily report approved.' : 'Daily report returned.'
    closeReview()
    await loadReports()
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to review daily report.'
  } finally {
    reviewing.value = false
  }
}

function statusLabel(status) {
  const value = String(status || 'submitted').replace(/_/g, ' ')
  return value.replace(/\b\w/g, (char) => char.toUpperCase())
}

function statusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value === 'approved') return 'bg-green-100 text-green-800'
  if (value === 'rejected' || value === 'needs_correction') return 'bg-red-100 text-red-800'
  if (value === 'under_review') return 'bg-amber-100 text-amber-800'
  return 'bg-blue-100 text-blue-800'
}

function formatDate(value) {
  if (!value) return 'No date'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function formatHours(value) {
  const hours = Number(value) || 0
  return `${hours.toFixed(2)} hrs`
}

onMounted(async () => {
  loading.value = true
  await loadReports()
  loading.value = false
})
</script>

<template>
  <Head title="Daily Accomplishment Reports" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
      <button
        type="button"
        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
        @click="router.visit('/employer')"
      >
        Back to Dashboard
      </button>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Employer Review</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Daily Accomplishment Reports</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Review reports submitted by beneficiaries assigned to your employer account.
        </p>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="grid gap-3 md:grid-cols-[1fr_220px]">
          <input
            v-model="search"
            type="search"
            placeholder="Search beneficiary, title, accomplishments..."
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          >
          <select
            v-model="statusFilter"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option value="all">All Statuses</option>
            <option value="submitted">Submitted</option>
            <option value="under_review">Under Review</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="needs_correction">Needs Correction</option>
          </select>
        </div>
      </section>

      <div
        v-if="message"
        class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-800"
      >
        {{ message }}
      </div>

      <div
        v-if="error"
        class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800"
      >
        {{ error }}
      </div>

      <section class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[1fr_0.8fr_1.1fr_0.7fr_0.8fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
          <span>Beneficiary</span>
          <span>Work Date</span>
          <span>Title</span>
          <span>Hours</span>
          <span>Status</span>
          <span>Action</span>
        </div>

        <div
          v-if="loading"
          class="p-8 text-center text-sm text-slate-500"
        >
          Loading daily reports...
        </div>

        <div
          v-else-if="filteredReports.length === 0"
          class="p-8 text-center text-sm text-slate-500"
        >
          No daily reports found.
        </div>

        <template v-else>
          <article
            v-for="report in filteredReports"
            :key="report.id"
            class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1fr_0.8fr_1.1fr_0.7fr_0.8fr_1fr] xl:items-center"
          >
            <p class="font-semibold text-slate-900">
              {{ report.beneficiary_name }}
            </p>

            <p class="text-sm text-slate-700">
              {{ formatDate(report.work_date) }}
            </p>

            <div>
              <p class="font-semibold text-slate-900">
                {{ report.title || 'Daily report' }}
              </p>
              <p class="mt-1 line-clamp-2 text-sm text-slate-500">
                {{ report.accomplishments }}
              </p>
            </div>

            <p class="text-sm font-semibold text-slate-900">
              {{ formatHours(report.hours_worked) }}
            </p>

            <span
              class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
              :class="statusClass(report.status)"
            >
              {{ statusLabel(report.status) }}
            </span>

            <div class="flex flex-wrap gap-2">
              <button
                type="button"
                class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                @click="openReview(report)"
              >
                View
              </button>
            </div>
          </article>
        </template>
      </section>
    </div>

    <div
      v-if="selectedReport"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="closeReview"
    >
      <form
        class="max-h-[92vh] w-full max-w-2xl overflow-auto rounded-lg bg-white p-5 shadow-xl sm:p-6"
        @submit.prevent="submitReview('approve')"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
              Daily Report
            </p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">
              {{ selectedReport.title || 'Daily report' }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
              {{ selectedReport.beneficiary_name }} / {{ formatDate(selectedReport.work_date) }}
            </p>
          </div>

          <button
            type="button"
            class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700"
            @click="closeReview"
          >
            Close
          </button>
        </div>

        <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
          <div>
            <dt class="font-semibold text-slate-500">Hours Worked</dt>
            <dd class="mt-1 text-slate-900">
              {{ formatHours(selectedReport.hours_worked) }}
            </dd>
          </div>

          <div>
            <dt class="font-semibold text-slate-500">Status</dt>
            <dd class="mt-1 text-slate-900">
              {{ statusLabel(selectedReport.status) }}
            </dd>
          </div>
        </dl>

        <div class="mt-4">
          <p class="text-sm font-semibold text-slate-500">
            Accomplishments
          </p>
          <p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-800">
            {{ selectedReport.accomplishments }}
          </p>
        </div>

        <a
          v-if="selectedReport.file_url"
          :href="selectedReport.file_url"
          target="_blank"
          rel="noopener noreferrer"
          class="mt-4 inline-flex text-sm font-semibold text-blue-700"
        >
          View Attachment
        </a>

        <div class="mt-4">
          <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">
            Review Remarks
          </label>
          <textarea
            v-model="reviewRemarks"
            rows="4"
            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          ></textarea>
        </div>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button
            type="button"
            :disabled="reviewing"
            class="rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-100 disabled:opacity-50"
            @click="submitReview('needs_correction')"
          >
            Reject / Request Correction
          </button>

          <button
            type="submit"
            :disabled="reviewing"
            class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-50"
          >
            {{ reviewing ? 'Saving...' : 'Approve' }}
          </button>
        </div>
      </form>
    </div>
  </main>
</template>