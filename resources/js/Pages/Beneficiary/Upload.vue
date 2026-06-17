<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const beneficiaryCategory = computed(() => page.props.auth?.user?.beneficiary_type || 'student')

const allRequirements = {
  student: [
    { key: 'valid_id', name: 'Valid ID', description: 'Clear copy of your valid government-issued ID.' },
    { key: 'barangay_certificate', name: 'Barangay Certificate', description: 'Barangay certificate of residency.' },
    { key: 'school_enrollment', name: 'School Enrollment', description: 'Enrollment form, certificate of registration, or school record.' },
  ],
  osy: [
    { key: 'valid_id', name: 'Valid ID', description: 'Clear copy of your valid government-issued ID.' },
    { key: 'barangay_certificate', name: 'Barangay Certificate', description: 'Barangay certificate of residency.' },
    { key: 'school_enrollment', name: 'School Record / Certification', description: 'Last school record or certification of attendance.' },
  ],
  dependent: [
    { key: 'valid_id', name: 'Valid ID', description: 'Clear copy of your valid government-issued ID.' },
    { key: 'barangay_certificate', name: 'Barangay Certificate', description: 'Barangay certificate of residency.' },
    { key: 'school_enrollment', name: 'School Record / ID', description: 'School record or student ID.' },
    { key: 'displacement_proof', name: 'Displacement Proof', description: 'Certificate or proof of parent/guardian displacement.' },
    { key: 'parent_valid_id', name: 'Parent/Guardian Valid ID', description: 'Valid ID of the displaced parent or guardian.' },
  ],
}

const requirements = computed(() => allRequirements[beneficiaryCategory.value] || allRequirements.student)

const documents = ref([])
const selectedFiles = reactive({})
const uploading = ref(false)
const loading = ref(true)
const message = ref('')
const error = ref('')

const documentMap = computed(() => {
  const map = {}

  documents.value.forEach((document) => {
    const key = document.key || normalizeKey(document.name)
    map[key] = document
  })

  return map
})

const checklist = computed(() =>
  requirements.value.map((requirement) => {
    const document = documentMap.value[requirement.key]
    const status = normalizeStatus(document?.status, document?.path)

    return {
      ...requirement,
      document,
      status,
      label: statusLabel(status),
      complete: ['uploaded', 'under_review', 'accepted'].includes(status),
      uploadedAt: document?.uploaded_at || document?.updated_at || null,
      remarks: document?.remarks || document?.rejection_reason || document?.reason || '',
      path: resolveDocumentPath(document?.path),
      selectedFile: selectedFiles[requirement.key] || null,
    }
  })
)

const completedCount = computed(() =>
  checklist.value.filter((item) => item.complete).length
)

const totalCount = computed(() => checklist.value.length)

function normalizeKey(value) {
  return String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_|_$/g, '')
}

function normalizeStatus(status, path) {
  const normalized = String(status || '').toLowerCase()

  if (['accepted', 'approved'].includes(normalized)) return 'accepted'
  if (['rejected', 'needs_correction', 'correction'].includes(normalized)) return 'needs_correction'
  if (['under_review', 'for_review', 'pending_review'].includes(normalized)) return 'under_review'
  if (['uploaded', 'submitted'].includes(normalized)) return 'uploaded'
  if (path) return 'uploaded'

  return 'missing'
}

function statusLabel(status) {
  return {
    missing: 'Missing',
    uploaded: 'Uploaded',
    under_review: 'Under Review',
    accepted: 'Accepted',
    needs_correction: 'Needs Correction',
  }[status] || 'Missing'
}

function statusClasses(status) {
  return {
    missing: 'bg-slate-100 text-slate-700 border-slate-200',
    uploaded: 'bg-blue-100 text-blue-800 border-blue-200',
    under_review: 'bg-amber-100 text-amber-800 border-amber-200',
    accepted: 'bg-green-100 text-green-800 border-green-200',
    needs_correction: 'bg-red-100 text-red-800 border-red-200',
  }[status] || 'bg-slate-100 text-slate-700 border-slate-200'
}

function actionLabel(item) {
  if (item.status === 'missing') return 'Upload'
  if (item.status === 'needs_correction') return 'Replace'
  if (item.selectedFile) return 'Replace'
  return 'Replace'
}

