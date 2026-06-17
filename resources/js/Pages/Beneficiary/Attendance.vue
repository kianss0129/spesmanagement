<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import axios from 'axios'

const attendance = ref([])
const assignedEmployer = ref(null)
const loading = ref(true)
const submitting = ref(false)
const proofFile = ref(null)
const message = ref('')
const error = ref('')
const cameraOpen = ref(false)
const cameraError = ref('')
const videoRef = ref(null)
const canvasRef = ref(null)
const mediaStream = ref(null)
const fallbackInput = ref(null)
const capturedPreview = ref('')

const today = localDate()

const todayRecord = computed(() =>
  attendance.value.find((record) => record.date === today && record.time_in && record.time_out) || null
)

const openRecord = computed(() =>
  attendance.value.find((record) => record.date === today && record.time_in && !record.time_out) || null
)

const isAssigned = computed(() => Boolean(assignedEmployer.value?.company))

const todayStatus = computed(() => {
  if (!isAssigned.value) return 'Not allowed'
  if (todayRecord.value) return 'Submitted'
  if (openRecord.value) return 'Time In submitted'
  return 'Not yet submitted'
})

const totalHours = computed(() =>
  attendance.value.reduce((sum, record) => sum + (Number(record.hours) || 0), 0)
)

const renderedDays = computed(() =>
  attendance.value.filter((record) => record.time_in || record.time_out).length
)

const missingDtrCount = computed(() =>
  attendance.value.filter((record) =>
    ['missing', 'needs_correction', 'rejected'].includes(normalizeStatus(record.status))
  ).length
)

const historyRows = computed(() =>
  [...attendance.value].sort((a, b) => new Date(b.date) - new Date(a.date))
)

const canTimeIn = computed(() =>
  isAssigned.value && !todayRecord.value && !openRecord.value
)

const canTimeOut = computed(() =>
  isAssigned.value && Boolean(openRecord.value)
)

function localDate() {
  const now = new Date()
  return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`
}

function currentTime() {
  return new Date().toTimeString().slice(0, 5)
}

function setCapturedPreview(file) {
  if (capturedPreview.value) {
    URL.revokeObjectURL(capturedPreview.value)
  }
  capturedPreview.value = URL.createObjectURL(file)
}

function handleProof(event) {
  const file = event.target.files?.[0] || null
  proofFile.value = file
  cameraError.value = ''
  error.value = ''
  message.value = ''

  if (file) {
    setCapturedPreview(file)
  } else {
    if (capturedPreview.value) {
      URL.revokeObjectURL(capturedPreview.value)
    }
    capturedPreview.value = ''
  }
}

async function openCamera() {
  cameraError.value = ''
  error.value = ''
  message.value = ''

  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    cameraError.value = 'Camera is not supported by this browser. Use the file upload fallback instead.'
    return
  }

  try {
    const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false })
    mediaStream.value = stream
    cameraOpen.value = true

    await nextTick()
    if (videoRef.value) {
      videoRef.value.srcObject = stream
      await videoRef.value.play().catch(() => {})
    }
  } catch (err) {
    cameraOpen.value = false
    mediaStream.value = null
    const name = err?.name || ''

    if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
      cameraError.value = 'Camera permission was denied. Please allow access or use the file upload fallback.'
    } else if (name === 'NotFoundError' || name === 'OverconstrainedError') {
      cameraError.value = 'No camera device was found. Please use the file upload fallback.'
    } else {
      cameraError.value = 'Unable to open the camera. Please try again or use the file upload fallback.'
    }
  }
}

function stopCamera() {
  cameraOpen.value = false
  if (videoRef.value) {
    videoRef.value.srcObject = null
  }
  mediaStream.value?.getTracks().forEach((track) => track.stop())
  mediaStream.value = null
}

function openFileUpload() {
  fallbackInput.value?.click()
}

function removeCapturedPhoto() {
  if (capturedPreview.value) {
    URL.revokeObjectURL(capturedPreview.value)
  }
  capturedPreview.value = ''
  proofFile.value = null
  cameraError.value = ''
  message.value = ''
}

function retakePhoto() {
  removeCapturedPhoto()
  openCamera()
}

async function capturePhoto() {
  cameraError.value = ''
  error.value = ''
  message.value = ''

  if (!videoRef.value || !mediaStream.value) {
    cameraError.value = 'No active camera to capture from.'
    return
  }

  const video = videoRef.value
  const canvas = canvasRef.value
  if (!canvas) {
    cameraError.value = 'Unable to capture photo.'
    return
  }

  canvas.width = video.videoWidth || 1280
  canvas.height = video.videoHeight || 720
  const context = canvas.getContext('2d')
  if (!context) {
    cameraError.value = 'Unable to capture photo.'
    return
  }

  context.drawImage(video, 0, 0, canvas.width, canvas.height)

  await new Promise((resolve) => {
    canvas.toBlob((blob) => {
      if (!blob) {
        cameraError.value = 'Unable to convert captured image.'
        return resolve(undefined)
      }

      const now = new Date()
      const timestamp = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}-${String(now.getHours()).padStart(2, '0')}-${String(now.getMinutes()).padStart(2, '0')}-${String(now.getSeconds()).padStart(2, '0')}`
      const file = new File([blob], `dtr-proof-${timestamp}.jpg`, { type: 'image/jpeg' })
      proofFile.value = file
      setCapturedPreview(file)
      stopCamera()
      message.value = 'Photo captured and ready for submission.'
      resolve(undefined)
    }, 'image/jpeg', 0.92)
  })
}

