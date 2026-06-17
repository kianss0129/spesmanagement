<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'

const { props } = usePage()

const applications = ref(props.applications || {})
const selectedProfile = ref(null)
const ratingTarget = ref(null)
const submittingRating = ref(false)
const toast = ref({ show: false, message: '', type: 'success' })

const ratingForm = ref({
  punctuality: 5,
  work_quality: 5,
  work_attitude: 5,
  communication: 5,
  overall: 5,
  comment: '',
})

const groupedApplications = computed(() => applications.value || {})
const allApplications = computed(() => Object.values(groupedApplications.value).flat())
// Only count beneficiaries who have signed their contract or are actively working
const assignedApplications = computed(() =>
  allApplications.value.filter(app => !['for_contract', 'contract_signing'].includes(app.status))
)
const totalAssigned = computed(() => assignedApplications.value.length)
const pendingAcknowledgement = computed(() =>
  assignedApplications.value.filter(app => app.status === 'contract_signed').length
)
const activeCount = computed(() =>
  assignedApplications.value.filter(app => ['deployed', 'ongoing'].includes(app.status)).length
)
const completionReviewCount = computed(() =>
  assignedApplications.value.filter(app => app.status === 'completion_review').length
)

function goBack() {
  window.history.back()
}

function beneficiaryName(beneficiary) {
  return [
    beneficiary?.first_name,
    beneficiary?.middle_name,
    beneficiary?.last_name,
  ].filter(Boolean).join(' ') || beneficiary?.name || 'Beneficiary'
}

function formatDate(value) {
  if (!value) return 'Not recorded'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value
  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function statusLabel(status, app = null) {
  if (status === 'contract_signed') return 'Contract Signed'
  return String(status || 'assigned')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, char => char.toUpperCase())
}

function statusClass(status, app = null) {
  if (status === 'contract_signed') return 'bg-amber-100 text-amber-800'
  if (['deployed', 'ongoing'].includes(status)) return 'bg-blue-100 text-blue-800'
  if (status === 'completion_review') return 'bg-violet-100 text-violet-800'
  if (status === 'completed') return 'bg-green-100 text-green-800'
  if (status === 'rejected') return 'bg-red-100 text-red-800'
  return 'bg-slate-100 text-slate-700'
}

function openProfile(app) {
  selectedProfile.value = app.beneficiary || null
}

function closeProfile() {
  selectedProfile.value = null
}

function openRating(app) {
  ratingTarget.value = app
  ratingForm.value = {
    punctuality: app.ratings?.punctuality || 5,
    work_quality: app.ratings?.work_quality || 5,
    work_attitude: app.ratings?.work_attitude || 5,
    communication: app.ratings?.communication || 5,
    overall: app.ratings?.overall || 5,
    comment: app.ratings?.comment || '',
  }
}

function replaceApplication(updated) {
  Object.keys(applications.value).forEach((jobTitle) => {
    applications.value[jobTitle] = applications.value[jobTitle].map((app) => (
      app.id === updated.id ? { ...app, ...updated } : app
    ))
  })
}

function canAcknowledge(app) {
  return app?.status === 'contract_signed'
}

async function acknowledgeAssignment(app) {
  app.updating = true

  try {
    const response = await axios.post(`/employer/applications/${app.id}/acknowledge`)
    replaceApplication(response.data.application)
    showToast('Beneficiary deployed successfully. Work supervision may begin.')
  } catch (error) {
    showToast(error.response?.data?.message || 'Failed to deploy beneficiary.', 'error')
  } finally {
    app.updating = false
  }
}

function closeRating() {
  ratingTarget.value = null
  ratingForm.value.comment = ''
}

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3200)
}

