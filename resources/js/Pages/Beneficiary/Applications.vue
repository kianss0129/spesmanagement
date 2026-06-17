<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const applications = computed(() => page.props.applications || [])

const showCertificateModal = ref(false)
const selectedCertificate = ref(null)

const statusMap = {
  pending: 'Pending Review',
  applied: 'Pending Review',
  screening: 'Under CPESO Screening',
  draft: 'Pending Review',
  for_exam: 'Exam',
  exam: 'Exam',
  exam_passed: 'Exam Passed',
  interview: 'Interview',
  scheduled: 'Interview',
  for_interview: 'Interview',
  interview_passed: 'Interview Passed',
  qualified: 'Qualified',
  approved: 'Approved',
  selected: 'Approved',
  assigned: 'Assigned',
  deployed: 'Work Deployment',
  ongoing: 'Assigned',
  contract: 'Contract Signing',
  for_contract: 'Contract Signing',
  contract_signing: 'Contract Signing',
  contract_signed: 'Contract Signed',
  signed: 'Contract Signing',
  completion_review: 'Completion Review',
  completed: 'Completed',
  rejected: 'Rejected',
  correction: 'Needs Correction',
  needs_correction: 'Needs Correction',
}

const timeline = [
  { key: 'pending', label: 'Applied', description: 'Application submitted.' },
  { key: 'screening', label: 'Screening', description: 'CPESO reviews requirements and eligibility.' },
  { key: 'exam', label: 'Exam', description: 'Beneficiary takes the scheduled examination.' },
  { key: 'exam_passed', label: 'Exam Passed', description: 'Exam result is passed and ready for interview scheduling.' },
  { key: 'interview', label: 'Interview', description: 'Beneficiary attends the scheduled interview.' },
  { key: 'interview_passed', label: 'Interview Passed', description: 'Interview result is passed and ready for qualification.' },
  { key: 'qualified', label: 'Qualified', description: 'Beneficiary passed screening, exam, and interview requirements.' },
  { key: 'approved', label: 'Approved', description: 'Officially approved for SPES participation.' },
  { key: 'assigned', label: 'Assigned', description: 'Assigned to employer/job placement.' },
  { key: 'contract_signing', label: 'Contract Signing', description: 'Contract signing is scheduled with employer/CPESO.' },
  { key: 'contract_signed', label: 'Contract Signed', description: 'Contract has been signed and is ready for deployment.' },
  { key: 'deployed', label: 'Deployed', description: 'Beneficiary is deployed to the assigned work site.' },
  { key: 'ongoing', label: 'Ongoing Work', description: 'Submit DTR and daily accomplishment reports during work.' },
  { key: 'completion_review', label: 'Completion Review', description: 'Employer and CPESO validate completion records.' },
  { key: 'completed', label: 'Completed', description: 'SPES participation completed.' },
]

const latestApplication = computed(() => applications.value[0] || null)

function normalizedStatus(app) {
  if (!app) return 'pending'
  if (app.status === 'completed') return 'completed'
  if (app.is_assigned) return 'assigned'

  const raw = String(app.status || 'pending').toLowerCase()

  if (['applied', 'pending', 'draft'].includes(raw)) return 'pending'
  if (['screening', 'submitted', 'under_review', 'review'].includes(raw)) return 'screening'
  if (['exam', 'for_exam', 'exam_scheduled'].includes(raw)) return 'exam'
  if (['exam_passed'].includes(raw)) return 'exam_passed'
  if (['interview', 'for_interview', 'scheduled'].includes(raw)) return 'interview'
  if (['interview_passed'].includes(raw)) return 'interview_passed'
  if (['qualified', 'for_approval', 'passed'].includes(raw)) return 'qualified'
  if (['approved', 'selected'].includes(raw)) return 'approved'
  if (['assigned'].includes(raw)) return 'assigned'
  if (['contract', 'for_contract', 'contract_signing', 'contract-signing'].includes(raw)) return 'contract_signing'
  if (['contract_signed', 'signed'].includes(raw)) return 'contract_signed'
  if (['deployed'].includes(raw)) return 'deployed'
  if (['ongoing'].includes(raw)) return 'ongoing'
  if (['completion_review'].includes(raw)) return 'completion_review'
  if (['rejected'].includes(raw)) return 'rejected'
  if (['correction', 'needs_correction'].includes(raw)) return 'needs_correction'

  return 'pending'
}

