<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const reports = ref([])
const loading = ref(true)
const submitting = ref(false)
const message = ref('')
const error = ref('')
const attachment = ref(null)
const attachmentInput = ref(null)
const editingReport = ref(null)

const form = ref({
  work_date: localDate(),
  title: '',
  accomplishments: '',
  hours_worked: '',
})

const sortedReports = computed(() =>
  [...reports.value].sort((a, b) => new Date(b.original_submitted_at || b.created_at || 0) - new Date(a.original_submitted_at || a.created_at || 0))
)

const isEditingCorrection = computed(() => Boolean(editingReport.value))

function localDate() {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}

function handleAttachment(event) {
  attachment.value = event.target.files?.[0] || null
}

async function loadReports() {
  const res = await axios.get('/beneficiary/work-outputs')
  reports.value = Array.isArray(res.data) ? res.data : []
}

async function submitReport() {
  const payload = new FormData()
  if (!isEditingCorrection.value) {
    payload.append('work_date', form.value.work_date)
  }
  payload.append('title', form.value.title)
  payload.append('accomplishments', form.value.accomplishments)
  payload.append('hours_worked', form.value.hours_worked)

  if (attachment.value) {
    payload.append('attachment', attachment.value)
  }

  try {
    submitting.value = true
    message.value = ''
    error.value = ''

    const endpoint = isEditingCorrection.value
      ? `/beneficiary/work-outputs/${editingReport.value.id}/resubmit`
      : '/beneficiary/work-outputs'

    await axios.post(endpoint, payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    message.value = isEditingCorrection.value
      ? 'Daily Accomplishment Report resubmitted successfully.'
      : 'Daily Accomplishment Report submitted successfully.'
    resetForm()
    await loadReports()
  } catch (err) {
    error.value = err.response?.data?.message || (isEditingCorrection.value
      ? 'Unable to resubmit Daily Accomplishment Report.'
      : 'Unable to submit Daily Accomplishment Report.')
  } finally {
    submitting.value = false
  }
}

function editCorrection(report) {
  editingReport.value = report
  form.value = {
    work_date: report.work_date || localDate(),
    title: report.title || '',
    accomplishments: report.accomplishments || '',
    hours_worked: report.hours_worked || '',
  }
  attachment.value = null
  if (attachmentInput.value) {
    attachmentInput.value.value = ''
  }
  message.value = ''
  error.value = ''
}

function resetForm() {
  editingReport.value = null
  form.value = {
    work_date: localDate(),
    title: '',
    accomplishments: '',
    hours_worked: '',
  }
  attachment.value = null
  if (attachmentInput.value) {
    attachmentInput.value.value = ''
  }
}

function statusLabel(status) {
  const value = String(status || 'submitted').replace(/_/g, ' ')
  return value.replace(/\b\w/g, (char) => char.toUpperCase())
}

function statusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value === 'approved') return 'bg-green-100 text-green-800 border-green-200'
  if (value === 'rejected' || value === 'needs_correction') return 'bg-red-100 text-red-800 border-red-200'
  if (value === 'under_review') return 'bg-amber-100 text-amber-800 border-amber-200'
  return 'bg-blue-100 text-blue-800 border-blue-200'
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

