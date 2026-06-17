<template>
  <div class="space-y-6">

    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <div
        v-for="card in queueCards"
        :key="card.label"
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
      >
        <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
        <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
      </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
      <div
        v-for="group in workflowGroups"
        :key="group.title"
        class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-blue-600">{{ group.eyebrow }}</p>
            <h2 class="mt-2 text-lg font-bold text-slate-900">{{ group.title }}</h2>
          </div>
          <button
            v-if="group.href"
            type="button"
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
            @click="go(group.href)"
          >
            Open
          </button>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
          <div
            v-for="item in group.items"
            :key="item.label"
            class="rounded-lg bg-slate-50 p-4"
          >
            <p class="text-sm font-semibold text-slate-600">{{ item.label }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ item.value }}</p>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  stats: Object,
  applications: { type: Array, default: () => [] },
  beneficiaries: { type: Array, default: () => [] },
  exams: { type: Array, default: () => [] },
  interviews: { type: Array, default: () => [] },
  contracts: { type: Array, default: () => [] },
  dailyReports: { type: Array, default: () => [] },
  selectedDays: Number,
  showUsersTable: { type: Boolean, default: true },
  showActivity: { type: Boolean, default: true },
  canManageRoles: { type: Boolean, default: false }
})

const stats = computed(() => props.stats || {})
const showActivity = computed(() => props.showActivity)

function normalizedStatus(value) {
  return String(value || '').toLowerCase().replace(/\s+/g, '_')
}

function countApplications(statuses) {
  const wanted = Array.isArray(statuses) ? statuses : [statuses]
  return props.applications.filter((application) => wanted.includes(normalizedStatus(application.status))).length
}

const completionReviewCount = computed(() =>
  props.beneficiaries.filter((beneficiary) => normalizedStatus(beneficiary.application_status || beneficiary.status) === 'completion_review').length
)

const queueCards = computed(() => [
  {
    label: 'Applications Queue',
    value: countApplications(['pending', 'submitted', 'needs_correction', 'qualified']),
    description: 'Pending, correction, qualified, and approval-ready records.',
  },
  {
    label: 'Scheduling Queue',
    value: props.exams.length + props.interviews.length + props.contracts.length,
    description: 'Exam, interview, and contract signing schedules.',
  },
  {
    label: 'Placement Queue',
    value: countApplications(['approved', 'assigned', 'deployed']),
    description: 'Approved applicants moving through matching and deployment.',
  },
  {
    label: 'Completion Queue',
    value: completionReviewCount.value,
    description: 'Applications awaiting final CPESO completion approval.',
  },
])

const workflowGroups = computed(() => [
  {
    eyebrow: 'Applications',
    title: 'Applications Queue',
    href: '/peso/beneficiaries/pending',
    items: [
      { label: 'Pending', value: countApplications(['pending', 'submitted']) || props.stats?.pending_beneficiaries || 0 },
      { label: 'Needs Correction', value: countApplications('needs_correction') },
      { label: 'Qualified', value: countApplications('qualified') },
      { label: 'Approved', value: countApplications('approved') },
    ],
  },
  {
    eyebrow: 'Scheduling',
    title: 'Scheduling Queue',
    href: null,
    items: [
      { label: 'Exams', value: props.exams.length },
      { label: 'Interviews', value: props.interviews.length },
      { label: 'Contracts', value: props.contracts.length },
      { label: 'Rescheduled', value: [...props.exams, ...props.interviews, ...props.contracts].filter((item) => item.rescheduled_at).length },
    ],
  },
  {
    eyebrow: 'Placement',
    title: 'Placement Queue',
    href: '/admin/assignment',
    items: [
      { label: 'Ready for Matching', value: countApplications('approved') },
      { label: 'Assigned', value: countApplications('assigned') },
      { label: 'Deployed', value: countApplications('deployed') },
      { label: 'Daily Reports', value: props.dailyReports.length },
    ],
  },
  {
    eyebrow: 'Completion',
    title: 'Completion Queue',
    href: null,
    items: [
      { label: 'For Review', value: completionReviewCount.value },
      { label: 'Completed', value: countApplications('completed') },
      { label: 'Approved Reports', value: props.dailyReports.filter((report) => normalizedStatus(report.status) === 'approved').length },
      { label: 'Needs Action', value: props.dailyReports.filter((report) => ['submitted', 'under_review', 'needs_correction'].includes(normalizedStatus(report.status))).length },
    ],
  },
])

function go(href) {
  window.location.href = href
}
</script>
