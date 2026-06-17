<script setup>
import { computed, onMounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const loading = ref(true)
const exams = ref([])
const interviews = ref([])
const contracts = ref([])
const contractHistory = ref([])
const profile = ref(null)

const allSchedules = computed(() => {
  const items = [
    ...exams.value.map((exam) => ({
      id: `exam-${exam.id}`,
      source_id: exam.id,
      type: 'Exam',
      date: exam.exam_date,
      end_at: exam.end_at,
      location: exam.location,
      status: normalizeStatus(exam.result || exam.status, exam.exam_date),
      batch_title: exam.batch_title,
      instructions: exam.instructions || exam.notes || 'Please arrive early and bring any required identification or documents.',
      rescheduled_at: exam.rescheduled_at,
      reschedule_reason: exam.reschedule_reason,
      action: null,
    })),
    ...interviews.value.map((interview) => ({
      id: `interview-${interview.id}`,
      source_id: interview.id,
      type: 'Interview',
      date: interview.scheduled_at,
      end_at: interview.end_at,
      location: interview.meet_link || interview.location || interview.employer || 'To be announced',
      status: normalizeStatus(interview.result, interview.scheduled_at),
      batch_title: interview.batch_title,
      interviewer: interview.interviewer,
      instructions: interview.instructions || (interview.meet_link
        ? 'Use the meeting link when the interview window opens.'
        : 'Wait for CPESO or the employer to provide final instructions.'),
      rescheduled_at: interview.rescheduled_at,
      reschedule_reason: interview.reschedule_reason,
      action: interview.meet_link
        ? { label: 'Join Interview', href: interview.meet_link, external: true, enabled: Boolean(interview.can_join) }
        : null,
    })),
    ...contracts.value.map((contract) => ({
      id: `contract-${contract.id}`,
      source_id: contract.id,
      type: 'Contract Signing',
      date: contract.contract_date,
      end_at: contract.end_at,
      location: contract.location,
      status: normalizeStatus(contract.result || contract.status, contract.contract_date),
      batch_title: contract.batch_title,
      instructions: contract.instructions || contract.notes || 'Bring valid identification and follow CPESO contract signing instructions.',
      rescheduled_at: contract.rescheduled_at,
      reschedule_reason: contract.reschedule_reason,
      action: null,
    })),
    ...contractHistory.value.map((contract) => ({
      id: `contract-history-${contract.id}`,
      source_id: contract.id,
      type: 'Contract Signing',
      date: contract.contract_date,
      end_at: contract.end_at,
      location: contract.location,
      status: normalizeStatus(contract.result || contract.status, contract.contract_date),
      batch_title: contract.batch_title,
      instructions: contract.instructions || contract.notes || 'Contract signing record from your SPES application.',
      rescheduled_at: contract.rescheduled_at,
      reschedule_reason: contract.reschedule_reason,
      action: null,
    })),
    ...workStartItems.value,
  ]

  return dedupeSchedules(items)
    .filter((item) => item.date)
    .sort((a, b) => new Date(a.date) - new Date(b.date))
})

const upcomingSchedules = computed(() => {
  const now = new Date()
  return allSchedules.value.filter((item) => {
    const date = new Date(item.date)
    return date >= now && !['Done', 'Missed', 'Cancelled'].includes(item.status)
  })
})

const completedSchedules = computed(() => {
  const now = new Date()
  return allSchedules.value
    .filter((item) => {
      const date = new Date(item.date)
      return date < now || ['Done', 'Missed', 'Cancelled'].includes(item.status)
    })
    .sort((a, b) => new Date(b.date) - new Date(a.date))
})

const nextSchedule = computed(() => upcomingSchedules.value[0] || null)

const completedCount = computed(() => completedSchedules.value.length)

const workStartItems = computed(() => {
  const beneficiary = profile.value?.beneficiary || profile.value || {}
  const schedules = beneficiary.work_schedules || beneficiary.work_schedule || []
  const startDate = beneficiary.work_start_date || beneficiary.start_date || beneficiary.assigned_at

  const items = Array.isArray(schedules)
    ? schedules
        .map((schedule, index) => ({
          id: `work-start-${schedule.id || index}`,
          source_id: schedule.id || index,
          type: 'Work Start',
          date: schedule.start_date || schedule.date || schedule.created_at,
          location: schedule.location || schedule.employer || beneficiary.employer?.company || 'Assigned employer',
          status: normalizeStatus(schedule.status || 'scheduled', schedule.start_date || schedule.date),
          instructions: schedule.remarks || schedule.notes || 'Coordinate with your assigned employer for reporting instructions.',
          action: { label: 'View Job Placement', href: '/beneficiary/jobs' },
        }))
        .filter((item) => item.date)
    : []

  if (!items.length && startDate) {
    items.push({
      id: 'work-start-profile',
      source_id: 'profile',
      type: 'Work Start',
      date: startDate,
      location: beneficiary.employer?.company || beneficiary.employer_name || 'Assigned employer',
      status: normalizeStatus('scheduled', startDate),
      instructions: 'Coordinate with your assigned employer for reporting instructions.',
      action: { label: 'View Job Placement', href: '/beneficiary/jobs' },
    })
  }

  return items
})

function dedupeSchedules(items) {
  const seen = new Set()
  return items.filter((item) => {
    const key = `${item.type}-${item.source_id || item.id}-${item.date}`
    if (seen.has(key)) return false
    seen.add(key)
    return true
  })
}

function normalizeStatus(value, dateValue) {
  const status = String(value || '').toLowerCase()

  if (['done', 'passed', 'completed', 'approved', 'signed'].includes(status)) return 'Done'
  if (['failed', 'missed', 'no_show', 'no-show'].includes(status)) return 'Missed'
  if (['cancelled', 'canceled'].includes(status)) return 'Cancelled'
  if (['rescheduled', 'moved'].includes(status)) return 'Rescheduled'

  const date = new Date(dateValue)
  if (!Number.isNaN(date.getTime()) && date < new Date() && ['pending', 'scheduled', ''].includes(status)) {
    return 'Done'
  }

  return 'Scheduled'
}

function statusClasses(status) {
  return {
    Scheduled: 'bg-blue-100 text-blue-800 border-blue-200',
    Done: 'bg-green-100 text-green-800 border-green-200',
    Missed: 'bg-red-100 text-red-800 border-red-200',
    Cancelled: 'bg-slate-100 text-slate-700 border-slate-200',
    Rescheduled: 'bg-amber-100 text-amber-800 border-amber-200',
  }[status] || 'bg-slate-100 text-slate-700 border-slate-200'
}

function typeClasses(type) {
  return {
    Exam: 'bg-indigo-50 text-indigo-800',
    Interview: 'bg-blue-50 text-blue-800',
    'Contract Signing': 'bg-emerald-50 text-emerald-800',
    'Work Start': 'bg-amber-50 text-amber-800',
  }[type] || 'bg-slate-50 text-slate-800'
}

function formatDate(value) {
  if (!value) return 'Date to be announced'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila',
  })
}

