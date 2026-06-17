<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  selectedTab: {
    type: String,
    default: 'attendance',
  },
  attendanceFilters: {
    type: Object,
    default: () => ({
      school: '',
      employer: '',
      status: '',
    }),
  },
  attendanceFilterOptions: {
    type: Object,
    default: () => ({
      schools: [],
      employers: [],
      statuses: [],
    }),
  },
  attendanceSummary: {
    type: Object,
    default: () => ({
      beneficiariesMonitored: 0,
      records: 0,
      avgPresenceDays: 0,
    }),
  },
  filteredAttendanceRecords: {
    type: Array,
    default: () => [],
  },
  dailyReports: {
    type: Array,
    default: () => [],
  },
  completionQueue: {
    type: Array,
    default: () => [],
  },
  approveCompletion: {
    type: Function,
    default: null,
  },
  resetAttendanceFilters: {
    type: Function,
    default: () => {},
  },
  formatTime: {
    type: Function,
    default: (value) => value,
  },
  showAttendanceHistoryModal: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['update:showAttendanceHistoryModal'])

const dateFilter = ref('')
const selectedRecord = ref(null)
const selectedDailyReport = ref(null)
const dailyReportFilters = ref({
  status: '',
  employer: '',
  beneficiary: '',
  date: '',
})

const visibleRecords = computed(() => {
  if (!dateFilter.value) return props.filteredAttendanceRecords

  return props.filteredAttendanceRecords.filter((record) => {
    const date = normalizeDate(record.date || record.created_at)
    return date === dateFilter.value
  })
})

const summaryCards = computed(() => [
  {
    label: 'Present Today',
    value: props.filteredAttendanceRecords.filter((record) => {
      return normalizeDate(record.date || record.created_at) === todayString() &&
        normalizedStatus(record).includes('present')
    }).length,
    description: 'Attendance logs marked present today.',
  },
  {
    label: 'Total DTR Entries',
    value: props.filteredAttendanceRecords.filter((record) => {
      const status = normalizedStatus(record)
      return status.includes('pending') || status.includes('submitted') || status.includes('review')
    }).length,
    description: 'DTR entries submitted by beneficiaries.',
  },
  {
    label: 'Flagged Entries',
    value: props.filteredAttendanceRecords.filter((record) => {
      const status = normalizedStatus(record)
      return status.includes('correction') || status.includes('rejected') || status.includes('missing')
    }).length,
    description: 'Entries with issues or missing details.',
  },
  {
    label: 'Completed',
    value: props.filteredAttendanceRecords.filter((record) => {
      const status = normalizedStatus(record)
      return status.includes('approved') || status.includes('completed') || status.includes('present')
    }).length,
    description: 'Recorded attendance entries.',
  },
])

const dailyReportEmployerOptions = computed(() => {
  return [...new Set(props.dailyReports.map((report) => report.employer_name).filter(Boolean))]
})

const filteredDailyReports = computed(() => {
  return props.dailyReports.filter((report) => {
    const beneficiary = String(report.beneficiary_name || '').toLowerCase()
    const employer = String(report.employer_name || '')
    const status = String(report.status || '')
    const date = normalizeDate(report.work_date || report.created_at)

    return (!dailyReportFilters.value.beneficiary || beneficiary.includes(dailyReportFilters.value.beneficiary.toLowerCase())) &&
      (!dailyReportFilters.value.employer || employer === dailyReportFilters.value.employer) &&
      (!dailyReportFilters.value.status || status === dailyReportFilters.value.status) &&
      (!dailyReportFilters.value.date || date === dailyReportFilters.value.date)
  })
})

function clearDailyReportFilters() {
  dailyReportFilters.value = {
    status: '',
    employer: '',
    beneficiary: '',
    date: '',
  }
}

function normalizedStatus(record) {
  return String(record.status || record.dtr_status || '').toLowerCase()
}

function displayStatus(record) {
  const value = String(record.status || record.dtr_status || 'Pending Review').replace(/_/g, ' ')
  return value.replace(/\b\w/g, (char) => char.toUpperCase())
}

