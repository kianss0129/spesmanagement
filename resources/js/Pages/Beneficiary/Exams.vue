<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-50 to-blue-100 p-6 relative">

    <!-- BACK BUTTON -->
    <button
      @click="goBack"
      class="absolute top-6 left-6 flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-md rounded-xl shadow-md hover:bg-white hover:shadow-lg transition group"
    >
      <svg xmlns="http://www.w3.org/2000/svg"
           class="w-5 h-5 text-gray-600 group-hover:text-indigo-600 transform group-hover:-translate-x-1 transition"
           fill="none"
           viewBox="0 0 24 24"
           stroke="currentColor">
        <path stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 19l-7-7 7-7" />
      </svg>

      <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-600">
        Back
      </span>
    </button>

    <div class="max-w-4xl mx-auto">

      <!-- HEADER -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
          Exams & Qualification
        </h1>
        <p class="text-gray-500 text-sm">
          View your scheduled exams and screening details
        </p>
      </div>

      <!-- EMPTY STATE -->
      <div
        v-if="!exams || exams.length === 0"
        class="bg-white p-8 rounded-2xl shadow text-center"
      >
        <div class="text-5xl mb-3">📄</div>
        <p class="text-gray-600 font-medium">
          No exams or screenings assigned yet
        </p>
        <p class="text-gray-400 text-sm mt-1">
          Check back later for updates
        </p>
      </div>

      <!-- EXAMS LIST -->
      <div v-else class="space-y-4">

        <div
          v-for="exam in exams"
          :key="exam.id"
          class="bg-white rounded-2xl shadow-md p-5 hover:shadow-lg transition flex justify-between items-center"
        >

          <!-- LEFT INFO -->
          <div class="space-y-1">
            <div class="text-lg font-semibold text-gray-800">
              Qualification Exam
            </div>

            <div class="text-sm text-gray-500">
              📍 {{ exam.location || 'No location provided' }}
            </div>

            <div class="text-sm text-gray-500">
              🗓 {{ formatDate(exam.exam_date) }}
            </div>
          </div>

          <!-- RIGHT STATUS BADGE -->
          <div>
            <span
              class="px-3 py-1 text-xs rounded-full font-semibold"
              :class="{
                'bg-yellow-100 text-yellow-700': !exam.result || exam.result === 'pending',
                'bg-green-100 text-green-700': exam.result === 'passed',
                'bg-red-100 text-red-700': exam.result === 'failed',
                'bg-indigo-100 text-indigo-700': exam.result === 'scheduled'
              }"
            >
              {{ exam.result ? exam.result.toUpperCase() : 'SCHEDULED' }}
            </span>
          </div>

        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

const props = defineProps({
  exams: {
    type: Array,
    default: () => []
  }
})

/* BACK BUTTON */
function goBack(){
  window.history.back()
}

/* FORMAT DATE */
function formatDate(date) {
  if (!date) return 'No date available'

  const d = new Date(date)
  if (isNaN(d)) return 'Invalid Date'

  return d.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'short',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  })
}
</script>

