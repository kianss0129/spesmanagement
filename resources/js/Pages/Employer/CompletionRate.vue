```vue
<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'

const { props } = usePage()

const applications = ref(props.applications || {})
const loading = ref(false)
const selectedProfile = ref(null)
const completionModalOpen = ref(false)
const completionModalJob = ref(null)
const toast = ref({ show: false, message: '', color: 'green' })
const uploadingId = ref(null)

/* � TOAST */
function showToast(msg, color = 'green') {
  toast.value.message = msg
  toast.value.color = color
  toast.value.show = true
  setTimeout(() => { toast.value.show = false }, 4000)
}

/* �🔙 BACK */
function goBack() {
  window.history.back()
}

/* 👤 PROFILE */
function openProfile(app) {
  selectedProfile.value = app.beneficiary || null
}

function closeProfile() {
  selectedProfile.value = null
}

function formatDate(value) {
  return value ? new Date(value).toLocaleString([], {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }) : 'N/A'
}

/* ✅ UPDATE STATUS */
async function updateStatus(app, status) {

  try {

    app.updating = true

    await axios.post(`/employer/job-status/${app.id}`, {
      status: status
    })

    app.status = status

    if (status === 'completed') {
      completionModalJob.value = app
      completionModalOpen.value = true
    } else {
      alert('Job marked as ongoing.')
    }

  } catch (error) {

    console.error(error)
    alert('Failed to update status.')

  } finally {

    app.updating = false
  }
}

/* 📄 CERTIFICATE UPLOAD */
async function sendCertificate(app) {
  const fileInput = document.getElementById(`cert-${app.id}`)
  const file = fileInput?.files[0]

  if (!file) {
    showToast('Please select a file first', 'red')
    return
  }

  // Validate file type
  const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg']
  if (!allowedTypes.includes(file.type)) {
    showToast('Only PDF, JPG, and PNG files are allowed', 'red')
    return
  }

  // Validate file size (5MB max)
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

    showToast('Certificate uploaded successfully!', 'green')
    app.certificate_path = response.data.path
    fileInput.value = ''

  } catch (error) {
    console.error('Upload error:', error)
    const errorMsg = error.response?.data?.error || error.message || 'Failed to upload certificate'
    showToast(errorMsg, 'red')
  } finally {
    uploadingId.value = null
  }
}

/* 🎨 STATUS COLORS */
function statusColor(status) {

  if (status === 'pending') {
    return 'bg-yellow-100 text-yellow-700'
  }

  if (status === 'ongoing') {
    return 'bg-blue-100 text-blue-700'
  }

  if (status === 'completed') {
    return 'bg-green-100 text-green-700'
  }

  if (status === 'rejected') {
    return 'bg-red-100 text-red-700'
  }

  return 'bg-gray-100 text-gray-600'
}
</script>

<template>
<div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-8">

  <div class="max-w-6xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center gap-4 mb-8">

      <!-- BACK -->
      <button
        @click="goBack"
        class="flex items-center gap-2 px-3 py-2 bg-white shadow-md rounded-xl hover:bg-blue-100 hover:shadow-lg transition group"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 text-gray-600 group-hover:text-blue-600 transform group-hover:-translate-x-1 transition"
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

        <span class="text-sm font-medium text-gray-700 group-hover:text-blue-600">
          Back
        </span>
      </button>

      <h1 class="text-3xl font-bold text-gray-800">
        Beneficiary Completion Rate
      </h1>

    </div>

    <!-- EMPTY -->
    <div
      v-if="Object.keys(applications).length === 0"
      class="bg-white p-10 rounded-2xl shadow text-center text-gray-500"
    >
      No beneficiaries found.
    </div>

    <!-- JOB GROUP -->
    <div
      v-for="(apps, jobTitle) in applications"
      :key="jobTitle"
      class="mb-10"
    >

      <!-- JOB TITLE -->
      <h2 class="text-2xl font-bold text-blue-700 mb-5">
        {{ jobTitle }}
      </h2>

      <!-- GRID -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- CARD -->
        <div
          v-for="app in apps"
          :key="app.id"
          class="bg-white p-6 rounded-2xl shadow hover:shadow-xl transition duration-300"
        >

          <!-- PROFILE -->
          <div class="flex items-center gap-4 mb-4">

            <img
              :src="app.beneficiary?.profile_photo_url || '/default-profile.png'"
              class="w-16 h-16 rounded-full object-cover border-2 border-blue-200"
            />

            <div>

              <!-- NAME -->
              <p class="font-bold text-lg text-gray-800">
                {{ app.beneficiary?.first_name }}
                {{ app.beneficiary?.last_name }}
              </p>

              <!-- JOB -->
              <p class="text-sm text-blue-700 font-medium">
                {{ app.job?.title || jobTitle }}
              </p>

              <!-- DATE -->
              <p class="text-xs text-gray-500 mt-1">
                Applied:
                {{ new Date(app.created_at).toLocaleString() }}
              </p>

            </div>

          </div>

          <!-- STATUS -->
          <div class="mb-4">

            <span
              class="px-3 py-1 text-xs rounded-full font-semibold uppercase tracking-wide"
              :class="statusColor(app.status)"
            >
              {{ app.status }}
            </span>

          </div>

          <!-- VIEW PROFILE -->
          <button
            @click="openProfile(app)"
            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-sm transition mb-4"
          >
            View Profile
          </button>

          <!-- CERTIFICATE -->
          <div class="mb-4">

            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Certificate of Completion
            </label>

            <input
              :id="`cert-${app.id}`"
              type="file"
              accept=".pdf,.jpg,.jpeg,.png"
              class="w-full border border-gray-300 rounded-xl p-2 text-sm"
            />

            <!-- SEND BUTTON -->
            <button
              @click="sendCertificate(app)"
              :disabled="uploadingId === app.id"
              class="w-full mt-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white py-2 rounded-xl text-sm transition font-medium"
            >
              {{ uploadingId === app.id ? 'Uploading...' : 'Send Certificate' }}
            </button>

            <!-- VIEW CERTIFICATE -->
            <div v-if="app.certificate_path" class="mt-3">

              <a
                :href="`/storage/${app.certificate_path}`"
                target="_blank"
                class="text-blue-600 text-sm underline hover:text-blue-800"
              >
                ✓ View Uploaded Certificate
              </a>

            </div>

          </div>

          <!-- ACTION BUTTONS -->
          <div class="grid grid-cols-2 gap-3">

            <!-- ONGOING -->
            <button
              @click="updateStatus(app, 'ongoing')"
              class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-xl transition font-medium"
              :disabled="app.updating"
            >
              Ongoing
            </button>

            <!-- COMPLETE -->
            <button
              @click="updateStatus(app, 'completed')"
              class="bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl transition font-medium"
              :disabled="app.updating"
            >
              Complete
            </button>

          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- 👤 PROFILE MODAL -->
  <div
    v-if="selectedProfile"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    @click.self="closeProfile"
  >

    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">

      <!-- HEADER -->
      <div class="flex justify-between items-center mb-5">

        <h2 class="text-2xl font-bold text-gray-800">
          Beneficiary Information
        </h2>

        <button
          @click="closeProfile"
          class="text-red-500 text-xl hover:scale-110 transition"
        >
          ✖
        </button>

      </div>

      <!-- PROFILE -->
      <div class="text-center">

        <img
          :src="selectedProfile.profile_photo_url || '/default-profile.png'"
          class="w-28 h-28 rounded-full mx-auto mb-4 object-cover border-4 border-blue-200"
        />

        <h3 class="text-xl font-bold text-gray-800">
          {{ selectedProfile.name || `${selectedProfile.first_name || ''} ${selectedProfile.last_name || ''}`.trim() || 'Beneficiary' }}
        </h3>

        <p class="text-gray-500 text-sm mt-1">
          {{ selectedProfile.email || 'No email available' }}
        </p>

      </div>

      <!-- DETAILS -->
      <div class="mt-6 bg-gray-50 rounded-xl p-4 text-sm text-gray-700">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="font-semibold text-gray-800">Name</p>
            <p>{{ selectedProfile.name || `${selectedProfile.first_name || ''} ${selectedProfile.last_name || ''}`.trim() || 'N/A' }}</p>
          </div>

          <div>
            <p class="font-semibold text-gray-800">Email</p>
            <p>{{ selectedProfile.email || 'N/A' }}</p>
          </div>

          <div>
            <p class="font-semibold text-gray-800">Phone</p>
            <p>{{ selectedProfile.phone || 'N/A' }}</p>
          </div>

          <div>
            <p class="font-semibold text-gray-800">Category</p>
            <p>{{ selectedProfile.category || selectedProfile.user?.category || selectedProfile.user?.beneficiary_type || 'N/A' }}</p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <p class="font-semibold text-gray-800">School</p>
            <p>{{ selectedProfile.school?.name || selectedProfile.school || 'N/A' }}</p>
          </div>

          <div>
            <p class="font-semibold text-gray-800">Submission Date</p>
            <p>{{ formatDate(selectedProfile.created_at) }}</p>
          </div>
        </div>
      </div>

    </div>

  </div>

  <!-- COMPLETION MODAL -->
  <div
    v-if="completionModalOpen"
    class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
    @click.self="completionModalOpen = false"
  >
    <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl">
      <h2 class="text-xl font-bold text-gray-900 mb-3">Job Completed</h2>
      <p class="text-gray-600 mb-4">
        {{ completionModalJob?.beneficiary?.first_name || 'The applicant' }} {{ completionModalJob?.beneficiary?.last_name || '' }} has been marked as completed.
      </p>
      <div class="flex justify-end gap-3">
        <button
          @click="completionModalOpen = false"
          class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300"
        >
          Close
        </button>
      </div>
    </div>
  </div>

  <!-- TOASTER -->
  <div
    v-if="toast.show"
    :class="`fixed top-6 right-6 px-4 py-3 rounded-lg text-white font-medium shadow-lg z-50 animate-fade-in bg-${toast.color}-600`"
  >
    {{ toast.message }}
  </div>

</div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-in-out;
}
</style>
```
