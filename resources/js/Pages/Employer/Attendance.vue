<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()

const records = ref([])
const employerJobs = ref(page.props.employerJobs || [])
const loading = ref(false)

const selectedJob = ref('')
const selectedDay = ref('')
const selectedMonth = ref('')
const selectedYear = ref('')
const searchName = ref('')
const pendingOnly = ref(false)

const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const showProofModal = ref(false)
const selectedProof = ref(null)
const reviewModalOpen = ref(false)
const selectedRecord = ref(null)
const reviewRemarks = ref('')
const reviewing = ref(false)

const filteredRecords = computed(() => {
  return [...records.value]
    .filter((record) => {
      if (!record?.date) return false

      const date = new Date(record.date)
      if (Number.isNaN(date.getTime())) return false

      const day = String(date.getDate()).padStart(2, '0')
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const year = String(date.getFullYear())
      const beneficiary = String(record.beneficiary_name || '').toLowerCase()
      const job = String(record.job_title || '').toLowerCase().trim()
      const selected = String(selectedJob.value || '').toLowerCase().trim()
      const status = String(record.review_status || 'pending').toLowerCase()

      return (!selectedJob.value || job === selected) &&
        (!selectedDay.value || selectedDay.value === day) &&
        (!selectedMonth.value || selectedMonth.value === month) &&
        (!selectedYear.value || selectedYear.value === year) &&
        (!searchName.value || beneficiary.includes(searchName.value.toLowerCase().trim())) &&
        (!pendingOnly.value || status === 'pending')
    })
    .sort((a, b) => new Date(b.date) - new Date(a.date))
})

const summaryCards = computed(() => {
  const all = records.value
  return [
    {
      label: 'Total DTRs',
      value: all.length,
      description: 'All submitted time records.',
      class: 'text-slate-950',
    },
    {
      label: 'Pending Review',
      value: countByStatus('pending'),
      description: 'Waiting for employer action.',
      class: 'text-amber-600',
    },
    {
      label: 'Approved',
      value: countByStatus('approved'),
      description: 'Reviewed and accepted.',
      class: 'text-green-600',
    },
    {
      label: 'Needs Correction',
      value: countByStatus('needs_correction'),
      description: 'Returned for updates.',
      class: 'text-orange-600',
    },
    {
      label: 'Rejected',
      value: countByStatus('rejected'),
      description: 'Declined after review.',
      class: 'text-red-600',
    },
  ]
})

function countByStatus(status) {
  return records.value.filter((record) => String(record.review_status || 'pending').toLowerCase() === status).length
}

function goBack() {
  window.history.back()
}

function triggerToast(message, type = 'success') {
  toastMessage.value = message
  toastType.value = type
  showToast.value = true

  setTimeout(() => {
    showToast.value = false
  }, 3500)
}

function backendMessage(error, fallback) {
  const data = error.response?.data
  const errors = data?.errors || data?.error

  if (typeof data?.message === 'string') return data.message
  if (typeof errors === 'string') return errors

  if (errors && typeof errors === 'object') {
    return Object.values(errors).flat().filter(Boolean).join('\n') || fallback
  }

  return fallback
}

function getProofUrl(proof) {
  if (!proof) return null
  const value = String(proof).trim()
  if (!value) return null
  if (value.startsWith('http://') || value.startsWith('https://') || value.startsWith('/storage/')) return value
  if (value.startsWith('storage/')) return `/${value}`
  return `/storage/${value.replace(/^\/+/, '')}`
}

function getInProof(record) {
  return record?.notes_in_url ||
    record?.time_in_proof_url ||
    record?.proof_in_url ||
    record?.in_proof_url ||
    record?.notes_in ||
    record?.proof_in ||
    record?.time_in_proof ||
    null
}

function getOutProof(record) {
  return record?.notes_out_url ||
    record?.time_out_proof_url ||
    record?.proof_out_url ||
    record?.out_proof_url ||
    record?.notes_out ||
    record?.proof_out ||
    record?.time_out_proof ||
    null
}

function openProof(proof) {
  const url = getProofUrl(proof)
  if (!url) {
    triggerToast('Proof file is not available', 'error')
    return
  }

  selectedProof.value = url
  showProofModal.value = true
  document.body.style.overflow = 'hidden'
}

