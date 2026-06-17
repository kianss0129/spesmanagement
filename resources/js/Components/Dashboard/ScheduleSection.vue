<script setup>
import { computed, reactive, ref, watch } from 'vue'

const props = defineProps({
  selectedTab: String,
  exams: {
    type: Array,
    default: () => [],
  },
  interviews: {
    type: Array,
    default: () => [],
  },
  contracts: {
    type: Array,
    default: () => [],
  },
  users: {
    type: Array,
    default: () => [],
  },
  rescheduleSchedule: {
    type: Function,
    default: async () => {},
  },
  updateExamResult: {
    type: Function,
    default: () => {},
  },
  updateInterviewResult: {
    type: Function,
    default: () => {},
  },
  updateContractResult: {
    type: Function,
    default: () => {},
  },
  markContractDeployed: {
    type: Function,
    default: () => {},
  },
  markApplicationQualified: {
    type: Function,
    default: () => {},
  },
  formatDate: {
    type: Function,
    default: (date) => date || 'Not set',
  },
  canCreateSchedules: {
    type: Boolean,
    default: false,
  },
  canRescheduleSchedules: {
    type: Boolean,
    default: false,
  },
  canManageExamsContracts: {
    type: Boolean,
    default: false,
  },
  canCompleteAssignedInterviews: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['open-scheduler'])

const activeType = ref('exams')
const interviewReviewFilter = ref('all')
const selectedItem = ref(null)
const rescheduleItem = ref(null)
const rescheduling = ref(false)
const rescheduleForm = reactive({
  scope: 'single',
  date: '',
  start_time: '',
  end_time: '',
  meet_link: '',
  location: '',
  interviewer_id: '',
  reschedule_reason: '',
  instructions: '',
  notify_beneficiaries: true,
})

const pesoInterviewers = computed(() => {
  return (props.users || []).filter((user) => {
    const hasRoleData = user.role || user.role_name || Array.isArray(user.roles)

    if (!hasRoleData) {
      return true
    }

    const roleNames = [
      user.role,
      user.role_name,
      ...(Array.isArray(user.roles) ? user.roles.map((role) => role?.name || role) : []),
    ]

    return roleNames.some((role) => String(role || '').toLowerCase() === 'peso')
  })
})

const visibleTabs = computed(() => {
  if (props.canManageExamsContracts) {
    return [
      { key: 'exams', label: 'Exams' },
      { key: 'interviews', label: 'Interviews' },
      { key: 'contracts', label: 'Contracts' },
    ]
  }

  return [{ key: 'interviews', label: 'Interviews' }]
})

watch(
  visibleTabs,
  (tabs) => {
    if (!tabs.some((tab) => tab.key === activeType.value)) {
      activeType.value = tabs[0]?.key || 'interviews'
    }
  },
  { immediate: true }
)

const rows = computed(() => {
  if (activeType.value === 'exams') {
    return props.exams.map((exam) => normalizeRow(exam, 'Exam', {
      date: exam.exam_date || exam.date || exam.scheduled_at,
      location: exam.location || 'To be announced',
      mode: 'FACE-TO-FACE / Venue',
    }))
  }

  if (activeType.value === 'interviews') {
    const interviewRows = props.interviews.map((interview) => normalizeRow(interview, 'Interview', {
      date: interview.scheduled_at || interview.start || interview.date,
      location: interview.meet_link || interview.location || 'To be announced',
      mode: 'ONLINE / Google Meet',
    }))

    if (interviewReviewFilter.value === 'scheduled') {
      return interviewRows.filter((row) => row.status === 'scheduled')
    }

    if (interviewReviewFilter.value === 'passed') {
      return interviewRows.filter((row) => row.result === 'passed')
    }

    if (interviewReviewFilter.value === 'failed') {
      return interviewRows.filter((row) => row.result === 'failed')
    }

    if (interviewReviewFilter.value === 'needs_review') {
      return interviewRows.filter((row) => row.result === 'needs_review')
    }

    return interviewRows
  }

  return props.contracts.map((contract) => normalizeRow(contract, 'Contract', {
    date: contract.contract_date || contract.date || contract.scheduled_at,
    location: contract.location || 'To be announced',
    mode: 'FACE-TO-FACE / Venue',
  }))
})

const summaryCards = computed(() => {
  const all = [
    ...props.exams.map((item) => ({ ...item, date: item.exam_date || item.date })),
    ...props.interviews.map((item) => ({ ...item, date: item.scheduled_at || item.start || item.date })),
    ...props.contracts.map((item) => ({ ...item, date: item.contract_date || item.date })),
  ]

  return [
    { label: 'Today', value: all.filter(isToday).length },
    { label: 'Upcoming', value: all.filter(isUpcoming).length },
    { label: 'Completed', value: all.filter(isCompleted).length },
    { label: 'Rescheduled', value: all.filter(isRescheduled).length },
  ]
})

function normalizeRow(item, type, options) {
  return {
    id: item.id,
    beneficiary: item.beneficiary_name || `Applicant #${item.application_id || item.id}`,
    type,
    date: options.date,
    end_at: item.end_at,
    application_id: item.application_id,
    application_status: item.application_status,
    location: options.location,
    mode: options.mode,
    status: item.status || item.result || 'scheduled',
    result: item.result,
    batch_title: item.batch_title,
    schedule_group_id: item.schedule_group_id,
    interviewer: item.interviewer,
    instructions: item.instructions,
    remarks: item.remarks,
    evaluated_at: item.evaluated_at,
    rescheduled_at: item.rescheduled_at,
    reschedule_reason: item.reschedule_reason,
    notify_beneficiaries: item.notify_beneficiaries ?? true,
    raw: item,
  }
}

function isToday(item) {
  if (!item.date) return false
  const date = new Date(item.date)
  return !Number.isNaN(date.getTime()) && date.toDateString() === new Date().toDateString()
}

function isUpcoming(item) {
  if (!item.date) return false
  const date = new Date(item.date)
  return !Number.isNaN(date.getTime()) && date > new Date()
}

function isCompleted(item) {
  const status = String(item.status || item.result || '').toLowerCase()
  return status.includes('completed') || status.includes('done') || status.includes('passed') || status.includes('signed')
}

function isCancelled(item) {
  return String(item.status || '').toLowerCase().includes('cancel')
}

function isRescheduled(item) {
  return Boolean(item.rescheduled_at) || String(item.status || '').toLowerCase().includes('rescheduled')
}

function statusClass(status) {
  const value = String(status || '').toLowerCase()
  if (value.includes('complete') || value.includes('done') || value.includes('passed') || value.includes('signed')) return 'bg-green-100 text-green-800'
  if (value.includes('rescheduled')) return 'bg-blue-100 text-blue-800'
  if (value.includes('cancel') || value.includes('failed') || value.includes('not')) return 'bg-red-100 text-red-800'
  return 'bg-amber-100 text-amber-800'
}

function displayStatus(status) {
  const text = String(status || 'Scheduled').replace(/_/g, ' ')
  return text.replace(/\b\w/g, (char) => char.toUpperCase())
}

function displayResult(result) {
  const text = String(result || 'pending').replace(/_/g, ' ')
  return text.replace(/\b\w/g, (char) => char.toUpperCase())
}

function datePart(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toISOString().slice(0, 10)
}

function timePart(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''
  return date.toTimeString().slice(0, 5)
}

function combineDateTime(date, time) {
  if (!date || !time) return ''
  return `${date}T${time}`
}

function openReschedule(row) {
  rescheduleItem.value = row
  rescheduleForm.scope = 'single'
  rescheduleForm.date = datePart(row.date)
  rescheduleForm.start_time = timePart(row.date)
  rescheduleForm.end_time = timePart(row.end_at)
  rescheduleForm.meet_link = row.type === 'Interview' && String(row.location).startsWith('http') ? row.location : ''
  rescheduleForm.location = row.type !== 'Interview' ? row.location : ''
  rescheduleForm.interviewer_id = row.raw?.interviewer_id || ''
  rescheduleForm.reschedule_reason = ''
  rescheduleForm.instructions = row.instructions || ''
  rescheduleForm.notify_beneficiaries = row.notify_beneficiaries ?? true
}

function closeReschedule() {
  rescheduleItem.value = null
}

async function submitReschedule() {
  if (!rescheduleItem.value) return

  const start = combineDateTime(rescheduleForm.date, rescheduleForm.start_time)
  const endAt = combineDateTime(rescheduleForm.date, rescheduleForm.end_time)
  const row = rescheduleItem.value

  const payload = {
    reschedule_scope: rescheduleForm.scope,
    end_at: endAt,
    reschedule_reason: rescheduleForm.reschedule_reason,
    instructions: rescheduleForm.instructions || null,
    notify_beneficiaries: rescheduleForm.notify_beneficiaries,
  }

  if (row.type === 'Interview') {
    payload.scheduled_at = start
    payload.meet_link = rescheduleForm.meet_link
    payload.interviewer_id = rescheduleForm.interviewer_id || null
  } else if (row.type === 'Exam') {
    payload.exam_date = start
    payload.location = rescheduleForm.location
  } else {
    payload.contract_date = start
    payload.location = rescheduleForm.location
  }

  rescheduling.value = true
  try {
    await props.rescheduleSchedule(row, payload)
    closeReschedule()
  } finally {
    rescheduling.value = false
  }
}

function markDone(row) {
  if (row.type === 'Exam') props.updateExamResult(row.id, 'passed')
  if (row.type === 'Interview') props.updateInterviewResult(row.id, 'passed')
  if (row.type === 'Contract') props.updateContractResult(row.id, 'signed')
}

function markDeployed(row) {
  props.markContractDeployed(row.application_id)
}

function markQualified(row) {
  props.markApplicationQualified({ id: row.application_id })
}

function canMarkDeployed(row) {
  return props.canManageExamsContracts &&
    row.type === 'Contract' &&
    String(row.application_status || '').toLowerCase() === 'contract_signed' &&
    row.application_id
}

function canMarkDone(row) {
  if (row.type === 'Interview') {
    return false
  }

  if (row.type === 'Exam') {
    return props.canManageExamsContracts &&
      String(row.status || '').toLowerCase() !== 'completed' &&
      !isCancelled(row) &&
      !['passed', 'failed'].includes(String(row.result || '').toLowerCase())
  }

  if (row.type === 'Contract') {
    return props.canManageExamsContracts &&
      String(row.status || '').toLowerCase() !== 'completed' &&
      !isCancelled(row) &&
      !['signed', 'not_signed'].includes(String(row.result || '').toLowerCase())
  }

  if (props.canManageExamsContracts) {
    return true
  }

  return row.type === 'Interview' && props.canCompleteAssignedInterviews
}

function canReschedule(row) {
  return props.canRescheduleSchedules &&
    !isCompleted(row) &&
    !isCancelled(row)
}

function canMarkInterviewQualified(row) {
  return props.canManageExamsContracts &&
    row.type === 'Interview' &&
    row.result === 'passed' &&
    row.application_status === 'interview_passed' &&
    row.application_id
}
</script>

<template>
  <section v-if="selectedTab === 'schedule'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Calendar</p>
          <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Schedule</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
            Review interviews, exams, and contract signing schedules in one place.
          </p>
        </div>

        <div v-if="canCreateSchedules" class="flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
            @click="emit('open-scheduler', 'interviews')"
          >
            Schedule Interview
          </button>
          <button
            type="button"
            class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
            @click="emit('open-scheduler', 'exam')"
          >
            Schedule Exam
          </button>
          <button
            type="button"
            class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700"
            @click="emit('open-scheduler', 'contract')"
          >
            Schedule Contract Signing
          </button>
        </div>
      </div>
    </header>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in summaryCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
      </div>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
      <div class="flex flex-wrap gap-2">
        <button
          v-for="tab in visibleTabs"
          :key="tab.key"
          type="button"
          class="rounded-lg px-4 py-2 text-sm font-semibold"
          :class="activeType === tab.key ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="activeType = tab.key"
        >
          {{ tab.label }}
        </button>
      </div>
      <div v-if="canManageExamsContracts && activeType === 'interviews'" class="mt-4 flex flex-wrap gap-2 border-t border-slate-200 pt-4">
        <button
          v-for="filter in [
            { key: 'all', label: 'All Interview Reviews' },
            { key: 'scheduled', label: 'Scheduled' },
            { key: 'passed', label: 'Passed' },
            { key: 'failed', label: 'Failed' },
            { key: 'needs_review', label: 'Needs Review' },
          ]"
          :key="filter.key"
          type="button"
          class="rounded-lg px-3 py-2 text-sm font-semibold"
          :class="interviewReviewFilter === filter.key ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
          @click="interviewReviewFilter = filter.key"
        >
          {{ filter.label }}
        </button>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1.15fr_0.8fr_1.05fr_1.2fr_0.8fr_1.2fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Beneficiary</span>
        <span>Type</span>
        <span>Date / Time</span>
        <span>Link / Venue</span>
        <span>Status</span>
        <span>Action</span>
      </div>

      <article
        v-for="row in rows"
        :key="`${row.type}-${row.id}`"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.15fr_0.8fr_1.05fr_1.2fr_0.8fr_1.2fr] xl:items-start"
      >
        <div>
          <p v-if="row.batch_title" class="text-xs font-bold uppercase tracking-wide text-blue-600">{{ row.batch_title }}</p>
          <p class="mt-1 font-semibold text-slate-900">{{ row.beneficiary }}</p>
          <p v-if="row.schedule_group_id" class="mt-1 text-xs text-slate-500">Batch ref: {{ row.schedule_group_id }}</p>
        </div>

        <div>
          <p class="text-sm font-semibold text-slate-800">{{ row.type }}</p>
          <p class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-500">{{ row.mode }}</p>
          <p v-if="row.type === 'Interview'" class="mt-2 text-xs text-slate-600">
            Interviewer: {{ row.interviewer?.name || 'Not assigned' }}
          </p>
        </div>

        <p class="text-sm text-slate-700">
          {{ formatDate(row.date) }}
          <span v-if="row.end_at"> - {{ formatDate(row.end_at) }}</span>
        </p>

        <div>
          <a
            v-if="String(row.location).startsWith('http')"
            :href="row.location"
            target="_blank"
            rel="noopener noreferrer"
            class="break-words text-sm font-semibold text-blue-700"
          >
            Google Meet Link
          </a>
          <p v-else class="text-sm text-slate-700">{{ row.location }}</p>
          <p v-if="row.instructions" class="mt-2 text-xs leading-5 text-slate-600">{{ row.instructions }}</p>
          <p v-if="row.reschedule_reason" class="mt-2 text-xs font-semibold text-amber-700">
            Reason: {{ row.reschedule_reason }}
          </p>
        </div>

        <div class="space-y-2">
          <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(row.status)">
            {{ displayStatus(row.status) }}
          </span>
          <span v-if="row.type === 'Interview'" class="block w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
            Result: {{ displayResult(row.result) }}
          </span>
          <span v-if="row.rescheduled_at" class="block w-fit rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">
            Rescheduled
          </span>
        </div>

        <div class="flex flex-wrap gap-2">
          <button class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="selectedItem = row">
            View
          </button>
          <button v-if="canReschedule(row)" class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800 hover:bg-amber-100" @click="openReschedule(row)">
            Reschedule
          </button>
          <button v-if="canMarkDone(row)" class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100" @click="markDone(row)">
            Mark Done
          </button>
          <button v-if="canMarkDeployed(row)" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="markDeployed(row)">
            Mark as Deployed
          </button>
          <button v-if="canMarkInterviewQualified(row)" class="rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-100" @click="markQualified(row)">
            Mark Qualified
          </button>
        </div>
      </article>

      <div v-if="rows.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No schedules found.</p>
        <p class="mt-1 text-sm text-slate-500">Switch tabs or create a new schedule from the existing workflows.</p>
      </div>
    </section>

    <div v-if="selectedItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="selectedItem = null">
      <div class="max-h-[90vh] w-full max-w-lg overflow-auto rounded-lg bg-white p-6 shadow-xl">
        <h2 class="text-lg font-bold text-slate-900">{{ selectedItem.type }} Details</h2>
        <dl class="mt-4 space-y-3 text-sm">
          <div><dt class="font-semibold text-slate-500">Batch</dt><dd class="mt-1 text-slate-900">{{ selectedItem.batch_title || 'No batch title' }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Beneficiary</dt><dd class="mt-1 text-slate-900">{{ selectedItem.beneficiary }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Date / Time</dt><dd class="mt-1 text-slate-900">{{ formatDate(selectedItem.date) }}<span v-if="selectedItem.end_at"> - {{ formatDate(selectedItem.end_at) }}</span></dd></div>
          <div><dt class="font-semibold text-slate-500">Location / Link</dt><dd class="mt-1 break-words text-slate-900">{{ selectedItem.location }}</dd></div>
          <div v-if="selectedItem.type === 'Interview'"><dt class="font-semibold text-slate-500">Interviewer</dt><dd class="mt-1 text-slate-900">{{ selectedItem.interviewer?.name || 'Not assigned' }}</dd></div>
          <div v-if="selectedItem.type === 'Interview'"><dt class="font-semibold text-slate-500">Result</dt><dd class="mt-1 text-slate-900">{{ displayResult(selectedItem.result) }}</dd></div>
          <div v-if="selectedItem.remarks"><dt class="font-semibold text-slate-500">Evaluation Remarks</dt><dd class="mt-1 whitespace-pre-line text-slate-900">{{ selectedItem.remarks }}</dd></div>
          <div><dt class="font-semibold text-slate-500">Instructions</dt><dd class="mt-1 text-slate-900">{{ selectedItem.instructions || 'None' }}</dd></div>
          <div v-if="selectedItem.reschedule_reason"><dt class="font-semibold text-slate-500">Reschedule Reason</dt><dd class="mt-1 text-slate-900">{{ selectedItem.reschedule_reason }}</dd></div>
        </dl>
        <div class="mt-6 flex justify-end">
          <button class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white" @click="selectedItem = null">Close</button>
        </div>
      </div>
    </div>

    <div v-if="rescheduleItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeReschedule">
      <form class="max-h-[92vh] w-full max-w-2xl overflow-auto rounded-lg bg-white p-5 shadow-xl sm:p-6" @submit.prevent="submitReschedule">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Reschedule</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ rescheduleItem.type }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ rescheduleItem.beneficiary }}</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeReschedule">Close</button>
        </div>

        <div class="mt-5 grid gap-4 md:grid-cols-2">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Schedule Type</label>
            <input :value="rescheduleItem.type" readonly class="w-full rounded-lg border border-slate-300 bg-slate-50 px-4 py-2 text-sm">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Reschedule Scope</label>
            <select v-model="rescheduleForm.scope" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
              <option value="single">This beneficiary only</option>
              <option :disabled="!rescheduleItem.schedule_group_id" value="batch">Whole batch</option>
            </select>
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">New Date</label>
            <input v-model="rescheduleForm.date" type="date" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">New Start Time</label>
            <input v-model="rescheduleForm.start_time" type="time" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">New End Time</label>
            <input v-model="rescheduleForm.end_time" type="time" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
          </div>
          <div v-if="rescheduleItem.type === 'Interview'">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Google Meet Link</label>
            <input v-model="rescheduleForm.meet_link" type="url" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
          </div>
          <div v-else>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Venue / Location</label>
            <input v-model="rescheduleForm.location" type="text" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
          </div>
          <div v-if="rescheduleItem.type === 'Interview'">
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Interviewer</label>
            <select v-model="rescheduleForm.interviewer_id" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm">
              <option value="">Select interviewer</option>
              <option v-for="user in pesoInterviewers" :key="user.id" :value="user.id">
                {{ user.name || `User #${user.id}` }} - {{ user.email }}
              </option>
            </select>
          </div>
        </div>

        <div class="mt-4">
          <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Reason for Reschedule</label>
          <textarea v-model="rescheduleForm.reschedule_reason" rows="3" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm"></textarea>
        </div>
        <div class="mt-4">
          <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Instructions</label>
          <textarea v-model="rescheduleForm.instructions" rows="3" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm"></textarea>
        </div>
        <label class="mt-4 flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
          <input v-model="rescheduleForm.notify_beneficiaries" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
          Notify beneficiaries
        </label>

        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="closeReschedule">Cancel</button>
          <button type="submit" :disabled="rescheduling" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50">
            {{ rescheduling ? 'Saving...' : 'Save Reschedule' }}
          </button>
        </div>
      </form>
    </div>
  </section>
</template>
