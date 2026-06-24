<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  jobs: {
    type: Array,
    default: () => [],
  },
})

const showModal = ref(false)
const jobToDelete = ref(null)
const showToast = ref(false)
const toastMessage = ref('')

const activeJobs = computed(() => props.jobs || [])
const totalSlots = computed(() => activeJobs.value.reduce((sum, job) => sum + Number(job.slots || 0), 0))

function goBack() {
  router.visit('/dashboard')
}

function openModal(job) {
  jobToDelete.value = job
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  jobToDelete.value = null
}

function deleteJob() {
  if (!jobToDelete.value) return

  router.delete(`/employer/jobs/${jobToDelete.value.id}`, {
    onSuccess: () => {
      toastMessage.value = `Job "${jobToDelete.value.title}" deleted.`
      showToast.value = true
      closeModal()
      setTimeout(() => { showToast.value = false }, 3000)
    },
  })
}

function formatDate(value) {
  if (!value) return 'No closing date'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: '2-digit' })
}
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
          <button type="button" class="mb-4 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" @click="goBack">
            Back
          </button>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Work Opportunities</p>
          <h1 class="mt-2 text-3xl font-bold text-slate-950">Job Slots</h1>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Maintain job slots that CPESO can use for matching and placement.
          </p>
        </div>
        <a href="/employer/jobs/create" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-blue-700">
          New Job Slot
        </a>
      </header>

      <section class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Available Jobs</p>
          <p class="mt-2 text-3xl font-bold">{{ activeJobs.length }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Total Slots</p>
          <p class="mt-2 text-3xl font-bold text-blue-600">{{ totalSlots }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Workflow</p>
          <p class="mt-2 text-sm font-semibold text-slate-700">Post slots for CPESO matching</p>
        </div>
      </section>

      <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="hidden grid-cols-[0.8fr_1.4fr_1fr_0.6fr_0.9fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:grid">
          <span>ID</span>
          <span>Job Title</span>
          <span>Location</span>
          <span>Slots</span>
          <span>Closing Date</span>
          <span>Actions</span>
        </div>

        <div v-if="activeJobs.length === 0" class="p-10 text-center">
          <h2 class="text-lg font-bold text-slate-900">No job slots yet</h2>
          <p class="mt-2 text-sm text-slate-500">Create a job slot so CPESO can match beneficiaries to your organization.</p>
        </div>

        <article
          v-for="job in activeJobs"
          :key="job.id"
          class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 lg:grid-cols-[0.8fr_1.4fr_1fr_0.6fr_0.9fr_1fr] lg:items-center"
        >
          <p class="text-sm font-semibold text-slate-500">#{{ job.id }}</p>
          <div>
            <p class="font-semibold text-slate-900">{{ job.title }}</p>
            <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ job.description }}</p>
          </div>
          <p class="text-sm text-slate-700">{{ job.location || 'No location' }}</p>
          <p class="text-sm font-semibold text-slate-900">{{ job.slots || 0 }}</p>
          <p class="text-sm text-slate-700">{{ formatDate(job.closing_date) }}</p>
          <div class="flex flex-wrap gap-2">
            <a :href="`/employer/jobs/${job.id}/edit`" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
              Edit
            </a>
            <button type="button" class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="openModal(job)">
              Delete
            </button>
          </div>
        </article>
      </section>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h2 class="text-xl font-bold text-slate-900">Delete Job Slot</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          Delete <span class="font-semibold">{{ jobToDelete?.title }}</span>? This only removes this job post from employer management.
        </p>
        <div class="mt-6 flex justify-end gap-3">
          <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="closeModal">Cancel</button>
          <button type="button" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="deleteJob">Delete</button>
        </div>
      </div>
    </div>

    <div v-if="showToast" class="fixed bottom-6 right-6 rounded-lg bg-green-600 px-5 py-3 text-sm font-semibold text-white shadow-xl">
      {{ toastMessage }}
    </div>
  </main>
</template>