function statusLabel(app) {
  const status = normalizedStatus(app)
  return statusMap[status] || statusMap[String(app?.status || '').toLowerCase()] || 'Pending Review'
}

function statusClasses(app) {
  const status = normalizedStatus(app)

  return {
    pending: 'bg-amber-100 text-amber-800 border-amber-200',
    screening: 'bg-amber-100 text-amber-800 border-amber-200',
    exam: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    exam_passed: 'bg-indigo-100 text-indigo-800 border-indigo-200',
    interview: 'bg-purple-100 text-purple-800 border-purple-200',
    interview_passed: 'bg-purple-100 text-purple-800 border-purple-200',
    needs_correction: 'bg-orange-100 text-orange-800 border-orange-200',
    qualified: 'bg-blue-100 text-blue-800 border-blue-200',
    approved: 'bg-green-100 text-green-800 border-green-200',
    assigned: 'bg-cyan-100 text-cyan-800 border-cyan-200',
    contract_signing: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    contract_signed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    deployed: 'bg-cyan-100 text-cyan-800 border-cyan-200',
    ongoing: 'bg-cyan-100 text-cyan-800 border-cyan-200',
    completion_review: 'bg-blue-100 text-blue-800 border-blue-200',
    completed: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    rejected: 'bg-red-100 text-red-800 border-red-200',
  }[status] || 'bg-slate-100 text-slate-700 border-slate-200'
}

function statusDescription(app) {
  const status = normalizedStatus(app)

  if (status === 'needs_correction') return 'Please review CPESO remarks and update your requirements.'
  if (status === 'rejected') return 'Your application was not approved. Review the reason below for guidance.'
  if (status === 'completed') return 'Your application journey is complete.'
  if (status === 'completion_review') return 'Your completion documents are being validated.'
  if (status === 'ongoing') return 'Continue submitting attendance and daily accomplishment reports.'
  if (status === 'deployed') return 'You are deployed to your assigned work site.'
  if (status === 'contract_signed') return 'Your contract has been signed and is ready for deployment.'
  if (status === 'contract_signing') return 'Your contract signing is scheduled.'
  if (status === 'assigned') return 'You have a job placement. Proceed with attendance and DTR requirements.'
  if (status === 'approved') return 'Your application is approved and waiting for placement or schedule updates.'
  if (status === 'qualified') return 'You passed screening, exam, and interview requirements.'
  if (status === 'interview_passed') return 'You passed the interview and are waiting for qualification.'
  if (status === 'interview') return 'Prepare for your interview and monitor messages for instructions.'
  if (status === 'exam_passed') return 'You passed the exam and are waiting for interview scheduling.'
  if (status === 'exam') return 'Prepare for your scheduled examination and monitor messages for instructions.'
  if (status === 'screening') return 'CPESO is reviewing your requirements and eligibility.'

  return 'Your application is waiting for CPESO review.'
}

function formatDate(value) {
  if (!value) return 'Not available'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleString('en-PH', {
    dateStyle: 'medium',
    timeStyle: 'short',
    timeZone: 'Asia/Manila',
  })
}

function currentTimelineIndex(app) {
  const status = normalizedStatus(app)
  if (status === 'rejected' || status === 'needs_correction') return 0
  return Math.max(0, timeline.findIndex((item) => item.key === status))
}