function formatTime(value) {
  if (!value) return ''
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return ''

  return date.toLocaleTimeString('en-PH', {
    hour: 'numeric',
    minute: '2-digit',
    timeZone: 'Asia/Manila',
  })
}

function isSameCalendarDate(startValue, endValue) {
  const start = new Date(startValue)
  const end = new Date(endValue)

  if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime())) return false

  return start.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' }) ===
    end.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' })
}

function formatRange(item) {
  const start = formatDate(item.date)
  if (!item.end_at) return start

  return isSameCalendarDate(item.date, item.end_at)
    ? `${start} - ${formatTime(item.end_at)}`
    : `${start} - ${formatDate(item.end_at)}`
}

function openExternal(url) {
  window.open(url, '_blank', 'noopener,noreferrer')
}

async function loadSchedule() {
  loading.value = true

  const requests = await Promise.allSettled([
    axios.get('/api/beneficiary/exams'),
    axios.get('/api/beneficiary/interviews'),
    axios.get('/api/beneficiary/contracts'),
    axios.get('/api/beneficiary/contracts/history'),
    axios.get('/api/beneficiary/profile'),
  ])

  exams.value = normalizeResponse(requests[0].value?.data, 'exams')
  interviews.value = normalizeResponse(requests[1].value?.data)
  contracts.value = normalizeResponse(requests[2].value?.data)
  contractHistory.value = normalizeResponse(requests[3].value?.data)
  profile.value = requests[4].value?.data || null

  loading.value = false
}

function normalizeResponse(data, key = null) {
  if (Array.isArray(data)) return data
  if (key && Array.isArray(data?.[key])) return data[key]
  return []
}

onMounted(loadSchedule)
</script>