function statusClass(record) {
  const status = normalizedStatus(record)
  if (status.includes('approved') || status.includes('present') || status.includes('completed')) return 'bg-green-100 text-green-800'
  if (status.includes('pending') || status.includes('submitted') || status.includes('review')) return 'bg-amber-100 text-amber-800'
  if (status.includes('correction') || status.includes('rejected') || status.includes('missing') || status.includes('absent')) return 'bg-red-100 text-red-800'
  return 'bg-slate-100 text-slate-700'
}

function readinessClass(ok) {
  return ok ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'
}

function readinessLabel(ok) {
  return ok ? 'Found' : 'Not found'
}

function completionReady(item) {
  return Number(item.readiness_score || 0) >= 3
}

function normalizeDate(date) {
  if (!date) return ''
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return String(date).slice(0, 10)
  return parsed.toISOString().slice(0, 10)
}

function todayString() {
  return new Date().toISOString().slice(0, 10)
}

function formatDate(date) {
  if (!date) return 'No date'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date
  return parsed.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function hours(record) {
  if (record.hours || record.total_hours) return record.hours || record.total_hours
  if (!record.time_in || !record.time_out) return '-'

  const start = new Date(`1970-01-01T${record.time_in}`)
  const end = new Date(`1970-01-01T${record.time_out}`)
  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return '-'

  const diff = Math.max((end - start) / 36e5, 0)
  return `${diff.toFixed(1)}h`
}

function clearFilters() {
  dateFilter.value = ''
  props.resetAttendanceFilters()
}
</script>

<template>
  <section v-if="selectedTab === 'attendance'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Attendance & DTR Monitoring</p>
      <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Monitoring</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Monitor beneficiary attendance logs and DTR entries. Attendance management is between the employer and beneficiary.
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
      <div class="grid gap-3 md:grid-cols-4">
        <input
          v-model="dateFilter"
          type="date"
          class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
        >
        <select v-model="attendanceFilters.employer" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Employers</option>
          <option v-for="employer in attendanceFilterOptions.employers" :key="employer" :value="employer">{{ employer }}</option>
        </select>
        <select v-model="attendanceFilters.status" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Statuses</option>
          <option v-for="status in attendanceFilterOptions.statuses" :key="status" :value="status">{{ status }}</option>
        </select>
        <button
          type="button"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          @click="clearFilters"
        >
          Reset Filters
        </button>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1.1fr_1fr_0.8fr_0.7fr_0.7fr_0.7fr_0.9fr_1.2fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Beneficiary</span>
        <span>Employer</span>
        <span>Date</span>
        <span>Time In</span>
        <span>Time Out</span>
        <span>Hours</span>
        <span>Status</span>
        <span>Action</span>
      </div>

      <div
        v-for="record in visibleRecords"
        :key="record.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.1fr_1fr_0.8fr_0.7fr_0.7fr_0.7fr_0.9fr_1.2fr] xl:items-center"
      >
        <p class="font-semibold text-slate-900">{{ record.beneficiary_name || 'Unknown beneficiary' }}</p>
        <p class="text-sm text-slate-700">{{ record.employer_name || 'Unknown employer' }}</p>
        <p class="text-sm text-slate-700">{{ formatDate(record.date || record.created_at) }}</p>
        <p class="text-sm text-slate-700">{{ formatTime(record.time_in) || '-' }}</p>
        <p class="text-sm text-slate-700">{{ formatTime(record.time_out) || '-' }}</p>
        <p class="text-sm font-semibold text-slate-900">{{ hours(record) }}</p>
        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(record)">
          {{ displayStatus(record) }}
        </span>
        <div class="flex flex-wrap gap-2">
          <button type="button" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="selectedRecord = record">
            View
          </button>
        </div>
      </div>

      <div v-if="visibleRecords.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No attendance records found.</p>
        <p class="mt-1 text-sm text-slate-500">Try changing the date, employer, or status filter.</p>
      </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Daily Reports</p>
          <h2 class="mt-2 text-xl font-bold text-slate-900">Daily Accomplishment Reports</h2>
          <p class="mt-1 text-sm text-slate-500">Monitor beneficiary daily reports reviewed by employers.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
          {{ filteredDailyReports.length }} shown
        </span>
      </div>

      <div class="mt-5 grid gap-3 md:grid-cols-5">
        <input
          v-model="dailyReportFilters.beneficiary"
          type="search"
          placeholder="Beneficiary"
          class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
        >
        <select v-model="dailyReportFilters.employer" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Employers</option>
          <option v-for="employer in dailyReportEmployerOptions" :key="employer" :value="employer">{{ employer }}</option>
        </select>
        <select v-model="dailyReportFilters.status" class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          <option value="">All Statuses</option>
          <option value="submitted">Submitted</option>
          <option value="under_review">Under Review</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
          <option value="needs_correction">Needs Correction</option>
        </select>
        <input
          v-model="dailyReportFilters.date"
          type="date"
          class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
        >
        <button
          type="button"
          class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          @click="clearDailyReportFilters"
        >
          Reset
        </button>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1fr_1fr_0.8fr_0.7fr_0.8fr_1.2fr_0.7fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Beneficiary</span>
        <span>Employer</span>
        <span>Work Date</span>
        <span>Hours</span>
        <span>Status</span>
        <span>Employer Remarks</span>
        <span>Action</span>
      </div>

      <article
        v-for="report in filteredDailyReports"
        :key="report.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1fr_1fr_0.8fr_0.7fr_0.8fr_1.2fr_0.7fr] xl:items-center"
      >
        <p class="font-semibold text-slate-900">{{ report.beneficiary_name }}</p>
        <p class="text-sm text-slate-700">{{ report.employer_name }}</p>
        <p class="text-sm text-slate-700">{{ formatDate(report.work_date || report.created_at) }}</p>
        <p class="text-sm font-semibold text-slate-900">{{ Number(report.hours_worked || 0).toFixed(2) }}h</p>
        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(report)">
          {{ displayStatus(report) }}
        </span>
        <p class="text-sm text-slate-700">{{ report.review_remarks || 'No remarks posted.' }}</p>
        <button
          type="button"
          class="w-fit rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
          @click="selectedDailyReport = report"
        >
          View
        </button>
      </article>

      <div v-if="filteredDailyReports.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No daily accomplishment reports found.</p>
        <p class="mt-1 text-sm text-slate-500">Reports will appear here once beneficiaries submit them.</p>
      </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Completion Review</p>
          <h2 class="mt-2 text-xl font-bold text-slate-900">Completion Review Queue</h2>
          <p class="mt-1 text-sm text-slate-500">Approve official completion after work evidence and employer review are checked.</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
          {{ completionQueue.length }} for review
        </span>
      </div>

      <div class="mt-5 overflow-hidden rounded-lg border border-slate-200">
        <div class="hidden grid-cols-[1fr_1fr_0.7fr_0.9fr_0.8fr_0.8fr_0.8fr_0.9fr] gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
          <span>Beneficiary</span>
          <span>Employer</span>
          <span>DTR</span>
          <span>Daily Reports</span>
          <span>Rating</span>
          <span>Certificate</span>
          <span>Readiness</span>
          <span>Action</span>
        </div>

        <article
          v-for="item in completionQueue"
          :key="item.application_id || item.id"
          class="grid gap-4 border-b border-slate-200 px-4 py-4 last:border-b-0 xl:grid-cols-[1fr_1fr_0.7fr_0.9fr_0.8fr_0.8fr_0.8fr_0.9fr] xl:items-center"
        >
          <div>
            <p class="font-semibold text-slate-900">{{ item.beneficiary_name }}</p>
            <p class="mt-1 text-xs text-slate-500">{{ item.job_title }}</p>
          </div>
          <p class="text-sm text-slate-700">{{ item.employer_name }}</p>
          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="readinessClass(item.has_dtr)">{{ readinessLabel(item.has_dtr) }}</span>
          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="readinessClass(item.has_approved_daily_reports)">{{ readinessLabel(item.has_approved_daily_reports) }}</span>
          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="readinessClass(item.has_employer_rating)">{{ readinessLabel(item.has_employer_rating) }}</span>
          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="readinessClass(item.has_certificate)">{{ readinessLabel(item.has_certificate) }}</span>
          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="completionReady(item) ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'">
            {{ item.readiness_score || 0 }}/4 checks
          </span>
          <button
            type="button"
            class="w-fit rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="!approveCompletion"
            @click="approveCompletion?.(item)"
          >
            Approve Completion
          </button>
        </article>

        <div v-if="completionQueue.length === 0" class="px-5 py-10 text-center">
          <p class="text-sm font-semibold text-slate-700">No completion reviews pending.</p>
          <p class="mt-1 text-sm text-slate-500">Applications submitted for final completion review will appear here.</p>
        </div>
      </div>
    </section>

    <div v-if="selectedRecord" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="selectedRecord = null">
      <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-900">Attendance Details</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
          <div><dt class="font-semibold text-slate-500">Beneficiary</dt><dd class="mt-1 text-slate-900">{{ selectedRecord.beneficiary_name }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Employer</dt><dd class="mt-1 text-slate-900">{{ selectedRecord.employer_name }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Date</dt><dd class="mt-1 text-slate-900">{{ formatDate(selectedRecord.date || selectedRecord.created_at) }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Hours</dt><dd class="mt-1 text-slate-900">{{ hours(selectedRecord) }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Time In</dt><dd class="mt-1 text-slate-900">{{ formatTime(selectedRecord.time_in) || '-' }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Time Out</dt><dd class="mt-1 text-slate-900">{{ formatTime(selectedRecord.time_out) || '-' }}</dd></div>
        </dl>
        <div class="mt-6 flex justify-end">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" @click="selectedRecord = null">Close</button>
        </div>
      </div>
    </div>

    <div v-if="selectedDailyReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="selectedDailyReport = null">
      <div class="max-h-[90vh] w-full max-w-2xl overflow-auto rounded-lg bg-white p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-900">{{ selectedDailyReport.title || 'Daily Accomplishment Report' }}</h2>
        <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
          <div><dt class="font-semibold text-slate-500">Beneficiary</dt><dd class="mt-1 text-slate-900">{{ selectedDailyReport.beneficiary_name }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Employer</dt><dd class="mt-1 text-slate-900">{{ selectedDailyReport.employer_name }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Work Date</dt><dd class="mt-1 text-slate-900">{{ formatDate(selectedDailyReport.work_date || selectedDailyReport.created_at) }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Hours</dt><dd class="mt-1 text-slate-900">{{ Number(selectedDailyReport.hours_worked || 0).toFixed(2) }}h</dd></div>
          <div><dt class="font-semibold text-slate-500">Status</dt><dd class="mt-1 text-slate-900">{{ displayStatus(selectedDailyReport) }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Reviewed By</dt><dd class="mt-1 text-slate-900">{{ selectedDailyReport.reviewed_by?.name || 'Not reviewed yet' }}</dd></div>
        </dl>
        <div class="mt-4">
          <p class="text-sm font-semibold text-slate-500">Accomplishments</p>
          <p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-800">{{ selectedDailyReport.accomplishments || 'No accomplishments entered.' }}</p>
        </div>
        <p v-if="selectedDailyReport.review_remarks" class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
          Employer remarks: {{ selectedDailyReport.review_remarks }}
        </p>
        <a v-if="selectedDailyReport.file_url" :href="selectedDailyReport.file_url" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex text-sm font-semibold text-blue-700">
          View Attachment
        </a>
        <div class="mt-6 flex justify-end">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" @click="selectedDailyReport = null">Close</button>
        </div>
      </div>
    </div>
  </section>
</template>
