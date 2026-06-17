<template>
  <div class="min-h-screen bg-slate-100 p-4 sm:p-6">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <button
          type="button"
          class="mb-4 inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          @click="goBack"
        >
          Back
        </button>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Review Center</p>
        <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Employer Applications</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Review employer submissions, verify company details, and approve or reject SPES employer participation.
        </p>
      </header>

      <section class="grid gap-4 sm:grid-cols-3">
        <div
          v-for="card in summaryCards"
          :key="card.label"
          class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"
        >
          <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
          <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
          <p class="mt-2 text-xs text-slate-500">{{ card.description }}</p>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[1.2fr_1.1fr_1fr_1fr_0.8fr_1.3fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:grid">
          <span>Employer</span>
          <span>Email</span>
          <span>Contact Person</span>
          <span>Submitted</span>
          <span>Status</span>
          <span>Actions</span>
        </div>

        <div
          v-for="employer in employers"
          :key="employer.id"
          class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 lg:grid-cols-[1.2fr_1.1fr_1fr_1fr_0.8fr_1.3fr] lg:items-center"
        >
          <div>
            <p class="font-semibold text-slate-900">{{ employer.company_name || employer.user?.name || 'Unnamed employer' }}</p>
            <p class="mt-1 text-xs text-slate-500 lg:hidden">{{ employer.user?.email || 'No email' }}</p>
          </div>

          <p class="hidden break-words text-sm text-slate-600 lg:block">{{ employer.user?.email || employer.email || 'No email' }}</p>
          <p class="text-sm text-slate-700">{{ contactPerson(employer) }}</p>
          <p class="text-sm text-slate-600">{{ formatDate(employer.created_at || employer.submitted_at) }}</p>

          <span
            class="w-fit rounded-full px-3 py-1 text-xs font-semibold"
            :class="statusClass(employer.approval_status || employer.status)"
          >
            {{ formatStatus(employer.approval_status || employer.status) }}
          </span>

          <div class="flex flex-wrap gap-2">
            <Link
              :href="route('peso.employers.applications', { employer: employer.id })"
              class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100"
            >
              Review
            </Link>

            <button
              v-if="canApprove"
              type="button"
              class="rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm font-semibold text-green-700 hover:bg-green-100"
              @click="openApproveDialog(employer.id)"
            >
              Approve
            </button>

            <button
              v-if="canApprove"
              type="button"
              class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100"
              @click="openRejectDialog(employer.id)"
            >
              Reject
            </button>
          </div>
        </div>

        <div v-if="employers.length === 0" class="px-5 py-12 text-center">
          <p class="text-sm font-semibold text-slate-700">No employer applications found.</p>
          <p class="mt-1 text-sm text-slate-500">New employer applications will appear here for CPESO review.</p>
        </div>
      </section>
    </div>

    <div v-if="showApproveDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Approve Employer</h3>
        <p class="mt-2 text-sm text-slate-600">Approve this employer for SPES participation.</p>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showApproveDialog = false">Cancel</button>
          <button class="rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700" @click="confirmApprove">Approve</button>
        </div>
      </div>
    </div>

    <div v-if="showRejectDialog" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
      <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">Reject Employer</h3>
        <p class="mt-2 text-sm text-slate-600">Enter the reason for rejecting this employer application.</p>
        <textarea
          v-model="rejectionReason"
          class="mt-4 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none"
          rows="4"
          placeholder="Reason for rejection..."
        ></textarea>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="showRejectDialog = false">Cancel</button>
          <button
            class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-50"
            :disabled="!rejectionReason.trim()"
            @click="confirmReject"
          >
            Reject
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  employers: {
    type: Array,
    required: true,
  },
  canApprove: {
    type: Boolean,
    default: false,
  },
})

const employers = computed(() => props.employers || [])
const showApproveDialog = ref(false)
const showRejectDialog = ref(false)
const rejectionReason = ref('')
const selectedId = ref(null)

const summaryCards = computed(() => {
  const pending = employers.value.filter((item) => statusValue(item) === 'pending').length
  const approved = employers.value.filter((item) => statusValue(item) === 'approved').length
  const rejected = employers.value.filter((item) => statusValue(item) === 'rejected').length

  return [
    { label: 'Pending Employers', value: pending, description: 'Employer applications awaiting review.' },
    { label: 'Approved Employers', value: approved, description: 'Approved employers in the current list.' },
    { label: 'Rejected Employers', value: rejected, description: 'Rejected employers in the current list.' },
  ]
})

function statusValue(employer) {
  if (employer.approved === true) return 'approved'
  return String(employer.approval_status || employer.status || 'pending').toLowerCase()
}

function contactPerson(employer) {
  const contact = employer.details?.contact_person || employer.contact_person || {}
  const name = [contact.first_name, contact.middle_name, contact.last_name].filter(Boolean).join(' ')
  return name || employer.contact_person_name || employer.user?.name || 'Not provided'
}

function formatStatus(status) {
  return {
    pending: 'Pending',
    approved: 'Approved',
    rejected: 'Rejected',
  }[String(status || 'pending').toLowerCase()] || status
}

function statusClass(status) {
  return {
    pending: 'bg-amber-100 text-amber-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  }[String(status || 'pending').toLowerCase()] || 'bg-slate-100 text-slate-700'
}

function formatDate(date) {
  if (!date) return 'Not submitted'
  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return date

  return parsed.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function goBack() {
  window.history.back()
}

function openApproveDialog(id) {
  selectedId.value = id
  showApproveDialog.value = true
}

function openRejectDialog(id) {
  selectedId.value = id
  rejectionReason.value = ''
  showRejectDialog.value = true
}

function confirmApprove() {
  router.post(route('peso.employers.approve', { id: selectedId.value }), {}, {
    onSuccess: () => {
      showApproveDialog.value = false
      router.reload()
    },
  })
}

function confirmReject() {
  router.post(route('peso.employers.reject', { id: selectedId.value }), { rejection_reason: rejectionReason.value }, {
    onSuccess: () => {
      showRejectDialog.value = false
      router.reload()
    },
  })
}
</script>
