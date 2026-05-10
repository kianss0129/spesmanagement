<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import axios from 'axios'

const { props } = usePage()

const applications = ref(props.applications || {})
const loading = ref(false)
const selectedProfile = ref(null)
const showSubmissionModal = ref(false)
const submissionMessage = ref('')
const submissionType = ref('success')

/* 🔙 BACK */
function goBack(){
  window.history.back()
}

/* SAFE INIT */
watch(
  applications,
  (newVal) => {
    if (!newVal) return

    Object.values(newVal).forEach(apps => {
      apps.forEach(app => {
        if (!app.ratings) {
          app.ratings = {
            punctuality: 5,
            work_quality: 5,
            work_attitude: 5,
            communication: 5,
            overall: 5,
            comment: ''
          }
        }
        app.submitting = false
      })
    })
  },
  { immediate: true, deep: true }
)

/* PROFILE */
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
    minute: '2-digit'
  }) : 'N/A'
}

/* SUBMIT RATING */
async function submitRating(app) {
  app.submitting = true
  try {
    await axios.post(`/employer/ratings`, {
      application_id: app.id,
      beneficiary_id: app.beneficiary_id,
      punctuality: Number(app.ratings.punctuality),
      work_quality: Number(app.ratings.work_quality),
      work_attitude: Number(app.ratings.work_attitude),
      communication: Number(app.ratings.communication),
      overall: Number(app.ratings.overall),
      comment: (app.ratings.comment || '').trim() || 'No comment',
    })

    submissionMessage.value = `Rating submitted for ${app.beneficiary?.first_name}`
    submissionType.value = 'success'
    showSubmissionModal.value = true
    
    setTimeout(() => {
      showSubmissionModal.value = false
    }, 3000)
  } catch (err) {
    submissionMessage.value = 'Failed to submit rating. Please try again.'
    submissionType.value = 'error'
    showSubmissionModal.value = true
    
    setTimeout(() => {
      showSubmissionModal.value = false
    }, 3500)
  } finally {
    app.submitting = false
  }
}

/* STATUS COLORS */
function statusColor(status) {
  if (status === 'pending') return 'bg-yellow-100 text-yellow-700'
  if (status === 'selected' || status === 'assigned') return 'bg-green-100 text-green-700'
  if (status === 'rejected') return 'bg-red-100 text-red-700'
  return 'bg-gray-100 text-gray-600'
}
</script>