function nextAction(app) {
  const status = normalizedStatus(app)

  if (status === 'completed' && app?.certificate_path) {
    return { label: 'View Certificate', action: () => viewCertificate(app.certificate_path), button: true }
  }

  if (['assigned', 'deployed', 'ongoing'].includes(status)) {
    return { label: 'Submit Attendance / DTR', href: `/beneficiary/attendance?job=${app.id}` }
  }

  if (status === 'contract_signing' || status === 'contract_signed') {
    return { label: 'View Schedule', href: '/beneficiary/interviews' }
  }

  if (status === 'needs_correction' || status === 'rejected') {
    return { label: 'Update Requirements', href: '/onboarding?step=1' }
  }

  if (status === 'approved') {
    return { label: 'View Job Placement', href: '/beneficiary/jobs' }
  }

  if (status === 'interview' || status === 'interview_passed' || status === 'qualified') {
    return { label: 'View Schedule', href: '/beneficiary/interviews' }
  }

  if (status === 'exam' || status === 'exam_passed') {
    return { label: 'View Exams', href: '/beneficiary/exams' }
  }

  return { label: 'Review Requirements', href: '/beneficiary/upload' }
}

const requirementItems = computed(() => [
  {
    name: 'Application information',
    status: latestApplication.value ? 'Submitted' : 'Pending',
    complete: Boolean(latestApplication.value),
    detail: latestApplication.value ? 'Your application record exists.' : 'Start or submit your SPES application.',
  },
  {
    name: 'Document requirements',
    status: latestApplication.value ? 'For CPESO review' : 'Pending',
    complete: Boolean(latestApplication.value),
    detail: 'Birth certificate, school record, income proof, and category-specific documents.',
  },
  {
    name: 'CPESO review',
    status: latestApplication.value ? statusLabel(latestApplication.value) : 'Pending',
    complete: ['approved', 'assigned', 'completed'].includes(normalizedStatus(latestApplication.value)),
    detail: 'CPESO will update this after reviewing your application.',
  },
])

function viewCertificate(path) {
  selectedCertificate.value = path
  showCertificateModal.value = true
  document.body.style.overflow = 'hidden'
}

function closeModal() {
  showCertificateModal.value = false
  selectedCertificate.value = null
  document.body.style.overflow = 'auto'
}

function goBack() {
  window.history.length > 1 ? window.history.back() : router.visit('/beneficiary')
}
</script>

