<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-6">

    <div class="max-w-3xl mx-auto">

      <!-- HEADER -->
      <div class="flex items-center gap-4 mb-6">

        <!-- 🔙 MODERN BACK BUTTON -->
        <button
          @click="goBack"
          class="flex items-center gap-2 px-3 py-2 bg-white shadow-md rounded-xl hover:bg-blue-100 hover:shadow-lg transition group"
        >
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-gray-600 group-hover:text-blue-600 transform group-hover:-translate-x-1 transition"
               fill="none"
               viewBox="0 0 24 24"
               stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7" />
          </svg>

          <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">
            Back
          </span>
        </button>

        <div>
          <h1 class="text-3xl font-bold text-gray-800">Submit Report</h1>
          <p class="text-gray-500 text-sm">Send your daily or weekly report to PESO</p>
        </div>

      </div>

      <!-- CARD -->
      <div class="bg-white shadow-lg rounded-2xl p-6">

        <!-- TITLE -->
        <label class="text-sm text-gray-600">Title</label>
        <input
          v-model="title"
          type="text"
          placeholder="Enter report title..."
          class="w-full mt-1 mb-4 border rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
        />

        <!-- BODY -->
        <label class="text-sm text-gray-600">Report Details</label>
        <textarea
          v-model="body"
          placeholder="Write your report here..."
          class="w-full mt-1 h-48 border rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-400 outline-none"
        ></textarea>

        <!-- FILE UPLOAD -->
        <label class="text-sm text-gray-600 block mt-4">Work Output Proof (Optional)</label>
        <input
          ref="fileInput"
          type="file"
          @change="handleFileChange"
          accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
          class="w-full mt-1 border rounded-xl px-4 py-2 focus:ring-2 focus:ring-blue-400 outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
        />
        <p class="text-xs text-gray-500 mt-1">Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max: 10MB)</p>

        <!-- BUTTONS -->
        <div class="flex gap-3 mt-5">

          <button
            @click="submit"
            :disabled="loading"
            class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl shadow transition disabled:opacity-50"
          >
            {{ loading ? 'Submitting...' : 'Submit Report' }}
          </button>

          <button
            @click="resetForm"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-xl transition"
          >
            Reset
          </button>

        </div>

        <!-- ERROR -->
        <p v-if="errorMessage" class="text-red-500 text-sm mt-3">
          {{ errorMessage }}
        </p>

      </div>

    </div>

    <!-- TOAST -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 px-5 py-3 rounded-xl text-white shadow-lg"
      :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      {{ toastMessage }}
    </div>

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

/* 🔙 BACK */
function goBack(){
  window.history.back()
}

/* TOAST */
function triggerToast(msg, type = 'success') {
  toastMessage.value = msg
  toastType.value = type
  showToast.value = true
  setTimeout(() => (showToast.value = false), 3000)
}

/* RESET */
function resetForm() {
  title.value = ''
  body.value = ''
  file.value = null
  errorMessage.value = ''
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

/* HANDLE FILE CHANGE */
function handleFileChange(event) {
  const selectedFile = event.target.files[0]
  if (selectedFile) {
    // Check file size (10MB limit)
    if (selectedFile.size > 10 * 1024 * 1024) {
      triggerToast('File size must be less than 10MB', 'error')
      event.target.value = ''
      file.value = null
      return
    }
    file.value = selectedFile
  } else {
    file.value = null
  }
}

/* SUBMIT */
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

    const res = await axios.post('/employer/reports', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    triggerToast(res.data.message || 'Report submitted successfully')
    resetForm()

  } catch (err) {
    console.error(err.response)
    triggerToast('Failed to submit report', 'error')
  } finally {
    loading.value = false
  }
}
</script>