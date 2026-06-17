<script setup>
import { computed, ref, watch } from 'vue'

const props = defineProps({
  selectedTab: String,
  reports: {
    type: Array,
    default: () => [],
  },
  formatDate: {
    type: Function,
    default: (date) => date,
  },
  openDocument: {
    type: Function,
    default: () => {},
  },
})

const search = ref('')
const currentPage = ref(1)
const selectedReport = ref(null)
const itemsPerPage = 10

const filteredReports = computed(() => {
  const keyword = search.value.trim().toLowerCase()

  return props.reports.filter((report) => {
    const haystack = [
      submittedBy(report),
      reportTitle(report),
      reportBody(report),
      employerName(report),
      report.id,
    ].join(' ').toLowerCase()

    return !keyword || haystack.includes(keyword)
  })
})

const totalPages = computed(() => Math.max(Math.ceil(filteredReports.value.length / itemsPerPage), 1))

const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  return filteredReports.value.slice(start, start + itemsPerPage)
})

const summaryCards = computed(() => [
  {
    label: 'Submitted',
    value: props.reports.length,
    description: 'Reports received by CPESO.',
  },
  {
    label: 'With Attachment',
    value: props.reports.filter((report) => Boolean(fileUrl(report))).length,
    description: 'Reports with uploaded proof or files.',
  },
  {
    label: 'No Attachment',
    value: props.reports.filter((report) => !fileUrl(report)).length,
    description: 'Reports submitted without files.',
  },
])

watch(search, () => {
  currentPage.value = 1
})

function submittedBy(report) {
  return report.submitted_by || report.employer_name || report.employer?.company_name || 'Unknown submitter'
}

function employerName(report) {
  return report.employer_name || report.employer?.company_name || submittedBy(report)
}

function reportTitle(report) {
  return report.title || 'Untitled report'
}

function reportBody(report) {
  return report.body || report.message || report.report_details || ''
}

function bodyPreview(report) {
  const text = reportBody(report).trim()
  if (!text) return 'No message provided.'
  return text.length > 120 ? `${text.slice(0, 120)}...` : text
}

function fileUrl(report) {
  const url = report.file_url || report.attachment_url || report.document_url || report.path || report.file_path || ''
  if (!url) return ''
  if (/^https?:\/\//i.test(url) || url.startsWith('/')) return url
  return `/storage/${url}`
}

function downloadUrl(report) {
  return fileUrl(report) || '#'
}

function openDetails(report) {
  selectedReport.value = report
}

function closeDetails() {
  selectedReport.value = null
}

function nextPage() {
  if (currentPage.value < totalPages.value) currentPage.value++
}

function prevPage() {
  if (currentPage.value > 1) currentPage.value--
}
</script>

<template>
  <section v-if="selectedTab === 'reports'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Report Records</p>
          <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Reports</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
            View employer-submitted reports and uploaded supporting files.
          </p>
        </div>

        <a
          href="/peso/reports/dole"
          class="inline-flex w-fit items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
        >
          Generate DOLE Report
        </a>
      </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="card in summaryCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
      </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
      <input
        v-model="search"
        type="search"
        placeholder="Search submitter, report title, employer, or message..."
        class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
      >
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1fr_1.1fr_1fr_0.9fr_1.2fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Submitted By</span>
        <span>Report Title</span>
        <span>Employer</span>
        <span>Date Submitted</span>
        <span>Message Preview</span>
        <span>Action</span>
      </div>

      <div
        v-for="report in paginatedReports"
        :key="report.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1fr_1.1fr_1fr_0.9fr_1.2fr_1fr] xl:items-center"
      >
        <div>
          <p class="font-semibold text-slate-900">{{ submittedBy(report) }}</p>
          <p class="mt-1 text-xs text-slate-500">Report #{{ report.id }}</p>
        </div>
        <p class="text-sm text-slate-700">{{ reportTitle(report) }}</p>
        <p class="text-sm text-slate-700">{{ employerName(report) }}</p>
        <p class="text-sm text-slate-700">{{ formatDate(report.created_at || report.submitted_at) }}</p>
        <p class="text-sm leading-6 text-slate-600">{{ bodyPreview(report) }}</p>
        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
            @click="openDetails(report)"
          >
            View Details
          </button>
          <a
            v-if="fileUrl(report)"
            :href="downloadUrl(report)"
            target="_blank"
            rel="noopener noreferrer"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            download
          >
            Download File
          </a>
          <span v-else class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-500">
            No attachment
          </span>
        </div>
      </div>

      <div v-if="paginatedReports.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No reports found.</p>
        <p class="mt-1 text-sm text-slate-500">Try adjusting the search keyword.</p>
      </div>

      <div v-if="filteredReports.length > itemsPerPage" class="flex flex-col gap-3 border-t border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-slate-500">Page {{ currentPage }} of {{ totalPages }}</p>
        <div class="flex gap-2">
          <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === 1" @click="prevPage">
            Previous
          </button>
          <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 disabled:opacity-50" :disabled="currentPage === totalPages" @click="nextPage">
            Next
          </button>
        </div>
      </div>
    </section>

    <div v-if="selectedReport" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeDetails">
      <div class="max-h-[90vh] w-full max-w-2xl overflow-auto rounded-lg bg-white p-6 shadow-xl">
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 pb-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Report Details</p>
            <h2 class="mt-2 text-xl font-bold text-slate-900">{{ reportTitle(selectedReport) }}</h2>
            <p class="mt-1 text-sm text-slate-500">Submitted by {{ submittedBy(selectedReport) }}</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-200" @click="closeDetails">
            Close
          </button>
        </div>

        <dl class="mt-5 grid gap-4 text-sm sm:grid-cols-2">
          <div class="rounded-lg bg-slate-50 p-4">
            <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Employer</dt>
            <dd class="mt-2 font-semibold text-slate-900">{{ employerName(selectedReport) }}</dd>
          </div>
          <div class="rounded-lg bg-slate-50 p-4">
            <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Date Submitted</dt>
            <dd class="mt-2 font-semibold text-slate-900">{{ formatDate(selectedReport.created_at || selectedReport.submitted_at) }}</dd>
          </div>
        </dl>

        <div class="mt-5">
          <p class="text-sm font-semibold text-slate-700">Report Message</p>
          <p class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-6 text-slate-800">
            {{ reportBody(selectedReport) || 'No message provided.' }}
          </p>
        </div>

        <div class="mt-5 rounded-lg border border-slate-200 p-4">
          <p class="text-sm font-semibold text-slate-700">Attachment</p>
          <a
            v-if="fileUrl(selectedReport)"
            :href="downloadUrl(selectedReport)"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-3 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            download
          >
            Download File
          </a>
          <p v-else class="mt-2 text-sm text-slate-500">No attachment uploaded.</p>
        </div>
      </div>
    </div>
  </section>
</template>