<template>
  <Head title="Schedule" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <Link
        href="/beneficiary"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Dashboard
      </Link>

      <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">SPES Activities</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Schedule</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          View your upcoming SPES activities and instructions.
        </p>
      </section>

      <section class="mt-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
          <p class="text-sm font-semibold text-slate-600">Next upcoming schedule</p>
          <div v-if="nextSchedule" class="mt-3">
            <div class="flex flex-wrap items-center gap-2">
              <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="typeClasses(nextSchedule.type)">
                {{ nextSchedule.type }}
              </span>
              <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClasses(nextSchedule.status)">
                {{ nextSchedule.status }}
              </span>
            </div>
            <p v-if="nextSchedule.batch_title" class="mt-3 text-xs font-bold uppercase tracking-wide text-blue-600">{{ nextSchedule.batch_title }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ formatRange(nextSchedule) }}</p>
            <p class="mt-1 text-sm text-slate-600">{{ nextSchedule.location || 'Location to be announced' }}</p>
            <p v-if="nextSchedule.interviewer" class="mt-1 text-sm text-slate-600">Interviewer: {{ nextSchedule.interviewer.name }}</p>
            <p v-if="nextSchedule.rescheduled_at" class="mt-2 rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">
              This schedule was rescheduled.
              <span v-if="nextSchedule.reschedule_reason">Reason: {{ nextSchedule.reschedule_reason }}</span>
            </p>
          </div>
          <p v-else class="mt-3 text-sm text-slate-500">No upcoming SPES schedule yet.</p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
          <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-600">Upcoming activities</p>
            <p class="mt-3 text-3xl font-bold text-blue-700">{{ upcomingSchedules.length }}</p>
          </div>
          <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-slate-600">Completed activities</p>
            <p class="mt-3 text-3xl font-bold text-green-700">{{ completedCount }}</p>
          </div>
        </div>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
          <h2 class="text-lg font-bold text-slate-900">Upcoming</h2>
          <p class="mt-1 text-sm text-slate-500">Exams, interviews, contract signing, and work start dates.</p>
        </div>

        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">Loading your schedule...</div>
        <div v-else-if="upcomingSchedules.length === 0" class="p-8 text-center text-sm text-slate-500">
          No upcoming activities scheduled.
        </div>
        <div v-else class="divide-y divide-slate-200">
          <article v-for="item in upcomingSchedules" :key="item.id" class="p-5 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="typeClasses(item.type)">
                    {{ item.type }}
                  </span>
                  <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClasses(item.status)">
                    {{ item.status }}
                  </span>
                </div>
                <p v-if="item.batch_title" class="mt-3 text-xs font-bold uppercase tracking-wide text-blue-600">{{ item.batch_title }}</p>
                <h3 class="mt-2 text-xl font-bold text-slate-900">{{ formatRange(item) }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ item.location || 'Location or meeting link to be announced' }}</p>
                <p v-if="item.interviewer" class="mt-1 text-sm text-slate-600">Interviewer: {{ item.interviewer.name }}</p>
                <p class="mt-3 text-sm leading-6 text-slate-600">{{ item.instructions || 'Please wait for CPESO instructions.' }}</p>
                <p v-if="item.rescheduled_at" class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-sm font-semibold text-amber-800">
                  Rescheduled
                  <span v-if="item.reschedule_reason">: {{ item.reschedule_reason }}</span>
                </p>
              </div>

              <button
                v-if="item.action?.external"
                type="button"
                :disabled="!item.action.enabled"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                @click="openExternal(item.action.href)"
              >
                {{ item.action.enabled ? item.action.label : 'Available near schedule' }}
              </button>

              <Link
                v-else-if="item.action"
                :href="item.action.href"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
              >
                {{ item.action.label }}
              </Link>
            </div>
          </article>
        </div>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
          <h2 class="text-lg font-bold text-slate-900">Completed / Past</h2>
          <p class="mt-1 text-sm text-slate-500">Previous SPES activities shown from available records.</p>
        </div>

        <div v-if="!loading && completedSchedules.length === 0" class="p-8 text-center text-sm text-slate-500">
          No completed or past activities found yet.
        </div>
        <div v-else class="divide-y divide-slate-200">
          <article v-for="item in completedSchedules" :key="item.id" class="p-5 sm:p-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <span class="rounded-full px-3 py-1 text-xs font-semibold" :class="typeClasses(item.type)">
                    {{ item.type }}
                  </span>
                  <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClasses(item.status)">
                    {{ item.status }}
                  </span>
                </div>
                <p v-if="item.batch_title" class="mt-3 text-xs font-bold uppercase tracking-wide text-blue-600">{{ item.batch_title }}</p>
                <h3 class="mt-2 text-base font-bold text-slate-900">{{ formatRange(item) }}</h3>
                <p class="mt-1 text-sm text-slate-600">{{ item.location || 'Location not posted' }}</p>
                <p v-if="item.interviewer" class="mt-1 text-sm text-slate-600">Interviewer: {{ item.interviewer.name }}</p>
                <p v-if="item.rescheduled_at" class="mt-2 text-sm font-semibold text-amber-700">
                  Rescheduled<span v-if="item.reschedule_reason">: {{ item.reschedule_reason }}</span>
                </p>
              </div>
              <p class="max-w-xl text-sm leading-6 text-slate-600">{{ item.instructions }}</p>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
