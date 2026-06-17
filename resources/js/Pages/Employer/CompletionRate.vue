<template>
  <div class="min-h-screen bg-slate-100 px-4 py-6 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl space-y-6">
      <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
          <div class="flex items-start gap-4">
            <button
              type="button"
              class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50"
              @click="goBack"
            >
              Back
            </button>

            <div>
              <h1 class="text-3xl font-bold tracking-tight text-slate-950 sm:text-4xl">
                Completion Submission
              </h1>
              <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
                Review beneficiary readiness and submit completion for CPESO approval.
              </p>
            </div>
          </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="card in summaryCards"
            :key="card.label"
            class="rounded-xl border border-slate-200 bg-slate-50 p-5"
          >
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ card.label }}</p>
            <p class="mt-3 text-3xl font-bold text-slate-950">{{ card.value }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ card.description }}</p>
          </div>
        </div>
      </header>

      <section
        v-if="Object.keys(applications).length === 0"
        class="rounded-2xl border border-slate-200 bg-white p-10 text-center shadow-sm"
      >
        <h2 class="text-xl font-bold text-slate-900">No beneficiaries found</h2>
        <p class="mt-2 text-sm text-slate-500">Beneficiaries assigned to jobs will appear here.</p>
      </section>

      <section
        v-for="(apps, jobTitle) in applications"
        :key="jobTitle"
        class="space-y-4"
      >
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:flex-row lg:items-center lg:justify-between">
          <div>
            <h2 class="text-xl font-bold text-slate-950">{{ jobTitle }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ apps.length }} assigned beneficiary(s)</p>
          </div>
          <div class="flex flex-wrap gap-2 text-sm">
            <span class="rounded-full bg-emerald-50 px-3 py-1 font-semibold text-emerald-700">
              {{ apps.filter((app) => app.status === 'completed').length }} completed
            </span>
            <span class="rounded-full bg-blue-50 px-3 py-1 font-semibold text-blue-700">
              {{ apps.filter((app) => ['deployed', 'ongoing'].includes(app.status)).length }} active
            </span>
            <span class="rounded-full bg-violet-50 px-3 py-1 font-semibold text-violet-700">
              {{ apps.filter((app) => app.status === 'completion_review').length }} submitted
            </span>
          </div>
        </div>

        <article
          v-for="app in apps"
          :key="app.id"
          class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >
          <div class="grid gap-6 xl:grid-cols-[1.15fr_0.95fr]">
            <div class="space-y-5">
              <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                <div class="flex min-w-0 gap-4">
                  <img
                    :src="app.beneficiary?.profile_photo_url || '/default-profile.png'"
                    class="h-16 w-16 rounded-xl border border-slate-200 object-cover"
                  />
                  <div class="min-w-0">
                    <h3 class="text-xl font-bold text-slate-950">{{ beneficiaryName(app) }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ app.job_title || app.job?.title || jobTitle }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                      <span class="rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="statusColor(app.status)">
                        {{ displayStatus(app.status) }}
                      </span>
                      <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-700">
                        {{ assignmentStatus(app) }}
                      </span>
                    </div>
                  </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 md:text-right">
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Completion progress</p>
                  <p class="mt-1 text-2xl font-bold text-slate-950">{{ completionProgress(app) }}%</p>
                </div>
              </div>

              <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                <div
                  class="h-full rounded-full bg-emerald-500 transition-all"
                  :style="{ width: `${completionProgress(app)}%` }"
                ></div>
              </div>

              <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Application status</p>
                  <p class="mt-2 font-semibold capitalize text-slate-900">{{ displayStatus(app.status) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Assigned date</p>
                  <p class="mt-2 font-semibold text-slate-900">{{ formatDate(app.assigned_at || app.created_at) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Contact</p>
                  <p class="mt-2 font-semibold text-slate-900">{{ contactValue(app) }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                  <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Certificate</p>
                  <p class="mt-2 font-semibold text-slate-900">{{ app.certificate_path ? 'Uploaded' : 'Pending' }}</p>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white p-4">
                <div class="flex items-center justify-between gap-3">
                  <div>
                    <h4 class="font-bold text-slate-950">Readiness Checklist</h4>
                    <p class="mt-1 text-sm text-slate-500">Complete the items below before submission.</p>
                  </div>
                  <span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-bold text-slate-700">
                    {{ readyItemCount(app) }}/{{ readinessItems(app).length }}
                  </span>
                </div>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                  <div
                    v-for="item in readinessItems(app)"
                    :key="item.label"
                    class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 px-4 py-3"
                  >
                    <span class="text-sm font-semibold text-slate-700">{{ item.label }}</span>
                    <span
                      class="rounded-full px-3 py-1 text-xs font-bold"
                      :class="item.ready ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                    >
                      {{ item.ready ? 'Completed' : 'Pending' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <aside class="space-y-5">
              <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <h4 class="font-bold text-slate-950">Quick Actions</h4>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                  <button type="button" class="rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white hover:bg-slate-800" @click="openProfile(app)">
                    View Full Profile
                  </button>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="goTo('/employer/attendance')">
                    Review DTR
                  </button>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="goTo('/employer/work-outputs')">
                    Daily Reports
                  </button>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="openRating(app)">
                    Submit Rating
                  </button>
                  <button type="button" class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50" @click="triggerCertificateInput(app)">
                    Upload Certificate
                  </button>
                  <button
                    v-if="canAcknowledge(app)"
                    type="button"
                    class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:bg-slate-400"
                    :disabled="app.updating"
                    @click="acknowledgeAssignment(app)"
                  >
                    {{ app.updating ? 'Acknowledging...' : 'Acknowledge Assignment' }}
                  </button>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <h4 class="font-bold text-slate-950">Certificate Upload</h4>
                    <p class="mt-1 text-sm text-slate-500">Attach the completion certificate or report.</p>
                  </div>
                  <span v-if="app.certificate_path" class="rounded-full bg-green-100 px-3 py-1 text-xs font-bold text-green-700">
                    Uploaded
                  </span>
                </div>

                <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4">
                  <input
                    :id="`cert-${app.id}`"
                    type="file"
                    accept=".pdf,.jpg,.jpeg,.png"
                    class="block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white"
                  />
                  <p class="mt-3 text-xs text-slate-500">Accepted formats: PDF, JPG, JPEG, PNG. Maximum file size: 5MB.</p>
                  <p v-if="app.certificate_path" class="mt-3 break-all text-xs font-semibold text-slate-700">
                    Current file: {{ app.certificate_path }}
                  </p>
                </div>

                <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                  <button
                    type="button"
                    class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:bg-slate-400"
                    :disabled="uploadingId === app.id"
                    @click="sendCertificate(app)"
                  >
                    {{ uploadingId === app.id ? 'Uploading...' : 'Upload Certificate' }}
                  </button>
                  <button
                    v-if="app.certificate_path"
                    type="button"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                    @click="openCertificate(`/storage/${app.certificate_path}`)"
                  >
                    View File
                  </button>
                </div>
              </div>

              <div class="rounded-2xl border border-slate-200 bg-slate-950 p-5 text-white shadow-sm">
                <h4 class="font-bold">Submit for CPESO Review</h4>
                <p class="mt-2 text-sm leading-6 text-slate-300">{{ submissionHelper(app) }}</p>
                <button
                  type="button"
                  class="mt-4 w-full rounded-xl bg-emerald-500 px-4 py-3 text-sm font-bold text-white hover:bg-emerald-600 disabled:bg-slate-600 disabled:text-slate-300"
                  :disabled="app.updating || !canSubmitCompletion(app)"
                  @click="updateStatus(app, 'completion_review')"
                >
                  {{ app.updating ? 'Submitting...' : 'Submit for CPESO Review' }}
                </button>
              </div>
            </aside>
          </div>
        </article>
      </section>
    </div>

    <div
      v-if="selectedProfile"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="closeProfile"
    >
      <div class="max-h-[92vh] w-full max-w-2xl overflow-auto rounded-2xl bg-white shadow-2xl">
        <div class="relative bg-slate-950 p-6 text-white sm:p-8">
          <button
            type="button"
            class="absolute right-5 top-5 rounded-full bg-white/10 px-3 py-2 text-sm font-bold hover:bg-white/20"
            @click="closeProfile"
          >
            X
          </button>

          <div class="flex items-center gap-5">
            <img
              :src="selectedProfile.profile_photo_url || '/default-profile.png'"
              class="h-24 w-24 rounded-2xl border-4 border-white object-cover shadow-xl"
            />
            <div>
              <h2 class="text-2xl font-bold">
                {{ selectedProfile.name || `${selectedProfile.first_name || ''} ${selectedProfile.last_name || ''}`.trim() || 'Beneficiary' }}
              </h2>
              <p class="mt-2 break-all text-slate-300">{{ selectedProfile.email || 'No email available' }}</p>
              <div class="mt-3 inline-flex rounded-full bg-white/10 px-3 py-1 text-sm font-semibold capitalize">
                {{ selectedProfile.category || selectedProfile.user?.category || selectedProfile.beneficiary_type || selectedProfile.user?.beneficiary_type || 'N/A' }}
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-4 p-6 md:grid-cols-2 sm:p-8">
          <div
            v-for="item in profileDetails"
            :key="item.label"
            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
          >
            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">{{ item.label }}</p>
            <p class="mt-2 text-lg font-bold text-slate-900">{{ item.value }}</p>
          </div>
        </div>
      </div>
    </div>

    <div
      v-if="ratingTarget"
      class="fixed inset-0 z-50 flex items-center justify-end bg-black/50 p-4"
      @click.self="closeRating"
    >
      <form class="max-h-[94vh] w-full max-w-lg overflow-auto rounded-2xl bg-white p-6 shadow-2xl" @submit.prevent="submitRating">
        <div class="flex items-start justify-between gap-4">
          <div>
            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-600">Performance Rating</p>
            <h2 class="mt-1 text-xl font-bold text-slate-900">{{ beneficiaryName(ratingTarget) }}</h2>
            <p class="mt-1 text-sm text-slate-500">Submit employer evaluation for CPESO completion review.</p>
          </div>
          <button type="button" class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-semibold text-slate-700" @click="closeRating">
            Close
          </button>
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
        <textarea
          v-model="ratingForm.comment"
          rows="4"
          class="mt-2 w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none"
          placeholder="Summarize work attitude, reliability, and performance."
        ></textarea>

        <button
          type="submit"
          :disabled="submittingRating"
          class="mt-6 w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {{ submittingRating ? 'Submitting...' : 'Submit Rating' }}
        </button>
      </form>
    </div>

    <div
      v-if="completionModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="completionModalOpen = false"
    >
      <div class="w-full max-w-md rounded-2xl bg-white p-8 text-center shadow-2xl">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100 text-3xl font-bold text-green-700">
          OK
        </div>
        <h2 class="text-2xl font-bold text-slate-950">Completion Submitted</h2>
        <p class="mt-3 text-sm leading-6 text-slate-600">
          {{ beneficiaryName(completionModalJob) }} has been submitted for CPESO completion review.
        </p>
        <button
          type="button"
          class="mt-8 w-full rounded-xl bg-slate-900 py-3 font-bold text-white hover:bg-black"
          @click="completionModalOpen = false"
        >
          Close
        </button>
      </div>
    </div>

    <div
      v-if="certificateModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4"
      @click.self="closeCertificate"
    >
      <div class="w-full max-w-6xl rounded-2xl bg-white p-6 shadow-2xl">
        <div class="mb-5 flex items-center justify-between">
          <h2 class="text-2xl font-bold text-slate-950">Certificate Preview</h2>
          <button
            type="button"
            class="rounded-full bg-red-100 px-4 py-2 font-bold text-red-600 hover:bg-red-200"
            @click="closeCertificate"
          >
            X
          </button>
        </div>
        <div class="h-[80vh] w-full overflow-hidden rounded-2xl border border-slate-200">
          <iframe v-if="certificateModalUrl" :src="certificateModalUrl" class="h-full w-full"></iframe>
        </div>
      </div>
    </div>

    <transition name="fade">
      <div
        v-if="toast.show"
        :class="[
          'fixed right-6 top-6 z-50 rounded-2xl px-6 py-4 font-bold text-white shadow-2xl',
          toast.color === 'green' ? 'bg-green-600' : '',
          toast.color === 'red' ? 'bg-red-600' : '',
          toast.color === 'blue' ? 'bg-blue-600' : ''
        ]"
      >
        {{ toast.message }}
      </div>
    </transition>
  </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import axios from 'axios'

const { props } = usePage()

const applications = ref(props.applications || {})
const selectedProfile = ref(null)
const completionModalOpen = ref(false)
const completionModalJob = ref(null)
const certificateModalOpen = ref(false)
const certificateModalUrl = ref(null)
const uploadingId = ref(null)
const ratingTarget = ref(null)
const submittingRating = ref(false)

const ratingForm = ref({
  punctuality: 5,
  work_quality: 5,
  work_attitude: 5,
  communication: 5,
  overall: 5,
  comment: '',
})

const toast = ref({
  show: false,
  message: '',
  color: 'green'
})

const allApplications = computed(() => Object.values(applications.value).flat())

const totalBeneficiaries = computed(() => allApplications.value.length)

const readyForSubmissionCount = computed(() => {
  return allApplications.value.filter((app) => canSubmitCompletion(app)).length
})

const submittedForReviewCount = computed(() => {
  return allApplications.value.filter((app) => app.status === 'completion_review').length
})

const completedCount = computed(() => {
  return allApplications.value.filter((app) => app.status === 'completed').length
})

const summaryCards = computed(() => [
  {
    label: 'Assigned Beneficiaries',
    value: totalBeneficiaries.value,
    description: 'Beneficiaries currently assigned to your job slots.',
  },
  {
    label: 'Ready for Submission',
    value: readyForSubmissionCount.value,
    description: 'Records with all readiness items completed.',
  },
  {
    label: 'Submitted for Review',
    value: submittedForReviewCount.value,
    description: 'Completion requests awaiting CPESO action.',
  },
  {
    label: 'Completed',
    value: completedCount.value,
    description: 'Beneficiaries already marked complete.',
  },
])

const profileDetails = computed(() => {
  if (!selectedProfile.value) return []

  const profile = selectedProfile.value
  const category = profile.category || profile.user?.category || profile.beneficiary_type || profile.user?.beneficiary_type || 'N/A'
  const supportLabel = category === 'osy'
    ? 'OSY Skills'
    : category === 'dependent'
      ? 'Parent / Guardian'
      : 'School'
  const supportValue = category === 'osy'
    ? profile.skills || profile.user?.skills || 'N/A'
    : category === 'dependent'
      ? profile.parent_name || profile.guardian_name || profile.user?.parent_name || 'N/A'
      : profile.school?.name || profile.school_name || profile.school || profile.user?.school?.name || 'N/A'

  return [
    {
      label: 'Name',
      value: profile.name || `${profile.first_name || ''} ${profile.last_name || ''}`.trim() || 'N/A',
    },
    {
      label: 'Email',
      value: profile.email || 'N/A',
    },
    {
      label: 'Phone',
      value: profile.phone || profile.contact_number || 'N/A',
    },
    {
      label: supportLabel,
      value: supportValue,
    },
    {
      label: 'Category',
      value: category,
    },
    {
      label: 'Submission Date',
      value: formatDate(profile.created_at),
    },
  ]
})

function showToast(message, color = 'green') {
  toast.value = { show: true, message, color }

  setTimeout(() => {
    toast.value.show = false
  }, 4000)
}

function goBack() {
  window.history.back()
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
    punctuality: app.rating?.punctuality || app.employer_rating?.punctuality || 5,
    work_quality: app.rating?.work_quality || app.employer_rating?.work_quality || 5,
    work_attitude: app.rating?.work_attitude || app.employer_rating?.work_attitude || 5,
    communication: app.rating?.communication || app.employer_rating?.communication || 5,
    overall: app.rating?.overall || app.employer_rating?.overall || 5,
    comment: app.rating?.comment || app.employer_rating?.comment || '',
  }
}

function closeRating() {
  ratingTarget.value = null
  ratingForm.value.comment = ''
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

    ratingTarget.value.rating_submitted = true
    ratingTarget.value.employer_rating = {
      punctuality: Number(ratingForm.value.punctuality),
      work_quality: Number(ratingForm.value.work_quality),
      work_attitude: Number(ratingForm.value.work_attitude),
      communication: Number(ratingForm.value.communication),
      overall: Number(ratingForm.value.overall),
      comment: ratingForm.value.comment?.trim() || 'No comment',
    }

    showToast(`Rating submitted for ${beneficiaryName(ratingTarget.value)}`, 'green')
    closeRating()
  } catch (error) {
    console.error(error)
    showToast(error.response?.data?.message || 'Failed to submit rating.', 'red')
  } finally {
    submittingRating.value = false
  }
}

function openCertificate(url) {
  certificateModalUrl.value = url
  certificateModalOpen.value = true
}

function closeCertificate() {
  certificateModalOpen.value = false
  certificateModalUrl.value = null
}

function formatDate(value) {
  if (!value) return 'N/A'

  return new Date(value).toLocaleString([], {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  })
}

function statusColor(status) {
  switch (status) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-700'
    case 'assigned':
      return 'bg-amber-100 text-amber-700'
    case 'deployed':
    case 'ongoing':
      return 'bg-blue-100 text-blue-700'
    case 'completion_review':
      return 'bg-purple-100 text-purple-700'
    case 'completed':
      return 'bg-green-100 text-green-700'
    case 'rejected':
      return 'bg-red-100 text-red-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
}

function displayStatus(status) {
  return String(status || 'pending')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase())
}

function beneficiaryName(app) {
  const beneficiary = app?.beneficiary || app
  const name = beneficiary?.name || `${beneficiary?.first_name || ''} ${beneficiary?.last_name || ''}`.trim()
  return name || 'Unnamed beneficiary'
}

function contactValue(app) {
  return app?.beneficiary?.phone || app?.beneficiary?.contact_number || app?.beneficiary?.email || 'N/A'
}

function assignmentStatus(app) {
  if (!app?.employer_acknowledged_at && app?.status === 'assigned') {
    return 'Pending Acknowledgement'
  }

  if (app?.status === 'completion_review') {
    return 'Completion Review'
  }

  if (app?.status === 'deployed' || app?.status === 'ongoing') {
    return 'Active'
  }

  if (app?.status === 'completed') {
    return 'Completed'
  }

  return app?.employer_acknowledged_at ? 'Acknowledged' : 'Pending Acknowledgement'
}

function canAcknowledge(app) {
  return app?.status === 'assigned' && !app?.employer_acknowledged_at
}

function canSubmitCompletion(app) {
  return ['deployed', 'ongoing'].includes(app?.status) &&
    readinessItems(app).every((item) => item.ready)
}

function readinessItems(app) {
  return [
    {
      label: 'Assignment acknowledged',
      ready: Boolean(app?.employer_acknowledged_at),
    },
    {
      label: 'DTR reviewed',
      ready: Boolean(app?.dtr_reviewed || app?.attendance_reviewed || app?.approved_attendance_count || app?.attendance_count),
    },
    {
      label: 'Daily reports reviewed',
      ready: Boolean(app?.daily_reports_reviewed || app?.approved_work_outputs_count || app?.work_outputs_count),
    },
    {
      label: 'Employer rating submitted',
      ready: Boolean(app?.rating || app?.rating_submitted || app?.employer_rating),
    },
    {
      label: 'Certificate/report uploaded',
      ready: Boolean(app?.certificate_path),
    },
  ]
}

function readyItemCount(app) {
  return readinessItems(app).filter((item) => item.ready).length
}

function completionProgress(app) {
  const items = readinessItems(app)
  if (!items.length) return 0

  return Math.round((readyItemCount(app) / items.length) * 100)
}

function missingReadinessItems(app) {
  return readinessItems(app)
    .filter((item) => !item.ready)
    .map((item) => item.label)
}

function submissionHelper(app) {
  if (app?.status === 'completed') {
    return 'This beneficiary has already been marked completed.'
  }

  if (app?.status === 'completion_review') {
    return 'Completion has already been submitted and is awaiting CPESO approval.'
  }

  if (!['deployed', 'ongoing'].includes(app?.status)) {
    return 'This beneficiary must be active before completion can be submitted.'
  }

  const missing = missingReadinessItems(app)
  if (missing.length) {
    return `Complete these items first: ${missing.join(', ')}.`
  }

  return 'All readiness items are complete. Submit this beneficiary for CPESO review.'
}

function replaceApplication(updated) {
  Object.keys(applications.value).forEach((jobTitle) => {
    applications.value[jobTitle] = applications.value[jobTitle].map((app) => (
      app.id === updated.id ? { ...app, ...updated } : app
    ))
  })
}

function goTo(path) {
  window.location.href = path
}

function triggerCertificateInput(app) {
  document.getElementById(`cert-${app.id}`)?.click()
}

async function acknowledgeAssignment(app) {
  try {
    app.updating = true
    const response = await axios.post(`/employer/applications/${app.id}/acknowledge`)
    replaceApplication(response.data.application)
    showToast('Assignment acknowledged. Work supervision may begin.', 'green')
  } catch (error) {
    console.error(error)
    showToast(error.response?.data?.message || 'Failed to acknowledge assignment', 'red')
  } finally {
    app.updating = false
  }
}

async function updateStatus(app, status) {
  try {
    app.updating = true
    const response = await axios.post(`/employer/job-status/${app.id}`, { status })

    app.status = response.data.status || status

    if (app.status === 'completion_review') {
      completionModalJob.value = app
      completionModalOpen.value = true
      showToast('Completion submitted for CPESO review', 'green')
    } else {
      showToast('Job marked as ongoing', 'blue')
    }
  } catch (error) {
    console.error(error)
    showToast(error.response?.data?.message || 'Failed to update status', 'red')
  } finally {
    app.updating = false
  }
}

async function sendCertificate(app) {
  const fileInput = document.getElementById(`cert-${app.id}`)
  const file = fileInput?.files?.[0]

  if (!file) {
    showToast('Please select a file first', 'red')
    return
  }

  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']

  if (!allowedTypes.includes(file.type)) {
    showToast('Only PDF, JPG, and PNG files are allowed', 'red')
    return
  }

  if (file.size > 5 * 1024 * 1024) {
    showToast('File size must be less than 5MB', 'red')
    return
  }

  const formData = new FormData()
  formData.append('certificate', file)

  try {
    uploadingId.value = app.id
    const response = await axios.post(
      `/employer/job-status/${app.id}/certificate`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    app.certificate_path = response.data.path
    showToast('Certificate uploaded successfully!', 'green')
    fileInput.value = ''
  } catch (error) {
    console.error(error)
    const message = error.response?.data?.error || error.message || 'Failed to upload certificate'
    showToast(message, 'red')
  } finally {
    uploadingId.value = null
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>