function closeProof() {
  showProofModal.value = false
  selectedProof.value = null
  document.body.style.overflow = 'auto'
}

function isImageProof(url) {
  return String(url || '').match(/\.(png|jpg|jpeg|webp|gif)$/i)
}

function isPdfProof(url) {
  return String(url || '').match(/\.(pdf)$/i)
}

function handleEsc(event) {
  if (event.key === 'Escape') {
    closeProof()
    closeReview()
  }
}

function formatDate(date) {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

function formatTime(time) {
  if (!time) return 'N/A'
  return new Date(`1970-01-01T${time}`).toLocaleTimeString([], {
    hour: '2-digit',
    minute: '2-digit',
  })
}

function totalHours(record) {
  const explicit = record?.total_hours || record?.hours || record?.rendered_hours
  if (explicit) return explicit
  if (!record?.time_in || !record?.time_out) return 'N/A'

  const start = new Date(`1970-01-01T${record.time_in}`)
  const end = new Date(`1970-01-01T${record.time_out}`)
  const diff = (end - start) / 36e5

  return Number.isFinite(diff) && diff >= 0 ? `${diff.toFixed(2)} hrs` : 'N/A'
}

function reviewStatusClass(status) {
  const value = String(status || 'pending').toLowerCase()
  if (value === 'approved') return 'bg-green-100 text-green-700'
  if (value === 'needs_correction') return 'bg-amber-100 text-amber-700'
  if (value === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-slate-100 text-slate-700'
}

function reviewStatusLabel(status) {
  return String(status || 'pending')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

function openReview(record) {
  selectedRecord.value = record
  reviewRemarks.value = record.review_remarks || ''
  reviewModalOpen.value = true
}

function closeReview() {
  reviewModalOpen.value = false
  selectedRecord.value = null
  reviewRemarks.value = ''
}

async function reloadData(silent = false) {
  loading.value = true

  try {
    const response = await axios.get('/employer/attendance/data')
    const fetched = response.data.records || response.data.data || response.data || []
    records.value = fetched.filter((record) => record?.id)
    if (!silent) triggerToast('DTR records refreshed.')
  } catch (error) {
    console.error(error)
    triggerToast(backendMessage(error, 'Failed to refresh DTR records'), 'error')
  } finally {
    loading.value = false
  }
}

async function submitReview(status) {
  if (!selectedRecord.value) return

  if (['needs_correction', 'rejected'].includes(status) && !reviewRemarks.value.trim()) {
    triggerToast('Remarks are required when requesting correction or rejecting DTR.', 'error')
    return
  }

  reviewing.value = true

  try {
    await axios.patch(`/employer/attendance/${selectedRecord.value.id}/review`, {
      review_status: status,
      review_remarks: reviewRemarks.value || null,
    })
    triggerToast('DTR review saved successfully.')
    closeReview()
    await reloadData(true)
  } catch (error) {
    console.error(error)
    triggerToast(backendMessage(error, 'Unable to save DTR review'), 'error')
  } finally {
    reviewing.value = false
  }
}

function setToday() {
  const today = new Date()
  selectedDay.value = String(today.getDate()).padStart(2, '0')
  selectedMonth.value = String(today.getMonth() + 1).padStart(2, '0')
  selectedYear.value = String(today.getFullYear())
}

function setThisMonth() {
  const today = new Date()
  selectedDay.value = ''
  selectedMonth.value = String(today.getMonth() + 1).padStart(2, '0')
  selectedYear.value = String(today.getFullYear())
}

function clearFilters() {
  selectedJob.value = ''
  selectedDay.value = ''
  selectedMonth.value = ''
  selectedYear.value = ''
  searchName.value = ''
  pendingOnly.value = false
}

onMounted(() => {
  window.addEventListener('keydown', handleEsc)
  reloadData(true)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleEsc)
  document.body.style.overflow = 'auto'
})
</script>

<template>
  <div class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex items-start gap-4">
            <button
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              @click="goBack"
            >
              Back
            </button>
            <div>
              <h1 class="text-3xl font-bold tracking-tight text-slate-950">Attendance / DTR Review</h1>
              <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Review daily time records submitted by assigned SPES beneficiaries.
              </p>
            </div>
          </div>

          <button
            type="button"
            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="loading"
            @click="reloadData()"
          >
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
        </div>
      </header>

      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <div
          v-for="card in summaryCards"
          :key="card.label"
          class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-bold" :class="card.class">{{ card.value }}</p>
          <p class="mt-2 text-sm text-slate-500">{{ card.description }}</p>
        </div>
      </section>

      <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="grid gap-4 lg:grid-cols-6">
          <div class="lg:col-span-2">
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Job</label>
            <select v-model="selectedJob" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
              <option value="">All jobs</option>
              <option v-for="job in employerJobs" :key="job.id" :value="job.title">{{ job.title }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Day</label>
            <select v-model="selectedDay" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
              <option value="">All days</option>
              <option v-for="day in 31" :key="day" :value="String(day).padStart(2, '0')">Day {{ day }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Month</label>
            <select v-model="selectedMonth" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
              <option value="">All months</option>
              <option value="01">Jan</option>
              <option value="02">Feb</option>
              <option value="03">Mar</option>
              <option value="04">Apr</option>
              <option value="05">May</option>
              <option value="06">Jun</option>
              <option value="07">Jul</option>
              <option value="08">Aug</option>
              <option value="09">Sep</option>
              <option value="10">Oct</option>
              <option value="11">Nov</option>
              <option value="12">Dec</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Year</label>
            <select v-model="selectedYear" class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
              <option value="">All years</option>
              <option v-for="year in 10" :key="year" :value="String(new Date().getFullYear() - (year - 1))">
                {{ new Date().getFullYear() - (year - 1) }}
              </option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold uppercase tracking-wide text-slate-500">Search beneficiary</label>
            <input
              v-model="searchName"
              type="search"
              class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
              placeholder="Name"
            />
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2">
          <button type="button" class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="setToday">
            Today
          </button>
          <button type="button" class="rounded-full bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100" @click="setThisMonth">
            This Month
          </button>
          <button
            type="button"
            class="rounded-full px-4 py-2 text-sm font-semibold"
            :class="pendingOnly ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700 hover:bg-amber-100'"
            @click="pendingOnly = !pendingOnly"
          >
            Pending Only
          </button>
          <button type="button" class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200" @click="clearFilters">
            Clear Filters
          </button>
        </div>
      </section>

      <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <h2 class="font-bold text-slate-950">DTR submissions</h2>
          <p class="text-sm text-slate-500">{{ filteredRecords.length }} record(s)</p>
        </div>

        <div v-if="loading" class="p-6">
          <div class="h-40 animate-pulse rounded-xl bg-slate-100"></div>
        </div>

        <div v-else-if="filteredRecords.length === 0" class="px-5 py-14 text-center">
          <p class="text-lg font-bold text-slate-800">No DTR records found for the selected filters.</p>
          <p class="mt-2 text-sm text-slate-500">Try clearing filters or refreshing the page.</p>
        </div>

        <div v-else>
          <div class="hidden overflow-x-auto lg:block">
            <table class="min-w-full divide-y divide-slate-200">
              <thead class="bg-slate-50 text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                <tr>
                  <th class="px-5 py-3">Beneficiary</th>
                  <th class="px-5 py-3">Job / Position</th>
                  <th class="px-5 py-3">Date</th>
                  <th class="px-5 py-3">Time In</th>
                  <th class="px-5 py-3">Time Out</th>
                  <th class="px-5 py-3">Total Hours</th>
                  <th class="px-5 py-3">Status</th>
                  <th class="px-5 py-3">Proof</th>
                  <th class="px-5 py-3">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 text-sm">
                <tr v-for="record in filteredRecords" :key="record.id" class="align-top hover:bg-slate-50">
                  <td class="px-5 py-4 font-semibold text-slate-900">{{ record.beneficiary_name || 'Unknown Beneficiary' }}</td>
                  <td class="px-5 py-4 text-slate-700">{{ record.job_title || 'No job assigned' }}</td>
                  <td class="px-5 py-4 text-slate-700">{{ formatDate(record.date) }}</td>
                  <td class="px-5 py-4 text-slate-700">{{ formatTime(record.time_in) }}</td>
                  <td class="px-5 py-4 text-slate-700">{{ formatTime(record.time_out) }}</td>
                  <td class="px-5 py-4 font-semibold text-slate-800">{{ totalHours(record) }}</td>
                  <td class="px-5 py-4">
                    <span class="rounded-full px-3 py-1 text-xs font-bold" :class="reviewStatusClass(record.review_status)">
                      {{ reviewStatusLabel(record.review_status) }}
                    </span>
                  </td>
                  <td class="px-5 py-4">
                    <div class="flex flex-col gap-2">
                      <button
                        type="button"
                        class="text-left text-sm font-semibold disabled:cursor-not-allowed disabled:text-slate-400"
                        :class="getInProof(record) ? 'text-blue-700 hover:underline' : 'text-slate-400'"
                        :disabled="!getInProof(record)"
                        @click="openProof(getInProof(record))"
                      >
                        {{ getInProof(record) ? 'View Time In Proof' : 'No time in proof' }}
                      </button>
                      <button
                        type="button"
                        class="text-left text-sm font-semibold disabled:cursor-not-allowed disabled:text-slate-400"
                        :class="getOutProof(record) ? 'text-blue-700 hover:underline' : 'text-slate-400'"
                        :disabled="!getOutProof(record)"
                        @click="openProof(getOutProof(record))"
                      >
                        {{ getOutProof(record) ? 'View Time Out Proof' : 'No time out proof' }}
                      </button>
                    </div>
                  </td>
                  <td class="px-5 py-4">
<div class="flex flex-wrap gap-2">
  <template v-if="String(record.review_status || 'pending').toLowerCase() === 'pending'">
    <button
      type="button"
      class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white hover:bg-slate-800"
      @click="openReview(record)"
    >
      Review DTR
    </button>

    <button
      type="button"
      class="rounded-lg bg-green-50 px-3 py-2 text-xs font-bold text-green-700 hover:bg-green-100"
      @click="selectedRecord = record; submitReview('approved')"
    >
      Approve
    </button>

    <button
      type="button"
      class="rounded-lg bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-100"
      @click="openReview(record)"
    >
      Request Correction
    </button>

    <button
      type="button"
      class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100"
      @click="openReview(record)"
    >
      Reject
    </button>
  </template>

  <span
    v-else
    class="rounded-lg bg-slate-100 px-3 py-2 text-xs font-bold text-slate-500"
  >
    Reviewed
  </span>
</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="grid gap-4 p-4 lg:hidden">
            <article v-for="record in filteredRecords" :key="record.id" class="rounded-xl border border-slate-200 p-4">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <h3 class="font-bold text-slate-950">{{ record.beneficiary_name || 'Unknown Beneficiary' }}</h3>
                  <p class="mt-1 text-sm text-slate-500">{{ record.job_title || 'No job assigned' }}</p>
                </div>
                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="reviewStatusClass(record.review_status)">
                  {{ reviewStatusLabel(record.review_status) }}
                </span>
              </div>
              <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div><span class="text-slate-500">Date</span><p class="font-semibold">{{ formatDate(record.date) }}</p></div>
                <div><span class="text-slate-500">Hours</span><p class="font-semibold">{{ totalHours(record) }}</p></div>
                <div><span class="text-slate-500">Time In</span><p class="font-semibold">{{ formatTime(record.time_in) }}</p></div>
                <div><span class="text-slate-500">Time Out</span><p class="font-semibold">{{ formatTime(record.time_out) }}</p></div>
              </div>
              <div class="mt-4 flex flex-wrap gap-2">
                <button
                  type="button"
                  class="rounded-lg px-3 py-2 text-xs font-bold disabled:cursor-not-allowed"
                  :class="getInProof(record) ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-400'"
                  :disabled="!getInProof(record)"
                  @click="openProof(getInProof(record))"
                >
                  {{ getInProof(record) ? 'View In Proof' : 'No In Proof' }}
                </button>
                <button
                  type="button"
                  class="rounded-lg px-3 py-2 text-xs font-bold disabled:cursor-not-allowed"
                  :class="getOutProof(record) ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-400'"
                  :disabled="!getOutProof(record)"
                  @click="openProof(getOutProof(record))"
                >
                  {{ getOutProof(record) ? 'View Out Proof' : 'No Out Proof' }}
                </button>
                <button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-bold text-white" @click="openReview(record)">Review DTR</button>
              </div>
            </article>
          </div>
        </div>
      </section>
    </div>

    <div
      v-if="reviewModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="closeReview"
    >
      <form class="max-h-[92vh] w-full max-w-3xl overflow-auto rounded-xl bg-white p-6 shadow-2xl" @submit.prevent="submitReview('approved')">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-wide text-blue-600">Review DTR</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ selectedRecord?.beneficiary_name || 'Beneficiary' }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ selectedRecord?.job_title || 'No job assigned' }}</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeReview">Close</button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Date</p>
            <p class="mt-2 font-semibold">{{ formatDate(selectedRecord?.date) }}</p>
          </div>
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Time In</p>
            <p class="mt-2 font-semibold">{{ formatTime(selectedRecord?.time_in) }}</p>
          </div>
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Time Out</p>
            <p class="mt-2 font-semibold">{{ formatTime(selectedRecord?.time_out) }}</p>
          </div>
          <div class="rounded-xl bg-slate-50 p-4">
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</p>
            <p class="mt-2 font-semibold">{{ reviewStatusLabel(selectedRecord?.review_status) }}</p>
          </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <button
            type="button"
            class="rounded-xl border px-4 py-3 text-sm font-bold disabled:cursor-not-allowed"
            :class="getInProof(selectedRecord) ? 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' : 'border-slate-200 bg-slate-100 text-slate-400'"
            :disabled="!getInProof(selectedRecord)"
            @click="openProof(getInProof(selectedRecord))"
          >
            {{ getInProof(selectedRecord) ? 'View Time In Proof' : 'No time in proof uploaded' }}
          </button>
          <button
            type="button"
            class="rounded-xl border px-4 py-3 text-sm font-bold disabled:cursor-not-allowed"
            :class="getOutProof(selectedRecord) ? 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100' : 'border-slate-200 bg-slate-100 text-slate-400'"
            :disabled="!getOutProof(selectedRecord)"
            @click="openProof(getOutProof(selectedRecord))"
          >
            {{ getOutProof(selectedRecord) ? 'View Time Out Proof' : 'No time out proof uploaded' }}
          </button>
        </div>

        <label class="mt-5 block text-sm font-bold text-slate-700">Remarks</label>
        <textarea
          v-model="reviewRemarks"
          rows="4"
          class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
          placeholder="Required when requesting correction or rejecting."
        ></textarea>

        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-end">
          <button type="button" :disabled="reviewing" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700 hover:bg-red-100 disabled:opacity-50" @click="submitReview('rejected')">
            Reject
          </button>
          <button type="button" :disabled="reviewing" class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 hover:bg-amber-100 disabled:opacity-50" @click="submitReview('needs_correction')">
            Request Correction
          </button>
          <button type="submit" :disabled="reviewing" class="rounded-xl bg-green-600 px-4 py-3 text-sm font-bold text-white hover:bg-green-700 disabled:opacity-50">
            {{ reviewing ? 'Saving...' : 'Approve' }}
          </button>
        </div>
      </form>
    </div>

    <div
      v-if="showProofModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
      @click.self="closeProof"
    >
      <div class="relative w-full max-w-4xl rounded-xl bg-white p-5 shadow-2xl">
        <button type="button" class="absolute right-4 top-3 text-lg font-bold text-slate-500 hover:text-red-500" @click="closeProof">X</button>
        <h2 class="mb-4 text-xl font-bold text-slate-950">Proof Preview</h2>
        <img
          v-if="isImageProof(selectedProof)"
          :src="selectedProof"
          class="max-h-[75vh] w-full rounded-xl border object-contain"
          @error="triggerToast('Unable to load proof image', 'error')"
        />
        <iframe v-else-if="isPdfProof(selectedProof)" :src="selectedProof" class="h-[600px] w-full rounded-xl border"></iframe>
        <div v-else class="flex flex-col items-center justify-center gap-4 rounded-xl border bg-slate-50 p-10 text-center">
          <p class="text-slate-600">Preview is not available for this file type.</p>
          <a :href="selectedProof" target="_blank" rel="noopener noreferrer" class="rounded-xl bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-700">
            Open Proof in New Tab
          </a>
        </div>
      </div>
    </div>

    <transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="translate-y-5 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="showToast"
        class="fixed bottom-6 right-6 z-50 rounded-xl px-5 py-3 text-white shadow-2xl"
        :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'"
      >
        {{ toastMessage }}
      </div>
    </transition>
  </div>
</template>