<template>
<div class="min-h-screen bg-gradient-to-br from-blue-100 to-blue-200 p-8">

  <div class="max-w-6xl mx-auto">

    <!-- HEADER -->
    <div class="flex items-center gap-4 mb-8">
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

      <h1 class="text-3xl font-bold text-gray-800">
        Applicants Per Job
      </h1>
    </div>

    <!-- EMPTY -->
    <div v-if="Object.keys(applications).length === 0"
         class="bg-white p-10 rounded-2xl shadow text-center text-gray-500">
      No applicants yet.
    </div>

    <!-- JOB GROUP -->
    <div v-for="(apps, jobTitle) in applications"
         :key="jobTitle"
         class="mb-10">

      <h2 class="text-xl font-bold text-blue-700 mb-4">
        {{ jobTitle }}
      </h2>

      <div class="grid md:grid-cols-2 gap-6">

        <!-- CARD -->
        <div v-for="app in apps"
             :key="app.id"
             class="bg-white p-6 rounded-2xl shadow hover:shadow-lg transition">

          <!-- PROFILE -->
          <div class="flex items-center gap-4 mb-3">
            <img :src="app.beneficiary?.profile_photo_url || '/default-profile.png'"
                 class="w-14 h-14 rounded-full object-cover border" />

            <div>
              <p class="font-semibold">
                {{ app.beneficiary?.first_name }}
                {{ app.beneficiary?.last_name }}
              </p>
              <p class="text-xs text-gray-500">
                {{ new Date(app.created_at).toLocaleString() }}
              </p>
            </div>
          </div>

          <!-- STATUS -->
          <span class="px-3 py-1 text-xs rounded-full"
                :class="statusColor(app.status)">
            {{ app.status }}
          </span>

          <!-- VIEW PROFILE -->
          <div class="mt-3">
            <button @click="openProfile(app)"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg text-sm transition">
              View Profile
            </button>
          </div>

          <!-- RATINGS -->
          <div class="mt-4 space-y-3">

            <div v-for="field in ['punctuality','work_quality','work_attitude','communication','overall']"
                 :key="field">
              <label class="text-xs text-gray-600 capitalize">
                {{ field.replace('_',' ') }}
              </label>
              <select v-model="app.ratings[field]"
                      class="w-full border rounded-lg p-2 text-sm">
                <option v-for="n in 5" :value="n">{{ n }}</option>
              </select>
            </div>

            <textarea v-model="app.ratings.comment"
                      placeholder="Write comment..."
                      class="w-full border rounded-lg p-2 text-sm"></textarea>

            <button @click="submitRating(app)"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition"
                    :disabled="app.submitting">
              {{ app.submitting ? 'Submitting...' : 'Submit Rating' }}
            </button>

          </div>

        </div>

      </div>
    </div>

  </div>

  <!-- ✅ PROFILE MODAL -->
  <div v-if="selectedProfile"
       class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
       @click.self="closeProfile">

    <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-lg">

      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Applicant Profile</h2>
        <button @click="closeProfile" class="text-red-500 text-lg">✖</button>
      </div>

      <div class="text-center">
        <img
          :src="selectedProfile.profile_photo_url || '/default-profile.png'"
          class="w-24 h-24 rounded-full mx-auto mb-3 object-cover border"
        />

        <h3 class="text-lg font-semibold">
          {{ selectedProfile.name || `${selectedProfile.first_name || ''} ${selectedProfile.last_name || ''}`.trim() || 'Beneficiary' }}
        </h3>

        <p class="text-gray-500 text-sm">
          {{ selectedProfile.email || 'No email available' }}
        </p>
      </div>

      <div class="mt-4 text-sm text-gray-700">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">Name</p>
            <p class="font-medium">{{ selectedProfile.name || `${selectedProfile.first_name || ''} ${selectedProfile.last_name || ''}`.trim() || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">Email</p>
            <p class="font-medium">{{ selectedProfile.email || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">Phone</p>
            <p class="font-medium">{{ selectedProfile.phone || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">Category</p>
            <p class="font-medium">{{ selectedProfile.category || selectedProfile.user?.category || selectedProfile.user?.beneficiary_type || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">School</p>
            <p class="font-medium">{{ selectedProfile.school?.name || selectedProfile.school || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-gray-600 text-xs uppercase tracking-wide">Submission Date</p>
            <p class="font-medium">{{ formatDate(selectedProfile.created_at) }}</p>
          </div>
        </div>
      </div>

    </div>
  </div>

  <!-- 📋 SUBMISSION MODAL -->
  <transition name="modal-fade">
    <div v-if="showSubmissionModal"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm"
         @click="showSubmissionModal = false">

      <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl animate-modal-pop">

        <!-- SUCCESS STATE -->
        <div v-if="submissionType === 'success'" class="text-center">
          <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-green-500 flex items-center justify-center shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="w-10 h-10 text-white"
                   fill="none"
                   viewBox="0 0 24 24"
                   stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7" />
              </svg>
            </div>
          </div>

          <h3 class="text-2xl font-bold text-gray-800 mb-2">Success!</h3>
          <p class="text-gray-600">{{ submissionMessage }}</p>
        </div>

        <!-- ERROR STATE -->
        <div v-else class="text-center">
          <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-400 to-red-500 flex items-center justify-center shadow-lg">
              <svg xmlns="http://www.w3.org/2000/svg"
                   class="w-10 h-10 text-white"
                   fill="none"
                   viewBox="0 0 24 24"
                   stroke="currentColor">
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>
          </div>

          <h3 class="text-2xl font-bold text-gray-800 mb-2">Oops!</h3>
          <p class="text-gray-600">{{ submissionMessage }}</p>
        </div>

        <!-- ACTION BUTTON -->
        <button @click="showSubmissionModal = false"
                :class="[
                  'w-full mt-8 py-3 rounded-xl font-semibold text-white transition-all duration-200 hover:scale-105 active:scale-95',
                  submissionType === 'success' 
                    ? 'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700' 
                    : 'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700'
                ]">
          {{ submissionType === 'success' ? 'Great!' : 'Try Again' }}
        </button>

      </div>

    </div>
  </transition>

</div>
</template>

<style scoped>
/* Modal Animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

@keyframes modalPop {
  0% {
    transform: scale(0.7) translateY(30px);
    opacity: 0;
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.animate-modal-pop {
  animation: modalPop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
</style>