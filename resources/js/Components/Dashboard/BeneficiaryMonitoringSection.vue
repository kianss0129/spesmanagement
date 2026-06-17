<template>
  <section v-if="selectedTab === 'beneficiaryMonitoring'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Records & Monitoring</p>
      <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Beneficiaries</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Monitor approved beneficiaries, job placement, attendance, and completion status.
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
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-col gap-3 sm:flex-row">
          <input
            v-model="search"
            type="search"
            placeholder="Search beneficiary, employer, or job..."
            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none sm:w-80"
          >
          <select
            v-model="statusFilter"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
          >
            <option value="all">All records</option>
            <option value="assigned">Assigned</option>
            <option value="unassigned">Unassigned</option>
            <option value="completed">Completed</option>
          </select>
        </div>
        <p class="text-sm text-slate-500">Showing {{ filteredBeneficiaries.length }} of {{ beneficiaries.length }} beneficiaries</p>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1.2fr_0.8fr_1fr_1.2fr_1fr_1fr_1.1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Beneficiary Name</span>
        <span>Category</span>
        <span>Application Status</span>
        <span>Assigned Employer / Job</span>
        <span>Attendance Status</span>
        <span>Completion Status</span>
        <span>Action</span>
      </div>

      <div
        v-for="beneficiary in filteredBeneficiaries"
        :key="beneficiary.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.2fr_0.8fr_1fr_1.2fr_1fr_1fr_1.1fr] xl:items-center"
      >
        <div>
          <p class="font-semibold text-slate-900">{{ beneficiaryName(beneficiary) }}</p>
          <p class="mt-1 text-xs text-slate-500">ID #{{ beneficiary.id }}</p>
        </div>

        <p class="text-sm capitalize text-slate-700">{{ beneficiaryCategory(beneficiary) }}</p>

        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(applicationStatus(beneficiary))">
          {{ applicationStatus(beneficiary) }}
        </span>

        <div class="text-sm text-slate-700">
          <p class="font-semibold">{{ assignedEmployer(beneficiary) }}</p>
          <p class="mt-1 text-xs text-slate-500">{{ assignedJob(beneficiary) }}</p>
        </div>

        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(attendanceStatus(beneficiary))">
          {{ attendanceStatus(beneficiary) }}
        </span>

        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(completionStatus(beneficiary))">
          {{ completionStatus(beneficiary) }}
        </span>

        <div class="flex flex-wrap gap-2">
          <button
            type="button"
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
            @click="viewBeneficiaryApplications(beneficiary.id)"
          >
            View
          </button>
          <a
            v-if="!isPesoUser"
            href="/admin/assignment"
            class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
          >
            Assign
          </a>
          <button
            type="button"
            class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            @click="viewBeneficiaryApplications(beneficiary.id)"
          >
            Monitor
          </button>
        </div>
      </div>

      <div v-if="filteredBeneficiaries.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No beneficiary records found.</p>
        <p class="mt-1 text-sm text-slate-500">Try adjusting the search or filter.</p>
      </div>
    </section>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  selectedTab: String,
  beneficiaries: {
    type: Array,
    default: () => [],
  },
  isPesoUser: Boolean,
  statusClass: Function,
  viewBeneficiaryApplications: {
    type: Function,
    default: () => {},
  },
})

const search = ref('')
const statusFilter = ref('all')

const beneficiaries = computed(() => props.beneficiaries || [])

const filteredBeneficiaries = computed(() => {
  const term = search.value.trim().toLowerCase()

  return beneficiaries.value.filter((beneficiary) => {
    const haystack = [
      beneficiaryName(beneficiary),
      beneficiaryCategory(beneficiary),
      applicationStatus(beneficiary),
      assignedEmployer(beneficiary),
      assignedJob(beneficiary),
      attendanceStatus(beneficiary),
      completionStatus(beneficiary),
    ].join(' ').toLowerCase()

    const matchesSearch = !term || haystack.includes(term)
    const matchesFilter =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'assigned' && isAssigned(beneficiary)) ||
      (statusFilter.value === 'unassigned' && !isAssigned(beneficiary)) ||
      (statusFilter.value === 'completed' && isCompleted(beneficiary))

    return matchesSearch && matchesFilter
  })
})

const summaryCards = computed(() => {
  const assigned = beneficiaries.value.filter(isAssigned).length
  const completed = beneficiaries.value.filter(isCompleted).length

  return [
    { label: 'Total', value: beneficiaries.value.length, description: 'Approved beneficiary records.' },
    { label: 'Assigned', value: assigned, description: 'Beneficiaries with employer or job placement.' },
    { label: 'Unassigned', value: beneficiaries.value.length - assigned, description: 'Beneficiaries still awaiting placement.' },
    { label: 'Completed', value: completed, description: 'Beneficiaries marked completed.' },
  ]
})

function beneficiaryName(beneficiary) {
  return beneficiary.name || [beneficiary.first_name, beneficiary.last_name].filter(Boolean).join(' ') || 'Unnamed beneficiary'
}

function beneficiaryCategory(beneficiary) {
  return beneficiary.category || beneficiary.beneficiary_type || beneficiary.user?.beneficiary_type || 'Student'
}

function applicationStatus(beneficiary) {
  return formatLabel(beneficiary.application_status || beneficiary.approval_status || beneficiary.status || 'Approved')
}

function assignedEmployer(beneficiary) {
  return beneficiary.assigned_employer || beneficiary.employer_name || beneficiary.employer?.company_name || 'Unassigned'
}

function assignedJob(beneficiary) {
  return beneficiary.job_title || beneficiary.job?.title || beneficiary.position || 'No job assigned'
}

function attendanceStatus(beneficiary) {
  return formatLabel(beneficiary.attendance_status || beneficiary.dtr_status || (isAssigned(beneficiary) ? 'For monitoring' : 'Not started'))
}

function completionStatus(beneficiary) {
  return isCompleted(beneficiary) ? 'Completed' : formatLabel(beneficiary.completion_status || beneficiary.employment_status || 'In Progress')
}

function isAssigned(beneficiary) {
  return Boolean(beneficiary.assigned_employer || beneficiary.employer_name || beneficiary.employer || beneficiary.job_title || beneficiary.job)
}

function isCompleted(beneficiary) {
  return [beneficiary.completion_status, beneficiary.employment_status, beneficiary.status, beneficiary.application_status]
    .some((value) => String(value || '').toLowerCase().includes('completed'))
}

function formatLabel(value) {
  const text = String(value || '').replace(/_/g, ' ').trim()
  return text ? text.replace(/\b\w/g, (char) => char.toUpperCase()) : 'Not Available'
}

function badgeClass(value) {
  const status = String(value || '').toLowerCase()
  if (status.includes('completed') || status.includes('approved') || status.includes('active')) return 'bg-green-100 text-green-800'
  if (status.includes('unassigned') || status.includes('pending') || status.includes('progress') || status.includes('monitoring')) return 'bg-amber-100 text-amber-800'
  if (status.includes('reject') || status.includes('missing')) return 'bg-red-100 text-red-800'
  return 'bg-slate-100 text-slate-700'
}
</script>