async function submitDtr(mode) {
  if (!isAssigned.value) {
    error.value = 'You need an assigned employer before submitting DTR.'
    return
  }

  if (!proofFile.value) {
    error.value = 'Please upload a clear proof photo or DTR file.'
    return
  }

  const formData = new FormData()
  formData.append('date', today)
  formData.append('proof', proofFile.value)

  if (mode === 'time_out' && openRecord.value) {
    formData.append('time_in', openRecord.value.time_in)
    formData.append('time_out', currentTime())
  } else {
    formData.append('time_in', currentTime())
    formData.append('time_out', '')
  }

  try {
    submitting.value = true
    error.value = ''
    message.value = ''

    await axios.post('/api/beneficiary/dtr', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    proofFile.value = null
    if (capturedPreview.value) {
      URL.revokeObjectURL(capturedPreview.value)
      capturedPreview.value = ''
    }
    message.value = mode === 'time_out' ? 'Time Out submitted successfully.' : 'Time In submitted successfully.'
    await loadAttendance()
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to submit DTR.'
  } finally {
    submitting.value = false
  }
}

async function loadAttendance() {
  const [attendanceResult, employerResult] = await Promise.allSettled([
    axios.get('/api/beneficiary/attendance'),
    axios.get('/api/beneficiary/assigned-employer'),
  ])

  attendance.value = Array.isArray(attendanceResult.value?.data) ? attendanceResult.value.data : []
  assignedEmployer.value = employerResult.value?.data || null
}

async function initialize() {
  loading.value = true
  await loadAttendance()
  loading.value = false
}

function normalizeStatus(status) {
  const value = String(status || '').toLowerCase()
  if (['approved', 'present', 'completed'].includes(value)) return 'approved'
  if (['pending', 'submitted', 'under_review'].includes(value)) return 'under_review'
  if (['rejected', 'needs_correction', 'correction'].includes(value)) return 'needs_correction'
  if (['missing', 'absent'].includes(value)) return 'missing'
  return value || 'submitted'
}

function statusLabel(status) {
  return {
    approved: 'Approved',
    under_review: 'Under Review',
    needs_correction: 'Needs Correction',
    missing: 'Missing',
    submitted: 'Submitted',
  }[normalizeStatus(status)] || 'Submitted'
}

function statusClasses(status) {
  return {
    approved: 'bg-green-100 text-green-800 border-green-200',
    under_review: 'bg-amber-100 text-amber-800 border-amber-200',
    needs_correction: 'bg-red-100 text-red-800 border-red-200',
    missing: 'bg-slate-100 text-slate-700 border-slate-200',
    submitted: 'bg-blue-100 text-blue-800 border-blue-200',
  }[normalizeStatus(status)] || 'bg-blue-100 text-blue-800 border-blue-200'
}

function formatDate(value) {
  if (!value) return 'Not available'
  const date = new Date(value)
  if (Number.isNaN(date.getTime())) return value

  return date.toLocaleDateString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
  })
}

