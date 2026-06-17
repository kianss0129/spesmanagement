<script setup>
import { computed } from 'vue'

const props = defineProps({
  selectedTab: String,
  applications: {
    type: Array,
    default: () => [],
  },
  interviews: {
    type: Array,
    default: () => [],
  },
  scheduleForm: {
    type: Object,
    required: true,
  },
  users: {
    type: Array,
    default: () => [],
  },
  minDateTime: String,
  schedulingInterview: {
    type: Boolean,
    default: false,
  },
  isValidText: Function,
  isAssignedApplication: Function,
  scheduleInterview: Function,
  updateInterviewResult: Function,
  formatDate: Function,
})

const unassignedApplications = computed(() => {
  return (props.applications || []).filter((app) => {
    const status = String(app?.status || '').toLowerCase()
    const interviewResult = String(
      app?.interview_result || app?.interview?.result || ''
    ).toLowerCase()

    if (status !== 'for_interview') {
      return false
    }

    if (['passed', 'failed'].includes(interviewResult)) {
      return false
    }

    if (typeof props.isAssignedApplication === 'function') {
      return !props.isAssignedApplication(app)
    }

    return true
  })
})

const selectedApplications = computed(() => {
  const apps = props.applications || []
  const selectedIds = props.scheduleForm?.application_ids || []

  return selectedIds
    .map((id) => apps.find((app) => Number(app.id) === Number(id)))
    .filter(Boolean)
})

const totalInterviews = computed(() => props.interviews?.length || 0)

const pendingInterviews = computed(() => {
  return (props.interviews || []).filter((interview) => interview.result === 'pending')
})

const scheduledInterviews = computed(() => {
  return (props.interviews || []).filter((interview) => interview.status === 'scheduled')
})

const completedInterviews = computed(() => {
  return (props.interviews || []).filter((interview) => {
    return interview.status === 'completed' || ['passed', 'failed'].includes(interview.result)
  })
})

const upcomingInterviews = computed(() => {
  return [...(props.interviews || [])].sort((a, b) => {
    return new Date(a.scheduled_at || a.start || 0) - new Date(b.scheduled_at || b.start || 0)
  })
})

const statCards = computed(() => [
  {
    label: 'Total Interviews',
    value: totalInterviews.value,
    detail: 'All interview records',
    tone: 'border-slate-200 bg-white text-slate-900',
  },
  {
    label: 'Scheduled',
    value: scheduledInterviews.value.length,
    detail: 'Ready for interview',
    tone: 'border-blue-200 bg-blue-50 text-blue-900',
  },
  {
    label: 'Completed',
    value: completedInterviews.value.length,
    detail: 'Evaluated applicants',
    tone: 'border-emerald-200 bg-emerald-50 text-emerald-900',
  },
  {
    label: 'Pending',
    value: pendingInterviews.value.length,
    detail: 'Awaiting result',
    tone: 'border-amber-200 bg-amber-50 text-amber-900',
  },
])

const scheduleOverview = computed(() => {
  const grouped = new Map()

  upcomingInterviews.value.forEach((interview) => {
    const rawDate = interview.scheduled_at || interview.start
    const date = rawDate ? new Date(rawDate) : null
    const key = date && !Number.isNaN(date.getTime())
      ? date.toISOString().slice(0, 10)
      : 'unscheduled'

    if (!grouped.has(key)) {
      grouped.set(key, {
        key,
        date,
        label: date
          ? date.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' })
          : 'Unscheduled',
        interviews: [],
      })
    }

    grouped.get(key).interviews.push(interview)
  })

  return Array.from(grouped.values()).slice(0, 6)
})

