<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-6 relative overflow-hidden">

    <!-- DECORATIVE BLOBS -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">

      <div
        class="absolute w-96 h-96 bg-blue-400/30 blur-3xl rounded-full
               -top-24 -left-24 animate-pulse"
      ></div>

      <div
        class="absolute w-96 h-96 bg-cyan-300/30 blur-3xl rounded-full
               bottom-0 right-0 animate-pulse"
      ></div>

      <div
        class="absolute w-80 h-80 bg-indigo-300/20 blur-3xl rounded-full
               top-1/2 left-1/3 animate-ping"
      ></div>

    </div>

    <div class="relative z-10 max-w-3xl mx-auto">

      <!-- HEADER -->
      <div class="flex items-center justify-between mb-6">

        <div class="flex items-center gap-4">

          <!-- BACK BUTTON -->
          <button
            @click="goBack"
            class="flex items-center gap-2 px-4 py-2
                   bg-white shadow-md rounded-2xl
                   hover:bg-blue-100 transition group"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5 text-gray-700 group-hover:-translate-x-1 transition"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
              />
            </svg>

            <span class="text-sm font-medium text-gray-700">
              Back
            </span>
          </button>

          <div>
            <h1 class="text-3xl font-bold text-gray-800">
              Submit Report
            </h1>

            <p class="text-gray-600 text-sm">
              Send your daily or weekly report to PESO
            </p>
          </div>

        </div>

        <!-- HISTORY BUTTON -->
        <button
          @click="openHistory"
          class="bg-blue-600 hover:bg-blue-700
                 text-white px-5 py-2 rounded-2xl
                 shadow-lg transition"
        >
          View History
        </button>

      </div>

      <!-- CARD -->
      <div
        class="bg-white rounded-3xl shadow-2xl border border-blue-100 p-6"
      >

        <!-- TITLE -->
        <label class="text-sm font-medium text-gray-700">
          Title
        </label>

        <input
          v-model="title"
          type="text"
          placeholder="Enter report title..."
          class="w-full mt-1 mb-4 px-4 py-3 rounded-2xl
                 bg-gray-50 border border-gray-200
                 text-gray-800 placeholder-gray-400
                 focus:ring-2 focus:ring-blue-400
                 outline-none"
        />

        <!-- BODY -->
        <label class="text-sm font-medium text-gray-700">
          Report Details
        </label>

        <textarea
          v-model="body"
          placeholder="Write your report here..."
          class="w-full mt-1 h-48 px-4 py-3 rounded-2xl
                 bg-gray-50 border border-gray-200
                 text-gray-800 placeholder-gray-400
                 focus:ring-2 focus:ring-blue-400
                 outline-none resize-none"
        ></textarea>

        <!-- FILE -->
        <label class="text-sm font-medium text-gray-700 block mt-4">
          Work Output Proof
        </label>

        <input
          ref="fileInput"
          type="file"
          @change="handleFileChange"
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
          class="w-full mt-1 text-gray-700
                 file:mr-4 file:py-2 file:px-4
                 file:rounded-xl file:border-0
                 file:bg-blue-600 file:text-white
                 hover:file:bg-blue-700"
        />

        <p class="text-xs text-gray-500 mt-1">
          Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)
        </p>

        <!-- BUTTONS -->
        <div class="flex gap-3 mt-5">

          <!-- SUBMIT -->
          <button
            @click="submit"
            :disabled="loading"
            class="bg-gradient-to-r from-blue-500 to-cyan-500
                   hover:from-blue-600 hover:to-cyan-600
                   text-white px-5 py-2 rounded-2xl shadow-lg
                   transition disabled:opacity-50"
          >
            {{ loading ? 'Submitting...' : 'Submit Report' }}
          </button>

          <!-- RESET -->
          <button
            @click="resetForm"
            class="bg-gray-200 hover:bg-gray-300
                   text-gray-700 px-5 py-2 rounded-2xl transition"
          >
            Reset
          </button>

        </div>

        <!-- ERROR -->
        <p
          v-if="errorMessage"
          class="text-red-500 text-sm mt-3"
        >
          {{ errorMessage }}
        </p>

      </div>

    </div>

    <!-- HISTORY MODAL -->
    <div
      v-if="historyModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
      @click.self="historyModal = false"
    >

      <div
        class="bg-white rounded-3xl shadow-2xl
               w-full max-w-3xl p-6 max-h-[85vh] overflow-y-auto"
      >

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-5">

          <h2 class="text-2xl font-bold text-gray-800">
            Submitted Reports
          </h2>

          <button
            @click="historyModal = false"
            class="text-red-500 text-xl hover:scale-110 transition"
          >
            ✖
          </button>

        </div>

        <!-- EMPTY -->
        <div
          v-if="reports.length === 0"
          class="text-center text-gray-500 py-10"
        >
          No reports submitted yet.
        </div>

        <!-- REPORT LIST -->
        <div
          v-for="report in reports"
          :key="report.id"
          class="border border-gray-200 rounded-2xl p-4 mb-4
                 hover:shadow-md transition"
        >

          <div class="flex justify-between items-start mb-2">

            <div>
              <h3 class="font-bold text-lg text-gray-800">
                {{ report.title }}
              </h3>

              <p class="text-xs text-gray-500">
                {{ formatDate(report.created_at) }}
              </p>
            </div>

            <span
              class="px-3 py-1 rounded-full text-xs font-semibold
                     bg-blue-100 text-blue-700"
            >
              Submitted
            </span>

          </div>

          <p class="text-gray-700 whitespace-pre-line">
            {{ report.body }}
          </p>

          <!-- FILE -->
          <div
            v-if="report.file_path"
            class="mt-3"
          >
            <a
              :href="`/storage/${report.file_path}`"
              target="_blank"
              class="text-blue-600 hover:text-blue-800 text-sm underline"
            >
              View Attachment
            </a>
          </div>

        </div>

      </div>

    </div>

    <!-- TOAST -->
    <transition name="fade">

      <div
        v-if="showToast"
        class="fixed bottom-6 right-6 px-5 py-3 rounded-2xl
               text-white shadow-2xl z-50"
        :class="toastType === 'success'
          ? 'bg-green-600'
          : 'bg-red-600'"
      >
        {{ toastMessage }}
      </div>

    </transition>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const title = ref('')