function formatHours(value) {
  const hours = Number(value) || 0
  return `${hours.toFixed(2)} hrs`
}

onMounted(initialize)

onBeforeUnmount(() => {
  stopCamera()
  if (capturedPreview.value) {
    URL.revokeObjectURL(capturedPreview.value)
  }
})
</script>

<template>
  <Head title="Attendance / DTR" />

  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <Link
        href="/beneficiary"
        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
      >
        Back to Dashboard
      </Link>

      <section class="mt-6">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Work Attendance</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-900">Attendance / DTR</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
          Track and submit your SPES work attendance.
        </p>
      </section>

      <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-semibold text-slate-600">Assignment status</p>
          <p class="mt-3 text-xl font-bold" :class="isAssigned ? 'text-green-700' : 'text-amber-700'">
            {{ isAssigned ? 'Assigned' : 'Not assigned' }}
          </p>
          <p class="mt-1 text-sm text-slate-500">{{ assignedEmployer?.company || 'No employer assigned yet.' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-semibold text-slate-600">Today’s DTR status</p>
          <p class="mt-3 text-xl font-bold text-blue-700">{{ todayStatus }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ today }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-semibold text-slate-600">Rendered</p>
          <p class="mt-3 text-xl font-bold text-slate-900">{{ formatHours(totalHours) }}</p>
          <p class="mt-1 text-sm text-slate-500">{{ renderedDays }} day{{ renderedDays === 1 ? '' : 's' }} with records</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <p class="text-sm font-semibold text-slate-600">Missing / correction</p>
          <p class="mt-3 text-xl font-bold text-red-700">{{ missingDtrCount }}</p>
          <p class="mt-1 text-sm text-slate-500">DTR entries needing attention</p>
        </div>
      </section>

      <section class="mt-6 grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <h2 class="text-lg font-bold text-slate-900">Assigned Job</h2>
          <dl class="mt-4 space-y-3 text-sm">
            <div class="rounded-lg bg-slate-50 p-3">
              <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Employer</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ assignedEmployer?.company || 'Not assigned' }}</dd>
            </div>
            <div class="rounded-lg bg-slate-50 p-3">
              <dt class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Job / position</dt>
              <dd class="mt-1 font-semibold text-slate-900">{{ assignedEmployer?.job_title || 'Not available' }}</dd>
            </div>
          </dl>
          <Link
            href="/beneficiary/jobs"
            class="mt-4 inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
          >
            View assigned job
          </Link>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
          <h2 class="text-lg font-bold text-slate-900">Submit Today’s DTR</h2>
          <p class="mt-1 text-sm text-slate-500">
            Upload a clear proof photo or DTR file before submitting your attendance action.
          </p>

          <div v-if="message" class="mt-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-800">
            {{ message }}
          </div>
          <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-800">
            {{ error }}
          </div>

          <div class="mt-5 grid gap-3">
            <button
              type="button"
              class="inline-flex w-full items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
              @click="openCamera"
              :disabled="submitting"
            >
              Open Camera
            </button>

            <button
              v-if="cameraError"
              type="button"
              class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
              @click="openFileUpload"
            >
              Use file upload instead
            </button>
            <div v-if="cameraError" class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">
              {{ cameraError }}
            </div>

            <div v-if="cameraOpen" class="rounded-lg border border-slate-200 bg-slate-50 p-3">
              <video
                ref="videoRef"
                class="h-64 w-full rounded-lg bg-black object-cover"
                playsinline
                muted
              ></video>
              <canvas ref="canvasRef" class="hidden"></canvas>
              <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  class="inline-flex w-full items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700"
                  @click="capturePhoto"
                >
                  Capture Photo
                </button>
                <button
                  type="button"
                  class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                  @click="stopCamera"
                >
                  Close Camera
                </button>
              </div>
            </div>

            <div v-if="capturedPreview" class="rounded-lg border border-slate-200 bg-white p-3">
              <p class="text-sm font-semibold text-slate-700">Photo preview</p>
              <img
                :src="capturedPreview"
                alt="Captured proof preview"
                class="mt-3 h-auto w-full rounded-lg border border-slate-200 object-cover"
              />
              <p class="mt-2 text-xs text-slate-500">Selected: {{ proofFile?.name || 'Captured photo' }}</p>
              <div class="mt-3 flex flex-col gap-3 sm:flex-row">
                <button
                  type="button"
                  class="inline-flex w-full items-center justify-center rounded-lg bg-yellow-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-yellow-600"
                  @click="retakePhoto"
                >
                  Retake Photo
                </button>
                <button
                  type="button"
                  class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                  @click="removeCapturedPhoto"
                >
                  Remove Photo
                </button>
              </div>
            </div>

            <input
              ref="fallbackInput"
              type="file"
              accept=".jpg,.jpeg,.png"
              class="hidden"
              @change="handleProof"
            />
          </div>

          <p v-if="proofFile && !capturedPreview" class="mt-2 text-xs text-slate-500">Selected: {{ proofFile.name }}</p>

          <div class="mt-5 flex flex-col gap-3 sm:flex-row">
            <button
              v-if="canTimeIn"
              type="button"
              :disabled="submitting"
              class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-slate-300"
              @click="submitDtr('time_in')"
            >
              {{ submitting ? 'Submitting...' : 'Submit Time In' }}
            </button>
            <button
              v-if="canTimeOut"
              type="button"
              :disabled="submitting"
              class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-green-700 disabled:cursor-not-allowed disabled:bg-slate-300"
              @click="submitDtr('time_out')"
            >
              {{ submitting ? 'Submitting...' : 'Submit Time Out' }}
            </button>
            <button
              v-if="!canTimeIn && !canTimeOut"
              type="button"
              disabled
              class="inline-flex items-center justify-center rounded-lg bg-slate-300 px-4 py-2 text-sm font-semibold text-white"
            >
              {{ isAssigned ? 'DTR complete for today' : 'DTR unavailable' }}
            </button>
            <Link
              href="/beneficiary/work-outputs"
              class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-100"
            >
              Submit Daily Report
            </Link>
          </div>
        </div>
      </section>

      <section class="mt-6 rounded-lg border border-amber-200 bg-amber-50 p-5 shadow-sm">
        <h2 class="text-lg font-bold text-amber-950">Reminder</h2>
        <p class="mt-2 text-sm leading-6 text-amber-900">
          Incomplete, missing, or unclear DTR entries may delay SPES completion and payment processing. Submit your attendance on time and follow CPESO or employer corrections.
        </p>
      </section>

      <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5">
          <h2 class="text-lg font-bold text-slate-900">Attendance History</h2>
          <p class="mt-1 text-sm text-slate-500">Your submitted DTR records and review status.</p>
        </div>

        <div v-if="loading" class="p-8 text-center text-sm text-slate-500">Loading attendance history...</div>
        <div v-else-if="historyRows.length === 0" class="p-8 text-center text-sm text-slate-500">
          No attendance records yet.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">
              <tr>
                <th class="px-5 py-3">Date</th>
                <th class="px-5 py-3">Time in</th>
                <th class="px-5 py-3">Time out</th>
                <th class="px-5 py-3">Hours</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Remarks</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
              <tr v-for="record in historyRows" :key="record.id">
                <td class="px-5 py-4 font-semibold text-slate-900">{{ formatDate(record.date) }}</td>
                <td class="px-5 py-4 text-slate-600">{{ record.time_in || 'Missing' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ record.time_out || 'Missing' }}</td>
                <td class="px-5 py-4 text-slate-600">{{ formatHours(record.hours) }}</td>
                <td class="px-5 py-4">
                  <span class="rounded-full border px-3 py-1 text-xs font-semibold" :class="statusClasses(record.status)">
                    {{ statusLabel(record.status) }}
                  </span>
                </td>
                <td class="px-5 py-4 text-slate-600">{{ record.remarks || record.notes || 'No remarks posted.' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>
</template>
