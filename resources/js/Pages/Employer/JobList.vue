<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  jobs: Array
})

// Modal control
const showModal = ref(false)
const jobToDelete = ref(null)

// Toast control
const showToast = ref(false)
const toastMessage = ref('')

// 🔙 BACK
function goBack() {
  window.history.back()
}

// Open delete modal
function openModal(job) {
  jobToDelete.value = job
  showModal.value = true
}

// Close modal
function closeModal() {
  showModal.value = false
  jobToDelete.value = null
}

// Delete job
function deleteJob() {
  if (!jobToDelete.value) return
  router.delete(`/employer/jobs/${jobToDelete.value.id}`, {
    onSuccess: () => {
      showToast.value = true
      toastMessage.value = `Job "${jobToDelete.value.title}" deleted successfully!`
      closeModal()
      setTimeout(() => { showToast.value = false }, 3000)
    }
  })
}
</script>

<template>
  <div class="min-h-screen bg-blue-50 p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">

      <!-- LEFT SIDE (BACK + TITLE) -->
      <div class="flex items-center gap-4">

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

        <h1 class="text-3xl font-bold text-blue-900">
          My Job Listings
        </h1>
      </div>

      <!-- RIGHT SIDE -->
      <a
        href="/employer/jobs/create"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow transition duration-200"
      >
        + New Job
      </a>
    </div>

    <!-- TABLE CARD -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <table class="w-full text-left border-collapse">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-4 py-2 text-left">Job ID</th>
            <th class="p-4 font-semibold">Title</th>
            <th class="p-4 font-semibold">Location</th>
            <th class="p-4 font-semibold">Slots</th>
            <th class="p-4 font-semibold">Closing</th>
            <th class="p-4 font-semibold text-center">Actions</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="job in jobs" :key="job.id" class="border-b hover:bg-blue-50 transition">
            <td class="p-4 font-medium text-gray-800">{{ job.id }}</td> 
            <td class="p-4 font-medium text-gray-800">{{ job.title }}</td>
            <td class="p-4 text-gray-600">{{ job.location }}</td>
            <td class="p-4 text-gray-600">{{ job.slots }}</td>
            <td class="p-4 text-gray-600">{{ job.closing_date }}</td>
            <td class="p-4 flex justify-center gap-3">
              <a
                :href="`/employer/jobs/${job.id}/edit`"
                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded-md text-sm transition"
              >
                Edit
              </a>
              <button
                @click="openModal(job)"
                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm transition"
              >
                Delete
              </button>
            </td>
          </tr>

          <tr v-if="!jobs || jobs.length === 0">
            <td colspan="6" class="text-center p-8 text-gray-400">
              No job listings yet.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- DELETE MODAL -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl p-6 w-96 shadow-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Delete Job</h2>

        <p class="text-gray-700 mb-6">
          Are you sure you want to delete
          <span class="font-semibold">{{ jobToDelete?.title }}</span>?
        </p>

        <div class="flex justify-end gap-3 mb-4">
          <button
            @click="closeModal"
            class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition"
          >
            Cancel
          </button>

          <button
            @click="deleteJob"
            class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 transition"
          >
            Delete
          </button>
        </div>

        <!-- VIEW APPLICANTS -->
        <a
          :href="`/employer/jobs/${jobToDelete?.id}`"
          class="text-blue-600 hover:underline text-sm"
        >
          View Applicants
        </a>

      </div>
    </div>

    <!-- TOAST -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 bg-green-600 text-white px-5 py-3 rounded-lg shadow-lg transition"
    >
      {{ toastMessage }}
    </div>

  </div>
</template>