const body = ref('')
const file = ref(null)

const loading = ref(false)
const errorMessage = ref('')

const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

const fileInput = ref(null)

/* HISTORY */
const historyModal = ref(false)
const reports = ref([])

/* =========================
   BACK
========================= */
function goBack() {
  window.history.back()
}

/* =========================
   TOAST
========================= */
function triggerToast(msg, type = 'success') {

  toastMessage.value = msg
  toastType.value = type
  showToast.value = true

  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

/* =========================
   FORMAT DATE
========================= */
function formatDate(date) {

  return new Date(date).toLocaleString([], {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/* =========================
   RESET FORM
========================= */
function resetForm() {

  title.value = ''
  body.value = ''
  file.value = ''
  errorMessage.value = ''

  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

/* =========================
   FILE CHANGE
========================= */
function handleFileChange(event) {

  const selectedFile = event.target.files[0]

  if (selectedFile && selectedFile.size > 10 * 1024 * 1024) {

    triggerToast('File size must be less than 10MB', 'error')

    event.target.value = ''
    file.value = null

    return
  }

  file.value = selectedFile || null
}

/* =========================
   OPEN HISTORY
========================= */
async function openHistory() {

  historyModal.value = true

  try {

    const response = await axios.get('/employer/reports/history')

    reports.value = response.data.reports || []

  } catch (error) {

    console.error(error)

    triggerToast('Failed to load reports history', 'error')
  }
}

/* =========================
   SUBMIT
========================= */
const submit = async () => {

  errorMessage.value = ''

  if (!title.value || !body.value) {

    errorMessage.value = 'Title and Body are required'

    triggerToast('Please fill all fields', 'error')

    return
  }

  try {

    loading.value = true

    const formData = new FormData()

    formData.append('title', title.value)
    formData.append('body', body.value)

    if (file.value) {
      formData.append('file', file.value)
    }

    const res = await axios.post(
      '/employer/reports',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    triggerToast(
      res.data.message || 'Report submitted successfully'
    )

    resetForm()

  } catch (err) {

    console.error(err)

    triggerToast('Failed to submit report', 'error')

  } finally {

    loading.value = false
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