async function submitRating() {
  if (!ratingTarget.value) return

  submittingRating.value = true

  try {
    await axios.post('/employer/ratings', {
      application_id: ratingTarget.value.id,
      beneficiary_id: ratingTarget.value.beneficiary_id,
      punctuality: Number(ratingForm.value.punctuality),
      work_quality: Number(ratingForm.value.work_quality),
      work_attitude: Number(ratingForm.value.work_attitude),
      communication: Number(ratingForm.value.communication),
      overall: Number(ratingForm.value.overall),
      comment: ratingForm.value.comment?.trim() || 'No comment',
    })

    showToast(`Rating submitted for ${beneficiaryName(ratingTarget.value.beneficiary)}`)
    closeRating()
  } catch (error) {
    showToast(error.response?.data?.message || 'Failed to submit rating.', 'error')
  } finally {
    submittingRating.value = false
  }
}
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            class="mb-4 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
            @click="goBack"
          >
            Back
          </button>
          <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Supervision Queue</p>
          <h1 class="mt-2 text-3xl font-bold text-slate-950">Assigned Beneficiaries</h1>
          <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
            Track beneficiaries assigned by CPESO, acknowledge work supervision, review DTRs and daily reports, then submit ratings.
          </p>
        </div>
      </header>

      <section class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Assigned</p>
          <p class="mt-2 text-3xl font-bold">{{ totalAssigned }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Ready for Deployment</p>
          <p class="mt-2 text-3xl font-bold text-amber-600">{{ pendingAcknowledgement }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Active Work</p>
          <p class="mt-2 text-3xl font-bold text-blue-600">{{ activeCount }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
          <p class="text-xs font-semibold uppercase text-slate-500">Completion Review</p>
          <p class="mt-2 text-3xl font-bold text-violet-600">{{ completionReviewCount }}</p>
        </div>
      </section>

      <section
        v-if="totalAssigned === 0"
        class="rounded-lg border border-slate-200 bg-white p-10 text-center shadow-sm"
      >
        <h2 class="text-xl font-bold text-slate-900">No assigned beneficiaries yet</h2>
        <p class="mt-2 text-sm text-slate-500">Assigned beneficiaries will appear after CPESO completes placement.</p>
      </section>

      <section v-for="(apps, jobTitle) in groupedApplications" :key="jobTitle" class="rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
          <h2 class="text-lg font-bold text-slate-900">{{ jobTitle }}</h2>
          <p class="mt-1 text-sm text-slate-500">{{ apps.length }} beneficiary{{ apps.length === 1 ? '' : 'ies' }} assigned</p>
        </div>

        <div class="hidden grid-cols-[1.2fr_0.8fr_0.9fr_1.2fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 lg:grid">
          <span>Beneficiary</span>
          <span>Status</span>
          <span>Assigned</span>
          <span>Actions</span>
        </div>

        <article
          v-for="app in apps"
          :key="app.id"
          class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 lg:grid-cols-[1.2fr_0.8fr_0.9fr_1.2fr] lg:items-center"
        >
          <div class="flex min-w-0 items-center gap-3">
            <img
              :src="app.beneficiary?.profile_photo_url || '/default-profile.png'"
              class="h-12 w-12 rounded-lg object-cover"
              alt=""
            >
            <div class="min-w-0">
              <p class="truncate font-semibold text-slate-900">{{ beneficiaryName(app.beneficiary) }}</p>
              <p class="truncate text-sm text-slate-500">{{ app.beneficiary?.email || 'No email' }}</p>
            </div>
          </div>

          <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(app.status, app)">
            {{ statusLabel(app.status, app) }}
          </span>

          <p class="text-sm text-slate-600">{{ formatDate(app.created_at) }}</p>

          <div class="flex flex-wrap gap-2">
            <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="openProfile(app)">
              View
            </button>
            <button
              v-if="canAcknowledge(app)"
              type="button"
              :disabled="app.updating"
              class="rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700 disabled:opacity-50"
              @click="acknowledgeAssignment(app)"
            >
              {{ app.updating ? 'Deploying...' : 'Deploy' }}
            </button>
            <a href="/employer/attendance" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
              DTR
            </a>
            <a href="/employer/work-outputs" class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100">
              Daily Reports
            </a>
            <button type="button" class="rounded-lg bg-slate-900 px-3 py-2 text-sm font-semibold text-white hover:bg-black" @click="openRating(app)">
              Rate
            </button>
          </div>
        </article>
      </section>
    </div>

    <div v-if="selectedProfile" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeProfile">
      <div class="max-h-[90vh] w-full max-w-2xl overflow-auto rounded-lg bg-white shadow-xl">
        <div class="flex items-start justify-between border-b border-slate-200 p-5">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Beneficiary Profile</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ beneficiaryName(selectedProfile) }}</h2>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeProfile">Close</button>
        </div>
        <dl class="grid gap-4 p-5 text-sm sm:grid-cols-2">
          <div class="rounded-lg bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Email</dt><dd class="mt-1 break-all text-slate-900">{{ selectedProfile.email || 'N/A' }}</dd></div>
          <div class="rounded-lg bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Phone</dt><dd class="mt-1 text-slate-900">{{ selectedProfile.phone || selectedProfile.contact_number || 'N/A' }}</dd></div>
          <div class="rounded-lg bg-slate-50 p-4"><dt class="font-semibold text-slate-500">Category</dt><dd class="mt-1 text-slate-900">{{ selectedProfile.category || selectedProfile.user?.beneficiary_type || 'N/A' }}</dd></div>
          <div class="rounded-lg bg-slate-50 p-4"><dt class="font-semibold text-slate-500">School / Background</dt><dd class="mt-1 text-slate-900">{{ selectedProfile.school_name || selectedProfile.last_school_attended || 'N/A' }}</dd></div>
        </dl>
      </div>
    </div>

    <div v-if="ratingTarget" class="fixed inset-0 z-50 flex items-center justify-end bg-black/50 p-4" @click.self="closeRating">
      <form class="max-h-[94vh] w-full max-w-lg overflow-auto rounded-lg bg-white p-5 shadow-2xl" @submit.prevent="submitRating">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Performance Rating</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ beneficiaryName(ratingTarget.beneficiary) }}</h2>
            <p class="mt-1 text-sm text-slate-500">Submit employer evaluation for CPESO completion review.</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeRating">Close</button>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <label v-for="field in ['punctuality','work_quality','work_attitude','communication','overall']" :key="field" class="block">
            <span class="text-sm font-semibold capitalize text-slate-700">{{ field.replace(/_/g, ' ') }}</span>
            <select v-model="ratingForm[field]" class="mt-2 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </label>
        </div>

        <label class="mt-5 block text-sm font-semibold text-slate-700">Remarks</label>
        <textarea v-model="ratingForm.comment" rows="4" class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none" placeholder="Summarize work attitude, reliability, and performance."></textarea>

        <button type="submit" :disabled="submittingRating" class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50">
          {{ submittingRating ? 'Submitting...' : 'Submit Rating' }}
        </button>
      </form>
    </div>

    <div
      v-if="toast.show"
      class="fixed bottom-6 right-6 z-50 rounded-lg px-5 py-3 text-sm font-semibold text-white shadow-xl"
      :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      {{ toast.message }}
    </div>
  </main>
</template>