const interviewerOptions = computed(() => {
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

const safeIsValidText = (text) => {
  if (typeof props.isValidText === 'function') {
    return props.isValidText(text)
  }

  return text !== null && text !== undefined && String(text).trim().length > 0
}

const safeFormatDate = (date) => {
  if (typeof props.formatDate === 'function') {
    return props.formatDate(date)
  }

  return date || 'Not set'
}

function ensureApplicationIds() {
  if (!props.scheduleForm.application_ids) {
    props.scheduleForm.application_ids = []
  }
}

function toggleApplicantSelection(application) {
  ensureApplicationIds()

  const id = application.id
  const exists = props.scheduleForm.application_ids.some((item) => Number(item) === Number(id))

  if (exists) {
    props.scheduleForm.application_ids = props.scheduleForm.application_ids.filter(
      (item) => Number(item) !== Number(id)
    )
  } else {
    props.scheduleForm.application_ids.push(id)
  }
}

function selectAllUnassigned() {
  props.scheduleForm.application_ids = unassignedApplications.value.map((app) => app.id)
}

function selectBatchBySize() {
  const size = Number(props.scheduleForm.batch_size) || 0

  if (size <= 0) {
    props.scheduleForm.application_ids = []
    return
  }

  props.scheduleForm.application_ids = unassignedApplications.value
    .slice(0, size)
    .map((app) => app.id)
}

function clearSelection() {
  props.scheduleForm.application_ids = []
}

function isSelected(applicationId) {
  return (props.scheduleForm.application_ids || []).some(
    (id) => Number(id) === Number(applicationId)
  )
}

function getApplicantName(app) {
  return app?.beneficiary_name || app?.name || `Applicant #${app?.id}`
}

function getApplicantDetails(app) {
  const rawCategory = app?.category || app?.beneficiary_type || ''
  const cat = String(rawCategory).toLowerCase()
  let categoryLabel = ''
  if (cat === 'student') categoryLabel = 'Student'
  else if (cat === 'osy') categoryLabel = 'OSY'
  else if (cat === 'dependent') categoryLabel = 'Dependent of Displaced Worker'
  else if (rawCategory) categoryLabel = rawCategory

  const status = String(app?.status || '').toUpperCase().replace(/_/g, ' ')

  if (categoryLabel && status) return `${categoryLabel} • ${status}`
  if (categoryLabel) return categoryLabel
  if (status) return status
  return ''
}

function getInitial(name) {
  if (!safeIsValidText(name)) {
    return 'A'
  }

  return String(name).trim().charAt(0).toUpperCase()
}

function getInterviewName(interview) {
  return interview?.beneficiary_name
    || interview?.beneficiary_profile?.name
    || 'Applicant'
}

function getInterviewerName(interview) {
  return interview?.interviewer?.name
    || interview?.scheduled_by_user?.name
    || 'Not assigned'
}

function getDayNumber(date) {
  const parsed = date ? new Date(date) : null
  return parsed && !Number.isNaN(parsed.getTime())
    ? parsed.toLocaleDateString(undefined, { day: '2-digit' })
    : '--'
}

function getMonthLabel(date) {
  const parsed = date ? new Date(date) : null
  return parsed && !Number.isNaN(parsed.getTime())
    ? parsed.toLocaleDateString(undefined, { month: 'short' })
    : 'TBA'
}

function getTimeLabel(date) {
  const parsed = date ? new Date(date) : null
  return parsed && !Number.isNaN(parsed.getTime())
    ? parsed.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' })
    : 'Not set'
}

function getTimeRange(interview) {
  const start = getTimeLabel(interview?.scheduled_at || interview?.start)
  const end = interview?.end_at ? getTimeLabel(interview.end_at) : null

  return end ? `${start} - ${end}` : start
}

function resultClass(result) {
  return {
    passed: 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    failed: 'bg-red-50 text-red-700 ring-red-200',
    pending: 'bg-amber-50 text-amber-700 ring-amber-200',
  }[result] || 'bg-slate-50 text-slate-600 ring-slate-200'
}

function statusClass(status) {
  return {
    scheduled: 'bg-blue-50 text-blue-700 ring-blue-200',
    completed: 'bg-slate-50 text-slate-700 ring-slate-200',
    cancelled: 'bg-red-50 text-red-700 ring-red-200',
  }[status] || 'bg-slate-50 text-slate-600 ring-slate-200'
}
</script>

<template>
  <section
    v-if="selectedTab === 'interviews'"
    class="w-full max-w-none space-y-8"
  >
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
      <div class="flex flex-col gap-6 2xl:flex-row 2xl:items-end 2xl:justify-between">
        <div class="max-w-3xl">
          <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">
            CPESO Interview Management
          </p>
          <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">
            Interview Scheduling
          </h2>
          <p class="mt-3 text-base leading-7 text-slate-600">
            Coordinate applicant batches, assign CPESO interviewers, and monitor upcoming interview outcomes from one dashboard workspace.
          </p>
        </div>

        <div class="grid w-full grid-cols-1 gap-4 sm:grid-cols-2 2xl:max-w-3xl 2xl:grid-cols-4">
          <div
            v-for="card in statCards"
            :key="card.label"
            :class="card.tone"
            class="rounded-2xl border p-5 shadow-sm"
          >
            <p class="text-xs font-bold uppercase tracking-wide opacity-70">
              {{ card.label }}
            </p>
            <p class="mt-3 text-3xl font-black">
              {{ card.value }}
            </p>
            <p class="mt-1 text-xs font-semibold opacity-70">
              {{ card.detail }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-8 xl:grid-cols-2">
      <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-6 sm:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-2xl font-black text-slate-950">
                Interview Scheduler
              </h3>
              <p class="mt-1 text-sm text-slate-500">
                Build a batch schedule and notify selected beneficiaries.
              </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-cyan-50 px-4 py-2 text-sm font-bold text-cyan-700">
              {{ selectedApplications.length }} selected
            </span>
          </div>
        </div>

        <form
          @submit.prevent="scheduleInterview"
          class="space-y-6 p-6 sm:p-8"
        >
          <div class="grid gap-5 lg:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Batch Title
              </label>
              <input
                v-model="scheduleForm.batch_title"
                type="text"
                placeholder="Example: Interview Batch A"
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Batch Selector
              </label>
              <div class="grid grid-cols-[1fr_auto] gap-3">
                <input
                  v-model.number="scheduleForm.batch_size"
                  type="number"
                  min="1"
                  placeholder="No. of applicants"
                  class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                />
                <button
                  type="button"
                  @click="selectBatchBySize"
                  :disabled="!unassignedApplications.length || !scheduleForm.batch_size"
                  class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Apply
                </button>
              </div>
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="text-sm font-black text-slate-900">
                  Applicant Selector
                </p>
                <p class="mt-1 text-xs font-semibold text-slate-500">
                  {{ unassignedApplications.length }} applicants available for scheduling.
                </p>
              </div>
              <div class="flex flex-wrap gap-2">
                <button
                  type="button"
                  @click="selectAllUnassigned"
                  :disabled="!unassignedApplications.length"
                  class="rounded-xl bg-cyan-600 px-4 py-2 text-xs font-black text-white transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Select All
                </button>
                <button
                  type="button"
                  @click="clearSelection"
                  :disabled="selectedApplications.length === 0"
                  class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Clear
                </button>
              </div>
            </div>

            <div
              v-if="selectedApplications.length"
              class="mt-4 flex flex-wrap gap-2"
            >
              <button
                v-for="app in selectedApplications"
                :key="app.id"
                type="button"
                @click="toggleApplicantSelection(app)"
                class="inline-flex max-w-full items-center gap-2 rounded-full border border-cyan-200 bg-white px-3 py-2 text-xs font-bold text-cyan-800 shadow-sm transition hover:border-red-200 hover:text-red-600"
              >
                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-cyan-100 text-[11px]">
                  {{ getInitial(getApplicantName(app)) }}
                </span>
                <span class="truncate">
                  {{ getApplicantName(app) }}
                </span>
              </button>
            </div>

            <div class="mt-4 max-h-80 space-y-2 overflow-auto pr-1">
              <div
                v-if="unassignedApplications.length === 0"
                class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center text-sm font-bold text-slate-500"
              >
                No applicants currently waiting for interview scheduling.
              </div>

              <template v-else>
                <button
                  v-for="app in unassignedApplications"
                  :key="app.id"
                  type="button"
                  @click="toggleApplicantSelection(app)"
                  :class="isSelected(app.id) ? 'border-cyan-300 bg-cyan-50' : 'border-slate-200 bg-white hover:border-cyan-200 hover:bg-white'"
                  class="flex w-full items-center gap-4 rounded-2xl border p-4 text-left transition"
                >
                  <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-slate-900 text-sm font-black text-white">
                    {{ getInitial(getApplicantName(app)) }}
                  </span>
                  <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-black text-slate-900">
                      {{ getApplicantName(app) }}
                    </span>
                    <span class="mt-1 block truncate text-xs font-semibold text-slate-500">
                      {{ getApplicantDetails(app) }}
                    </span>
                  </span>
                  <span
                    :class="isSelected(app.id) ? 'bg-cyan-600 text-white' : 'bg-slate-100 text-slate-600'"
                    class="rounded-full px-3 py-1 text-xs font-black"
                  >
                    {{ isSelected(app.id) ? 'Selected' : 'Select' }}
                  </span>
                </button>
              </template>
            </div>
          </div>

          <div class="grid gap-5 lg:grid-cols-2">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Meet Link
              </label>
              <input
                v-model="scheduleForm.meet_link"
                type="url"
                placeholder="https://meet.google.com/..."
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Interviewer
              </label>
              <select
                v-model="scheduleForm.interviewer_id"
                required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              >
                <option value="">Select interviewer</option>
                <option
                  v-for="user in interviewerOptions"
                  :key="user.id"
                  :value="user.id"
                >
                  {{ user.name || `User #${user.id}` }} - {{ user.email }}
                </option>
              </select>
            </div>
          </div>

          <div class="grid gap-5 md:grid-cols-3">
            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Date
              </label>
              <input
                v-model="scheduleForm.date"
                type="date"
                required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                Start Time
              </label>
              <input
                v-model="scheduleForm.start_time"
                type="time"
                required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              />
            </div>

            <div>
              <label class="mb-2 block text-sm font-bold text-slate-800">
                End Time
              </label>
              <input
                v-model="scheduleForm.end_time"
                type="time"
                required
                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
              />
            </div>
          </div>

          <div>
            <label class="mb-2 block text-sm font-bold text-slate-800">
              Instructions
            </label>
            <textarea
              v-model="scheduleForm.instructions"
              rows="4"
              placeholder="Add reminders, requirements, or interview preparation notes"
              class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
            ></textarea>
          </div>

          <div class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700">
              <input
                v-model="scheduleForm.notify_beneficiaries"
                type="checkbox"
                class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500"
              />
              Notify beneficiaries
            </label>

            <button
              type="submit"
              :disabled="schedulingInterview || selectedApplications.length === 0"
              class="inline-flex items-center justify-center gap-3 rounded-xl bg-cyan-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-cyan-600/20 transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg
                v-if="schedulingInterview"
                class="h-5 w-5 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
              </svg>
              {{ schedulingInterview ? 'Scheduling Interview...' : 'Schedule Interview' }}
            </button>
          </div>
        </form>
      </div>

      <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-6 sm:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h3 class="text-2xl font-black text-slate-950">
                Upcoming Interviews
              </h3>
              <p class="mt-1 text-sm text-slate-500">
                Review schedules, meeting links, interviewers, and result actions.
              </p>
            </div>
            <span class="inline-flex w-fit items-center rounded-full bg-slate-900 px-4 py-2 text-sm font-black text-white">
              {{ upcomingInterviews.length }} records
            </span>
          </div>
        </div>

        <div class="max-h-[980px] space-y-4 overflow-auto p-6 sm:p-8">
          <div
            v-if="upcomingInterviews.length === 0"
            class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-8 py-20 text-center"
          >
            <p class="text-xl font-black text-slate-700">
              No interviews scheduled
            </p>
            <p class="mt-2 text-sm text-slate-500">
              New schedules will appear here after saving the scheduler form.
            </p>
          </div>

          <template v-else>
            <article
              v-for="interview in upcomingInterviews"
              :key="interview.id"
              class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md sm:p-6"
            >
              <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-cyan-600 text-xl font-black text-white shadow-sm">
                  {{ getInitial(getInterviewName(interview)) }}
                </div>

                <div class="min-w-0 flex-1">
                  <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="min-w-0">
                      <h4 class="truncate text-xl font-black text-slate-950">
                        {{ getInterviewName(interview) }}
                      </h4>
                      <p class="mt-1 truncate text-sm font-semibold text-slate-500">
                        {{ interview.job_title || 'No job title' }} / {{ interview.employer_name || 'No employer' }}
                      </p>
                      <p v-if="interview.batch_title" class="mt-2 text-xs font-black uppercase tracking-wide text-cyan-700">
                        {{ interview.batch_title }}
                      </p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                      <span
                        :class="statusClass(interview.status)"
                        class="rounded-full px-3 py-1.5 text-xs font-black uppercase ring-1"
                      >
                        {{ interview.status || 'N/A' }}
                      </span>
                      <span
                        v-if="interview.result"
                        :class="resultClass(interview.result)"
                        class="rounded-full px-3 py-1.5 text-xs font-black uppercase ring-1"
                      >
                        {{ interview.result }}
                      </span>
                    </div>
                  </div>

                  <div class="mt-5 grid gap-3 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                        Date and Time
                      </p>
                      <p class="mt-2 text-sm font-black text-slate-800">
                        {{ safeFormatDate(interview.scheduled_at || interview.start) || 'Not set' }}
                      </p>
                      <p class="mt-1 text-xs font-bold text-slate-500">
                        {{ getTimeRange(interview) }}
                      </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                      <p class="text-xs font-black uppercase tracking-wide text-slate-400">
                        Interviewer
                      </p>
                      <p class="mt-2 text-sm font-black text-slate-800">
                        {{ getInterviewerName(interview) }}
                      </p>
                      <p v-if="interview.rescheduled_at" class="mt-1 text-xs font-black text-amber-700">
                        Rescheduled
                      </p>
                    </div>
                  </div>

                  <p
                    v-if="interview.instructions"
                    class="mt-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold leading-6 text-slate-600"
                  >
                    {{ interview.instructions }}
                  </p>

                  <p
                    v-if="interview.reschedule_reason"
                    class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-800"
                  >
                    Reason: {{ interview.reschedule_reason }}
                  </p>

                  <div class="mt-5 flex flex-col gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:flex-wrap">
                    <a
                      v-if="interview.meet_link"
                      :href="interview.meet_link"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-black text-white transition hover:bg-slate-800"
                    >
                      Join Meet
                    </a>

                    <template v-if="interview.status === 'scheduled' && interview.result === 'pending'">
                      <button
                        type="button"
                        @click="updateInterviewResult(interview.id, 'passed')"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-emerald-700"
                      >
                        Pass Applicant
                      </button>
                      <button
                        type="button"
                        @click="updateInterviewResult(interview.id, 'failed')"
                        class="inline-flex items-center justify-center rounded-xl bg-red-600 px-4 py-2.5 text-sm font-black text-white transition hover:bg-red-700"
                      >
                        Fail Applicant
                      </button>
                    </template>

                    <span
                      v-else
                      class="inline-flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm font-bold text-slate-500"
                    >
                      Result action unavailable
                    </span>
                  </div>
                </div>
              </div>
            </article>
          </template>
        </div>
      </div>
    </div>

    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <h3 class="text-2xl font-black text-slate-950">
            Calendar Overview
          </h3>
          <p class="mt-1 text-sm text-slate-500">
            A quick schedule board for upcoming CPESO interviews.
          </p>
        </div>
        <p class="text-sm font-bold text-slate-500">
          {{ scheduleOverview.length }} active schedule days
        </p>
      </div>

      <div
        v-if="scheduleOverview.length === 0"
        class="mt-6 rounded-3xl border border-dashed border-slate-300 bg-slate-50 px-8 py-12 text-center text-sm font-bold text-slate-500"
      >
        No calendar items to display.
      </div>

      <div
        v-else
        class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
      >
        <div
          v-for="day in scheduleOverview"
          :key="day.key"
          class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
        >
          <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl bg-white text-center shadow-sm">
              <span class="text-xs font-black uppercase text-cyan-700">
                {{ getMonthLabel(day.date) }}
              </span>
              <span class="text-2xl font-black text-slate-950">
                {{ getDayNumber(day.date) }}
              </span>
            </div>
            <div>
              <p class="text-base font-black text-slate-950">
                {{ day.label }}
              </p>
              <p class="mt-1 text-sm font-semibold text-slate-500">
                {{ day.interviews.length }} interview{{ day.interviews.length === 1 ? '' : 's' }}
              </p>
            </div>
          </div>

          <div class="mt-4 space-y-3">
            <div
              v-for="interview in day.interviews.slice(0, 3)"
              :key="interview.id"
              class="rounded-xl bg-white p-3 shadow-sm"
            >
              <p class="truncate text-sm font-black text-slate-800">
                {{ getInterviewName(interview) }}
              </p>
              <p class="mt-1 text-xs font-semibold text-slate-500">
                {{ getTimeRange(interview) }} / {{ getInterviewerName(interview) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