function formatDateTime(value) {
  if (!value) return 'Not recorded'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: 'numeric',
    minute: '2-digit',
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
  <Head title="Daily Accomplishment Report" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <Link
        href="/beneficiary/attendance"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Attendance / DTR
      </Link>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">SPES Work Monitoring</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Daily Accomplishment Report</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Submit your daily tasks and accomplishments after or together with your Attendance / DTR.
        </p>
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <form class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6" @submit.prevent="submitReport">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
              <h2 class="text-lg font-bold text-slate-900">{{ isEditingCorrection ? 'Resubmit Corrected Report' : 'Submit Daily Report' }}</h2>
              <p v-if="isEditingCorrection" class="mt-1 text-sm text-slate-500">
                Editing the report originally submitted at {{ formatDateTime(editingReport.original_submitted_at || editingReport.created_at) }}.
              </p>
            </div>
            <button
              v-if="isEditingCorrection"
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
              @click="resetForm"
            >
              Cancel Edit
            </button>
          </div>

          <div v-if="message" class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-800">
            {{ message }}
          </div>
          <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800">
            {{ error }}
          </div>

          <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <div>
              <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Work Date</label>
              <input v-model="form.work_date" type="date" required :disabled="isEditingCorrection" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none disabled:bg-slate-100 disabled:text-slate-500">
              <p v-if="isEditingCorrection" class="mt-1 text-xs text-slate-500">Work date remains unchanged during correction.</p>
            </div>
            <div>
              <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Hours Worked</label>
              <input v-model="form.hours_worked" type="number" min="0" max="24" step="0.25" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
            </div>
          </div>

          <div class="mt-4">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Title</label>
            <input v-model="form.title" type="text" placeholder="Example: Front desk assistance" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          </div>

          <div class="mt-4">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Accomplishments / Tasks Done</label>
            <textarea v-model="form.accomplishments" rows="5" required placeholder="Describe the tasks you completed today." class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
          </div>

          <div class="mt-4">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Optional Attachment</label>
            <input ref="attachmentInput" type="file" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" class="block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white" @change="handleAttachment">
            <p v-if="attachment" class="mt-2 text-xs text-slate-500">Selected: {{ attachment.name }}</p>
            <p v-else-if="isEditingCorrection && editingReport.file_url" class="mt-2 text-xs text-slate-500">
              Current attachment will be kept unless you choose a replacement.
            </p>
          </div>

          <button
            type="submit"
            :disabled="submitting"
            class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
          >
            {{ submitting ? 'Submitting...' : (isEditingCorrection ? 'Resubmit Daily Report' : 'Submit Daily Report') }}
          </button>
        </form>

        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="border-b border-slate-200 p-5">
            <h2 class="text-lg font-bold text-slate-900">Submission History</h2>
            <p class="mt-1 text-sm text-slate-500">Employer review status and remarks.</p>
          </div>

          <div v-if="loading" class="p-8 text-center text-sm text-slate-500">Loading reports...</div>
          <div v-else-if="sortedReports.length === 0" class="p-8 text-center text-sm text-slate-500">No daily reports submitted yet.</div>
          <div v-else class="divide-y divide-slate-200">
            <article v-for="report in sortedReports" :key="report.id" class="p-5">
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <p class="text-xs font-bold uppercase tracking-wide text-blue-600">{{ formatDate(report.work_date) }}</p>
                  <h3 class="mt-1 font-bold text-slate-900">{{ report.title || 'Daily report' }}</h3>
                  <p class="mt-1 text-sm text-slate-600">{{ formatHours(report.hours_worked) }}</p>
                </div>
                <span class="w-fit rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClass(report.status)">
                  {{ statusLabel(report.status) }}
                </span>
              </div>
              <p class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">{{ report.accomplishments }}</p>
              <dl class="mt-3 grid gap-2 rounded-lg bg-slate-50 px-3 py-3 text-xs text-slate-600 sm:grid-cols-2">
                <div>
                  <dt class="font-bold uppercase tracking-wide text-slate-500">Original submitted at</dt>
                  <dd class="mt-1 text-slate-800">{{ formatDateTime(report.original_submitted_at || report.created_at) }}</dd>
                </div>
                <div>
                  <dt class="font-bold uppercase tracking-wide text-slate-500">Resubmitted at</dt>
                  <dd class="mt-1 text-slate-800">{{ report.resubmitted_at ? formatDateTime(report.resubmitted_at) : 'Not resubmitted' }}</dd>
                </div>
                <div>
                  <dt class="font-bold uppercase tracking-wide text-slate-500">Current status</dt>
                  <dd class="mt-1 text-slate-800">{{ statusLabel(report.status) }}</dd>
                </div>
                <div>
                  <dt class="font-bold uppercase tracking-wide text-slate-500">Employer remarks</dt>
                  <dd class="mt-1 text-slate-800">{{ report.review_remarks || 'No remarks yet' }}</dd>
                </div>
              </dl>
              <a v-if="report.file_url" :href="report.file_url" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex text-sm font-semibold text-blue-700">
                View Attachment
              </a>
              <p v-if="report.review_remarks" class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
                Employer remarks: {{ report.review_remarks }}
              </p>
              <button
                v-if="String(report.status || '').toLowerCase() === 'needs_correction'"
                type="button"
                class="mt-3 inline-flex rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700"
                @click="editCorrection(report)"
              >
                Resubmit / Edit
              </button>
            </article>
          </div>
        </section>
      </section>
    </div>
  </main>
</template>
