<template>
  <section v-if="selectedTab === 'approvedEmployers'" class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Employer Records</p>
      <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Employers</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Monitor approved employers, active job posts, assigned beneficiaries, and account status.
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
            placeholder="Search employer, contact, or email..."
            class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none sm:w-80"
          >
        </div>
        <p class="text-sm text-slate-500">Showing {{ filteredEmployers.length }} of {{ approvedEmployers.length }} employers</p>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="hidden grid-cols-[1.2fr_1fr_1.1fr_0.8fr_1fr_0.8fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Employer Name</span>
        <span>Contact Person</span>
        <span>Email</span>
        <span>Active Job Posts</span>
        <span>Assigned Beneficiaries</span>
        <span>Status</span>
        <span>Action</span>
      </div>

      <div
        v-for="employer in filteredEmployers"
        :key="employer.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.2fr_1fr_1.1fr_0.8fr_1fr_0.8fr_1fr] xl:items-center"
      >
        <div>
          <p class="font-semibold text-slate-900">{{ employerName(employer) }}</p>
          <p class="mt-1 text-xs text-slate-500">ID #{{ employer.id }}</p>
        </div>

        <p class="text-sm text-slate-700">{{ contactPerson(employer) }}</p>
        <p class="break-words text-sm text-slate-700">{{ employerEmail(employer) }}</p>
        <p class="text-sm font-semibold text-slate-900">{{ activeJobCount(employer) }}</p>
        <p class="text-sm font-semibold text-slate-900">{{ assignedBeneficiaryCount(employer) }}</p>

        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="badgeClass(employerStatus(employer))">
          {{ employerStatus(employer) }}
        </span>

        <div class="flex flex-wrap gap-2">
          <a
            :href="`/peso/employers/${employer.id}/applications`"
            class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
          >
            View Record
          </a>
        </div>
      </div>

      <div v-if="filteredEmployers.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No employer records found.</p>
        <p class="mt-1 text-sm text-slate-500">Try adjusting the search or filter.</p>
      </div>
    </section>
  </section>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  selectedTab: String,
  approvedEmployers: {
    type: Array,
    default: () => [],
  },
  isAdminRole: {
    type: Boolean,
    default: false,
  },
  formatDate: {
    type: Function,
    default: (date) => date,
  },
})

defineEmits(['revert', 'manage-jobs'])

const search = ref('')
const statusFilter = ref('all')

const approvedEmployers = computed(() => props.approvedEmployers || [])

const filteredEmployers = computed(() => {
  const term = search.value.trim().toLowerCase()

  return approvedEmployers.value.filter((employer) => {
    const haystack = [
      employerName(employer),
      contactPerson(employer),
      employerEmail(employer),
      employerStatus(employer),
    ].join(' ').toLowerCase()

    const matchesSearch = !term || haystack.includes(term)
    const matchesFilter =
      statusFilter.value === 'all' ||
      (statusFilter.value === 'active' && isActive(employer)) ||
      (statusFilter.value === 'pending_jobs' && pendingJobCount(employer) > 0) ||
      (statusFilter.value === 'assigned' && assignedBeneficiaryCount(employer) > 0)

    return matchesSearch && matchesFilter
  })
})

const summaryCards = computed(() => {
  return [
    { label: 'Total', value: approvedEmployers.value.length, description: 'Approved employer records.' },
    { label: 'Active', value: approvedEmployers.value.filter(isActive).length, description: 'Employers currently active.' },
    { label: 'Assigned Beneficiaries', value: approvedEmployers.value.reduce((sum, employer) => sum + assignedBeneficiaryCount(employer), 0), description: 'Beneficiaries linked to employers.' },
  ]
})

function employerName(employer) {
  return employer.company_name || employer.name || employer.user?.name || 'Unknown employer'
}

function employerEmail(employer) {
  return employer.email || employer.user?.email || employer.company_email || 'No email'
}

function contactPerson(employer) {
  const contact = employer.details?.contact_person || employer.contact_person || {}
  return [contact.first_name, contact.middle_name, contact.last_name].filter(Boolean).join(' ') ||
    employer.contact_person_name ||
    employer.contact_person ||
    'Not provided'
}

function activeJobCount(employer) {
  return Number(employer.active_job_posts ?? employer.active_jobs_count ?? employer.job_listings_count ?? employer.jobs_count ?? 0)
}

function pendingJobCount(employer) {
  return Number(employer.pending_jobs_count ?? employer.pending_job_posts ?? employer.pending_jobs ?? 0)
}

function assignedBeneficiaryCount(employer) {
  return Number(employer.assigned_beneficiaries_count ?? employer.assigned_beneficiaries ?? employer.beneficiaries_count ?? employer.hires ?? 0)
}

function employerStatus(employer) {
  return formatLabel(employer.approval_status || employer.status || (employer.approved === false ? 'Pending' : 'Active'))
}

function isActive(employer) {
  const status = employerStatus(employer).toLowerCase()
  return status.includes('active') || status.includes('approved')
}

function formatLabel(value) {
  const text = String(value || '').replace(/_/g, ' ').trim()
  return text ? text.replace(/\b\w/g, (char) => char.toUpperCase()) : 'Not Available'
}

function badgeClass(value) {
  const status = String(value || '').toLowerCase()
  if (status.includes('active') || status.includes('approved')) return 'bg-green-100 text-green-800'
  if (status.includes('pending')) return 'bg-amber-100 text-amber-800'
  if (status.includes('reject') || status.includes('inactive')) return 'bg-red-100 text-red-800'
  return 'bg-slate-100 text-slate-700'
}
</script>
