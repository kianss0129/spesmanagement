<template>
  <div class="flex min-h-screen bg-slate-100 text-slate-900">
    <Sidebar
      :is-open="isSidebarOpen"
      :selected-tab="selectedTab"
      :menu-items="menuItems"
      @toggle="toggleSidebar"
      @select="handleMenuClick"
      @logout="logout"
    />

    <main class="flex-1 overflow-auto">
      <Head title="PESO Interview Tasks" />

      <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 px-4 py-4 shadow-sm backdrop-blur sm:px-6">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">PESO Interviewer</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900 sm:text-3xl">Assigned Interview Tasks</h1>
            <p class="mt-1 text-sm text-slate-500">Open your assigned Google Meet interviews, review beneficiary details, and submit evaluations.</p>
          </div>

          <div class="flex items-center gap-3">
            <button
              type="button"
              class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
              @click="refreshInterviews"
            >
              Refresh
            </button>
            <div class="hidden text-right sm:block">
              <p class="text-sm font-semibold text-slate-900">{{ user?.name }}</p>
              <p class="text-xs text-slate-500">{{ user?.email }}</p>
            </div>
          </div>
        </div>
      </header>

      <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6">
        <section v-if="selectedTab === 'tasks'" class="space-y-6">
          <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <button
              v-for="card in summaryCards"
              :key="card.key"
              type="button"
              class="rounded-lg border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-blue-300 hover:shadow-md"
              :class="activeFilter === card.key ? 'border-blue-500 ring-2 ring-blue-100' : ''"
              @click="activeFilter = card.key"
            >
              <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
              <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
              <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
            </button>
          </section>

          <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
              <div>
                <h2 class="text-lg font-bold text-slate-900">{{ activeFilterLabel }}</h2>
                <p class="mt-1 text-sm text-slate-500">Only interviews assigned to you are shown here.</p>
              </div>
              <span class="w-fit rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold text-slate-700">
                {{ filteredInterviews.length }} tasks
              </span>
            </div>

            <div class="hidden grid-cols-[1fr_1fr_0.9fr_1.2fr_0.8fr_0.9fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
              <span>Beneficiary</span>
              <span>Schedule</span>
              <span>Mode</span>
              <span>Meet Link</span>
              <span>Status</span>
              <span>Action</span>
            </div>

            <article
              v-for="interview in filteredInterviews"
              :key="interview.id"
              class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1fr_1fr_0.9fr_1.2fr_0.8fr_0.9fr] xl:items-center"
            >
              <div>
                <p v-if="interview.batch_title" class="text-xs font-bold uppercase tracking-wide text-blue-600">{{ interview.batch_title }}</p>
                <p class="font-semibold text-slate-900">{{ interview.beneficiary_name }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ interview.job_title || 'SPES Interview' }}</p>
              </div>

              <p class="text-sm text-slate-700">
                {{ formatDateTime(interview.scheduled_at) }}
                <span v-if="interview.end_at" class="block text-xs text-slate-500">Until {{ formatDateTime(interview.end_at) }}</span>
              </p>

              <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Online / Google Meet</p>

              <a
                v-if="interview.meet_link"
                :href="interview.meet_link"
                target="_blank"
                rel="noopener noreferrer"
                class="break-words text-sm font-semibold text-blue-700 hover:text-blue-800"
              >
                Open Google Meet
              </a>
              <p v-else class="text-sm text-slate-500">No link posted</p>

              <div class="space-y-2">
                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(interview)">
                  {{ statusLabel(interview) }}
                </span>
                <p v-if="interview.evaluated_at" class="text-xs text-slate-500">Evaluated {{ formatDateTime(interview.evaluated_at) }}</p>
              </div>

              <button
                type="button"
                class="w-fit rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
                @click="openInterview(interview)"
              >
                Open Interview
              </button>
            </article>

            <div v-if="filteredInterviews.length === 0" class="px-5 py-12 text-center">
              <p class="text-sm font-semibold text-slate-700">No interview tasks found.</p>
              <p class="mt-1 text-sm text-slate-500">Assigned interviews from CPESO will appear here.</p>
            </div>
          </section>
        </section>

        <section v-if="selectedTab === 'announcements'" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">Announcements</h2>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <article v-for="announcement in announcements" :key="announcement.id" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
              <p class="font-semibold text-slate-900">{{ announcement.title }}</p>
              <p class="mt-2 text-sm leading-6 text-slate-700">{{ announcement.content }}</p>
              <p class="mt-3 text-xs text-slate-500">{{ formatDateTime(announcement.created_at) }}</p>
            </article>
          </div>
        </section>
      </div>
    </main>

    <div v-if="selectedInterview" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeInterview">
      <div class="max-h-[92vh] w-full max-w-5xl overflow-auto rounded-lg bg-white shadow-xl">
        <div class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white p-5">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Assigned Interview</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ selectedInterview.beneficiary_name }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ formatDateTime(selectedInterview.scheduled_at) }}</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeInterview">Close</button>
        </div>

        <div class="grid gap-6 p-5 lg:grid-cols-[1.05fr_0.95fr]">
          <section class="space-y-5">
            <div class="rounded-lg border border-slate-200 p-4">
              <h3 class="font-bold text-slate-900">Beneficiary Profile</h3>
              <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <InfoItem label="Name" :value="profile.name || selectedInterview.beneficiary_name" />
                <InfoItem label="Category" :value="formatCategory(profile.category)" />
                <InfoItem label="School" :value="profile.school" />
                <InfoItem label="Course" :value="profile.course" />
                <InfoItem label="Skills" :value="skillText" class="sm:col-span-2" />
                <InfoItem label="Application Status" :value="formatLabel(selectedInterview.application_status)" />
              </dl>
            </div>

            <div class="rounded-lg border border-slate-200 p-4">
              <h3 class="font-bold text-slate-900">Uploaded Requirements Summary</h3>
              <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Available</p>
                  <p class="mt-2 text-2xl font-bold text-slate-900">{{ profile.requirements_summary?.available || 0 }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-4">
                  <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Records</p>
                  <p class="mt-2 text-2xl font-bold text-slate-900">{{ profile.requirements_summary?.total || 0 }}</p>
                </div>
              </div>
            </div>

            <div class="rounded-lg border border-slate-200 p-4">
              <h3 class="font-bold text-slate-900">Interview Schedule</h3>
              <p class="mt-3 text-sm text-slate-700">{{ formatDateTime(selectedInterview.scheduled_at) }}<span v-if="selectedInterview.end_at"> - {{ formatDateTime(selectedInterview.end_at) }}</span></p>
              <a
                v-if="selectedInterview.meet_link"
                :href="selectedInterview.meet_link"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-4 inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700"
              >
                Conduct Google Meet Interview
              </a>
              <p v-if="selectedInterview.instructions" class="mt-3 rounded-lg bg-blue-50 px-3 py-2 text-sm text-blue-900">{{ selectedInterview.instructions }}</p>
            </div>
          </section>

          <form class="rounded-lg border border-slate-200 p-4" @submit.prevent="submitEvaluation">
            <h3 class="font-bold text-slate-900">Interview Evaluation</h3>
            <p class="mt-1 text-sm text-slate-500">PESO users submit evaluation only. CPESO still decides qualification and approval.</p>

            <div class="mt-5">
              <label class="text-sm font-semibold text-slate-700">Interview Result</label>
              <select v-model="evaluation.result" required class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
                <option value="">Select result</option>
                <option value="passed">Passed</option>
                <option value="failed">Failed</option>
                <option value="needs_review">Needs Review</option>
              </select>
            </div>

            <div class="mt-4">
              <label class="text-sm font-semibold text-slate-700">Remarks</label>
              <textarea v-model="evaluation.remarks" rows="7" required class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none" placeholder="Record interview evaluation, concerns, strengths, and any CPESO review notes."></textarea>
            </div>

            <div class="mt-4 rounded-lg bg-slate-50 p-4">
              <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Evaluation Date</p>
              <p class="mt-1 text-sm font-semibold text-slate-900">{{ formatDateTime(new Date().toISOString()) }}</p>
            </div>

            <p v-if="selectedInterview.remarks" class="mt-4 rounded-lg bg-amber-50 px-3 py-2 text-sm text-amber-900">
              Previous remarks: {{ selectedInterview.remarks }}
            </p>

            <button
              type="submit"
              class="mt-5 w-full rounded-lg bg-green-600 px-4 py-3 text-sm font-semibold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-60"
              :disabled="submittingEvaluation || !evaluation.result || !evaluation.remarks.trim()"
            >
              {{ submittingEvaluation ? 'Submitting...' : 'Submit Interview Evaluation' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, defineComponent, h, ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Sidebar from '@/Components/Sidebar.vue'

const props = defineProps({
  user: Object,
  stats: {
    type: Object,
    default: () => ({}),
  },
  assignedInterviews: {
    type: Array,
    default: () => [],
  },
  announcements: {
    type: Array,
    default: () => [],
  },
})

const InfoItem = defineComponent({
  props: {
    label: { type: String, required: true },
    value: { default: null },
  },
  setup(itemProps) {
    return () => h('div', {}, [
      h('dt', { class: 'text-xs font-semibold uppercase tracking-wide text-slate-500' }, itemProps.label),
      h('dd', { class: 'mt-1 break-words font-semibold text-slate-900' }, displayValue(itemProps.value)),
    ])
  },
})

const user = computed(() => props.user || {})
const interviews = ref([...(props.assignedInterviews || [])])
const announcements = computed(() => props.announcements || [])
const selectedTab = ref('tasks')
const activeFilter = ref('assigned')
const isSidebarOpen = ref(true)
const selectedInterview = ref(null)
const submittingEvaluation = ref(false)
const evaluation = ref({
  result: '',
  remarks: '',
})

const menuItems = [
  { key: 'tasks', label: 'Assigned Interviews', shortLabel: 'AI' },
  { key: 'announcements', label: 'Announcements', shortLabel: 'AN' },
]

const summaryCards = computed(() => [
  {
    key: 'assigned',
    label: 'Assigned Interviews',
    value: interviews.value.length,
    description: 'All interviews assigned to you.',
  },
  {
    key: 'upcoming',
    label: 'Upcoming Interviews',
    value: interviews.value.filter((item) => item.status === 'scheduled').length,
    description: 'Scheduled interviews not yet evaluated.',
  },
  {
    key: 'completed',
    label: 'Completed Interviews',
    value: interviews.value.filter((item) => item.status === 'completed').length,
    description: 'Evaluations already submitted.',
  },
  {
    key: 'needs_review',
    label: 'Needs Review',
    value: interviews.value.filter((item) => item.result === 'needs_review').length,
    description: 'Evaluations escalated for CPESO review.',
  },
])

const filteredInterviews = computed(() => {
  if (activeFilter.value === 'upcoming') return interviews.value.filter((item) => item.status === 'scheduled')
  if (activeFilter.value === 'completed') return interviews.value.filter((item) => item.status === 'completed')
  if (activeFilter.value === 'needs_review') return interviews.value.filter((item) => item.result === 'needs_review')
  return interviews.value
})

const activeFilterLabel = computed(() => summaryCards.value.find((card) => card.key === activeFilter.value)?.label || 'Assigned Interviews')
const profile = computed(() => selectedInterview.value?.beneficiary_profile || {})
const skillText = computed(() => {
  const skills = profile.value.skills
  return Array.isArray(skills) && skills.length ? skills.join(', ') : 'Not specified'
})

function displayValue(value) {
  return value === null || value === undefined || value === '' ? 'Not provided' : value
}

function formatDateTime(value) {
  if (!value) return 'Not set'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function formatLabel(value) {
  return String(value || 'Pending').replace(/_/g, ' ').replace(/\b\w/g, (char) => char.toUpperCase())
}

function formatCategory(value) {
  const normalized = String(value || '').toLowerCase()
  if (normalized === 'osy' || normalized === 'out_of_school_youth') return 'Out-of-School Youth (OSY)'
  if (normalized === 'ddw' || normalized === 'dependent_of_displaced_worker') return 'Dependent of Displaced Worker (DDW)'
  if (normalized === 'student') return 'Student'
  return formatLabel(value || 'Student')
}

function statusLabel(interview) {
  if (interview.result === 'needs_review') return 'Needs Review'
  if (interview.status === 'completed') return `Completed / ${formatLabel(interview.result)}`
  return 'Scheduled'
}

function statusClass(interview) {
  if (interview.result === 'needs_review') return 'bg-amber-100 text-amber-800'
  if (interview.result === 'passed') return 'bg-green-100 text-green-800'
  if (interview.result === 'failed') return 'bg-red-100 text-red-800'
  return 'bg-blue-100 text-blue-800'
}

function openInterview(interview) {
  selectedInterview.value = interview
  evaluation.value = {
    result: interview.result && interview.result !== 'pending' ? interview.result : '',
    remarks: interview.remarks || '',
  }
}

function closeInterview() {
  selectedInterview.value = null
}

async function refreshInterviews() {
  const response = await axios.get('/peso-user/interviews')
  interviews.value = response.data || []
}

async function submitEvaluation() {
  if (!selectedInterview.value) return

  submittingEvaluation.value = true
  try {
    const response = await axios.post(`/peso-user/interviews/${selectedInterview.value.id}/evaluation`, {
      result: evaluation.value.result,
      remarks: evaluation.value.remarks,
    })

    const updated = response.data.interview
    if (updated) {
      interviews.value = interviews.value.map((item) => item.id === updated.id ? updated : item)
      selectedInterview.value = updated
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Unable to submit interview evaluation.')
  } finally {
    submittingEvaluation.value = false
  }
}

function logout() {
  router.post(route('logout'))
}

function toggleSidebar() {
  isSidebarOpen.value = !isSidebarOpen.value
}

function handleMenuClick(tab) {
  selectedTab.value = tab
}
</script>
