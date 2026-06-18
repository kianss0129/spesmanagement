<script setup>
import { computed } from 'vue'

const props = defineProps({
  selectedTab: String,
  sortedApplications: {
    type: Array,
    default: () => [],
  },
  contracts: {
    type: Array,
    default: () => [],
  },
  contractForm: Object,
  schedulingContract: Boolean,
  isAssignedApplication: Function,
  selectApplicant: Function,
  getSelectedApplicantName: Function,
  clearApplicantSelection: Function,
  scheduleContract: Function,
  updateContractResult: Function,
  formatDate: Function,
})

const selectedIds = computed(() => props.contractForm?.application_ids || [])
const assignedApplications = computed(() => {
  return (props.sortedApplications || []).filter((app) => {
    if (typeof props.isAssignedApplication === 'function' && !props.isAssignedApplication(app)) {
      return false
    }

    return isReadyForContractScheduling(app)
  })
})
const unassignedApplications = computed(() => (props.sortedApplications || []).filter((app) => !props.isAssignedApplication(app)))

function isSelected(id) {
  return selectedIds.value.some((item) => Number(item) === Number(id))
}

function normalizedStatus(value) {
  return String(value || '').toLowerCase().trim()
}

function isReadyForContractScheduling(app) {
  const status = normalizedStatus(app?.status)
  const contractStatus = normalizedStatus(app?.contract_status || app?.contract?.status)
  const contractResult = normalizedStatus(app?.contract_result || app?.contract?.result)
  const terminalStatuses = [
    'for_contract',
    'contract',
    'contract_signed',
    'signed',
    'deployed',
    'ongoing',
    'completion_review',
    'completed',
    'rejected',
    'failed',
    'cancelled',
  ]

  if (terminalStatuses.includes(status)) return false
  if (['scheduled', 'rescheduled', 'completed'].includes(contractStatus)) return false
  if (['signed', 'not_signed'].includes(contractResult)) return false

  return status === 'assigned' || status === 'job_placement' || status === 'jobplacement'
}
</script>

<template>
  <div v-if="selectedTab === 'contract'" class="space-y-6">
    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Face-to-face / Venue</p>
      <h2 class="mt-2 text-xl font-bold text-slate-900">Contract Signing</h2>
      <p class="mt-1 text-sm text-slate-500">Schedule contract signing for one beneficiary or a whole batch.</p>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <form @submit.prevent="scheduleContract" class="space-y-6">
        <div class="grid gap-4 lg:grid-cols-2">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Batch Title</label>
            <input
              v-model="contractForm.batch_title"
              type="text"
              placeholder="Example: Contract Batch A"
              class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"
            >
          </div>
          <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="font-bold">Contract signing is face-to-face.</span>
            Physical venue is required.
          </div>
        </div>

        <div>
          <div class="mb-3 flex items-center justify-between gap-3">
            <h3 class="text-sm font-bold text-slate-800">Selected Beneficiaries</h3>
            <button
              type="button"
              class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-700 hover:bg-red-100"
              @click="clearApplicantSelection"
            >
              Clear
            </button>
          </div>
          <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-800">
            {{ selectedIds.length ? getSelectedApplicantName() : 'No beneficiaries selected' }}
          </div>
        </div>

        <div>
          <div class="rounded-lg border border-slate-200">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
              <h3 class="text-sm font-bold text-slate-800">Assigned Beneficiaries</h3>
            </div>
            <div class="max-h-80 divide-y divide-slate-100 overflow-auto">
              <button
                v-for="app in assignedApplications"
                :key="app.id"
                type="button"
                class="flex w-full items-start justify-between gap-3 px-4 py-3 text-left text-sm hover:bg-slate-50"
                @click="selectApplicant(app.id)"
              >
                <span>
                  <span class="block font-semibold text-slate-900">{{ app.beneficiary_name }}</span>
                  <span class="mt-1 block text-xs text-slate-500">{{ app.job_title || 'N/A' }} / {{ app.employer_name || 'N/A' }}</span>
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-bold" :class="isSelected(app.id) ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-600'">
                  {{ isSelected(app.id) ? 'Selected' : 'Select' }}
                </span>
              </button>
              <p v-if="assignedApplications.length === 0" class="px-4 py-8 text-center text-sm text-slate-500">No assigned beneficiaries.</p>
            </div>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Date</label>
            <input v-model="contractForm.date" type="date" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Start Time</label>
            <input v-model="contractForm.start_time" type="time" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">End Time</label>
            <input v-model="contractForm.end_time" type="time" required class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          </div>
          <div>
            <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Physical Venue</label>
            <input v-model="contractForm.location" type="text" required placeholder="City Hall / CPESO Office" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none">
          </div>
        </div>

        <div>
          <label class="mb-1 block text-xs font-bold uppercase tracking-wide text-slate-500">Instructions</label>
          <textarea v-model="contractForm.instructions" rows="3" placeholder="Bring valid ID and required documents" class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none"></textarea>
        </div>

        <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700">
          <input v-model="contractForm.notify_beneficiaries" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
          Notify beneficiaries
        </label>

        <button
          type="submit"
          :disabled="schedulingContract || selectedIds.length === 0"
          class="w-full rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
        >
          {{ schedulingContract ? 'Scheduling...' : 'Schedule Contract Signing' }}
        </button>
      </form>
    </section>

    <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
      <h3 class="text-lg font-semibold text-slate-900">Upcoming Contracts</h3>
      <div v-if="contracts.length === 0" class="mt-4 text-sm text-slate-500">No upcoming contracts.</div>
      <div v-else class="mt-4 grid gap-3 md:grid-cols-2">
        <article v-for="contract in contracts" :key="contract.id" class="rounded-lg border border-slate-200 p-4">
          <p class="font-semibold text-slate-900">{{ contract.beneficiary_name || 'Applicant #' + contract.application_id }}</p>
          <p v-if="contract.batch_title" class="mt-1 text-xs font-bold uppercase tracking-wide text-blue-600">{{ contract.batch_title }}</p>
          <p class="mt-2 text-sm text-slate-600">{{ formatDate(contract.contract_date) }}<span v-if="contract.end_at"> - {{ formatDate(contract.end_at) }}</span></p>
          <p class="mt-1 text-sm text-slate-600">FACE-TO-FACE / Venue: {{ contract.location || 'TBA' }}</p>
          <p v-if="contract.instructions" class="mt-2 text-sm text-slate-600">{{ contract.instructions }}</p>
          <p v-if="contract.rescheduled_at" class="mt-2 text-xs font-bold text-amber-700">Rescheduled</p>
          <div v-if="contract.status === 'scheduled' || contract.status === 'rescheduled'" class="mt-3 flex gap-2">
            <button @click="updateContractResult(contract.id, 'signed')" class="rounded bg-green-600 px-3 py-1 text-xs font-bold text-white hover:bg-green-700">Signed</button>
            <button @click="updateContractResult(contract.id, 'not_signed')" class="rounded bg-red-600 px-3 py-1 text-xs font-bold text-white hover:bg-red-700">Not Signed</button>
          </div>
        </article>
      </div>
    </section>
  </div>
</template>