function resolveDocumentPath(path) {
  if (!path) return ''
  if (String(path).startsWith('/storage/')) return path
  if (String(path).startsWith('http')) return path
  return `/storage/${path}`
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

function handleFile(event, key) {
  const file = event.target.files?.[0]
  if (file) {
    selectedFiles[key] = file
    message.value = ''
    error.value = ''
  }
}

async function loadDocuments() {
  try {
    loading.value = true
    const response = await axios.get('/api/beneficiary/documents')
    documents.value = Array.isArray(response.data) ? response.data : []
  } catch (err) {
    error.value = 'Unable to load your document checklist.'
    documents.value = []
  } finally {
    loading.value = false
  }
}

async function uploadRequirement(item) {
  const file = selectedFiles[item.key]

  if (!file) {
    error.value = `Please choose a file for ${item.name}.`
    return
  }

  const formData = new FormData()
  formData.append(item.key, file)

  try {
    uploading.value = true
    error.value = ''
    message.value = ''

    await axios.post('/onboarding/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    selectedFiles[item.key] = null
    message.value = `${item.name} uploaded successfully.`
    await loadDocuments()
  } catch (err) {
    error.value = err.response?.data?.message || `Unable to upload ${item.name}.`
  } finally {
    uploading.value = false
  }
}

onMounted(loadDocuments)
</script>

<template>
  <Head title="Requirements" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <Link
        href="/beneficiary"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Dashboard
      </Link>

      <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">
          SPES Application
        </p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Requirements</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Upload clear copies of your SPES requirements. CPESO will review each document and may ask you to replace unclear or incomplete files.
        </p>
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <p class="text-sm font-semibold text-slate-700">Progress summary</p>
          <div class="mt-4 flex items-end gap-2">
            <span class="text-4xl font-bold text-blue-700">{{ completedCount }}</span>
            <span class="pb-1 text-sm text-slate-500">/ {{ totalCount }} completed</span>
          </div>
          <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-200">
            <div
              class="h-full rounded-full bg-blue-600 transition-all duration-500"
              :style="{ width: `${totalCount ? Math.round((completedCount / totalCount) * 100) : 0}%` }"
            ></div>
          </div>
          <p class="mt-4 text-sm leading-6 text-slate-600">
            Completed means the requirement has been uploaded or already accepted. CPESO may still review uploaded files.
          </p>
        </div>

        <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 shadow-sm">
          <h2 class="text-lg font-bold text-amber-950">Reminder</h2>
          <p class="mt-2 text-sm leading-6 text-amber-900">
            Incomplete, blurry, expired, or incorrect documents may delay your application review. Replace documents marked as Needs Correction as soon as possible.
          </p>
        </div>
      </section>

      <div v-if="message" class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">
        {{ message }}
      </div>

      <div v-if="error" class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">
        {{ error }}
      </div>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5 sm:p-6">
          <h2 class="text-lg font-bold text-slate-900">Requirement checklist</h2>
          <p class="mt-1 text-sm text-slate-500">Choose a file, then upload or replace the requirement.</p>
        </div>

        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">
          Loading requirements...
        </div>

        <div v-else class="divide-y divide-slate-200">
          <article
            v-for="item in checklist"
            :key="item.key"
            class="grid gap-5 p-5 sm:p-6 xl:grid-cols-[1fr_0.8fr]"
          >
            <div>
              <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                  <h3 class="text-base font-bold text-slate-900">{{ item.name }}</h3>
                  <p class="mt-1 text-sm leading-6 text-slate-600">{{ item.description }}</p>
                </div>
                <span
                  class="w-fit rounded-full border px-3 py-1 text-xs font-semibold"
                  :class="statusClasses(item.status)"
                >
                  {{ item.label }}
                </span>
              </div>

              <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div class="rounded-lg bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Last uploaded</dt>
                  <dd class="mt-1 font-medium text-slate-800">{{ formatDate(item.uploadedAt) }}</dd>
                </div>
                <div class="rounded-lg bg-slate-50 p-3">
                  <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">CPESO remarks</dt>
                  <dd class="mt-1 font-medium text-slate-800">{{ item.remarks || 'No remarks posted.' }}</dd>
                </div>
              </dl>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
              <label class="block text-sm font-semibold text-slate-800" :for="`file-${item.key}`">
                Upload or replace file
              </label>
              <input
                :id="`file-${item.key}`"
                type="file"
                accept=".pdf,.jpg,.jpeg,.png"
                class="mt-3 block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white"
                @change="event => handleFile(event, item.key)"
              />

              <p v-if="item.selectedFile" class="mt-2 text-xs text-slate-500">
                Selected: {{ item.selectedFile.name }}
              </p>

              <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  :disabled="uploading || !item.selectedFile"
                  class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                  @click="uploadRequirement(item)"
                >
                  {{ uploading ? 'Uploading...' : actionLabel(item) }}
                </button>

                <a
                  v-if="item.path"
                  :href="item.path"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                >
                  View
                </a>
              </div>
            </div>
          </article>
        </div>
      </section>
    </div>
  </main>
</template>