<template>
  <Head title="My Application" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <button
        type="button"
        @click="goBack"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back
      </button>

      <div class="mt-6 flex flex-col gap-2">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Application Tracking</p>
        <h1 class="text-3xl font-bold text-slate-900">My Application</h1>
        <p class="text-sm text-slate-600">Track your SPES application status, review progress, and next step.</p>
      </div>

      <section
        v-if="!latestApplication"
        class="mt-6 rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm"
      >
        <h2 class="text-xl font-bold text-slate-900">No application record yet</h2>
        <p class="mt-2 text-sm text-slate-600">Your submitted SPES application will appear here.</p>
        <Link
          href="/onboarding"
          class="mt-5 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
        >
          Start Application
        </Link>
      </section>

      <section v-else class="mt-6 space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
            <div>
              <p class="text-sm text-slate-500">Current application status</p>
              <div class="mt-3 flex flex-wrap items-center gap-3">
                <h2 class="text-3xl font-bold text-slate-900">{{ statusLabel(latestApplication) }}</h2>
                <span
                  class="rounded-full border px-3 py-1 text-xs font-semibold"
                  :class="statusClasses(latestApplication)"
                >
                  {{ statusLabel(latestApplication) }}
                </span>
              </div>
              <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                {{ statusDescription(latestApplication) }}
              </p>
            </div>

            <component
              :is="nextAction(latestApplication).button ? 'button' : Link"
              :href="nextAction(latestApplication).href"
              type="button"
              @click="nextAction(latestApplication).button ? nextAction(latestApplication).action() : null"
              class="inline-flex shrink-0 items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
            >
              {{ nextAction(latestApplication).label }}
            </component>
          </div>

          <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Submitted date</p>
              <p class="mt-2 font-semibold text-slate-900">{{ formatDate(latestApplication.submitted_at) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">Last updated</p>
              <p class="mt-2 font-semibold text-slate-900">{{ formatDate(latestApplication.updated_at) }}</p>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-bold text-slate-900">Review progress</h3>
          <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <div
              v-for="(item, index) in timeline"
              :key="item.key"
              class="rounded-xl border p-4"
              :class="index < currentTimelineIndex(latestApplication)
                ? 'border-green-200 bg-green-50'
                : index === currentTimelineIndex(latestApplication)
                  ? 'border-blue-200 bg-blue-50'
                  : 'border-slate-200 bg-slate-50'"
            >
              <div
                class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold"
                :class="index < currentTimelineIndex(latestApplication)
                  ? 'bg-green-600 text-white'
                  : index === currentTimelineIndex(latestApplication)
                    ? 'bg-blue-600 text-white'
                    : 'bg-slate-200 text-slate-500'"
              >
                {{ index < currentTimelineIndex(latestApplication) ? '✓' : index + 1 }}
              </div>
              <p class="mt-3 font-semibold text-slate-900">{{ item.label }}</p>
              <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.description }}</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_0.85fr]">
          <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">Requirements / document status</h3>
            <div class="mt-5 space-y-3">
              <div
                v-for="item in requirementItems"
                :key="item.name"
                class="flex items-start justify-between gap-4 rounded-xl border border-slate-200 p-4"
              >
                <div>
                  <p class="font-semibold text-slate-900">{{ item.name }}</p>
                  <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.detail }}</p>
                </div>
                <span
                  class="rounded-full px-3 py-1 text-xs font-semibold"
                  :class="item.complete ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                >
                  {{ item.status }}
                </span>
              </div>
            </div>
          </div>

          <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-slate-900">CPESO remarks</h3>
            <div class="mt-5 rounded-xl border p-4" :class="latestApplication.remarks ? 'border-orange-200 bg-orange-50' : 'border-slate-200 bg-slate-50'">
              <p class="text-sm leading-6" :class="latestApplication.remarks ? 'text-orange-900' : 'text-slate-600'">
                {{ latestApplication.remarks || 'No correction or rejection remarks have been posted by CPESO.' }}
              </p>
            </div>

            <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
              <p class="text-sm font-semibold text-slate-900">{{ latestApplication.job_title }}</p>
              <p class="mt-1 text-sm text-slate-600">{{ latestApplication.employer }}</p>
              <p class="mt-2 text-xs text-slate-500">Application #{{ latestApplication.id }}</p>
            </div>
          </div>
        </div>

        <div v-if="applications.length > 1" class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-bold text-slate-900">Other application records</h3>
          <div class="mt-4 divide-y divide-slate-200">
            <div v-for="app in applications.slice(1)" :key="app.id" class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
              <div>
                <p class="font-semibold text-slate-900">{{ app.job_title }}</p>
                <p class="text-sm text-slate-500">{{ app.employer }}</p>
              </div>
              <span class="w-fit rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClasses(app)">
                {{ statusLabel(app) }}
              </span>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div
      v-if="showCertificateModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
      @click.self="closeModal"
    >
      <div class="h-[85vh] w-full max-w-5xl overflow-hidden rounded-lg bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b p-4">
          <h2 class="text-lg font-bold text-slate-900">Certificate of Completion</h2>
          <button type="button" @click="closeModal" class="text-sm font-semibold text-red-600">Close</button>
        </div>

        <iframe
          v-if="selectedCertificate?.endsWith('.pdf')"
          :src="`/storage/${selectedCertificate}`"
          class="h-full w-full"
        ></iframe>

        <div v-else class="flex h-full items-center justify-center bg-slate-100 p-4">
          <img :src="`/storage/${selectedCertificate}`" class="max-h-full max-w-full rounded-xl shadow" />
        </div>
      </div>
    </div>
  </main>
</template>
