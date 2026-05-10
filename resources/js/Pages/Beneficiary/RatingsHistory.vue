BENE SIDE 
RATINGHISTORY.VUE



<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-blue-100 p-6 relative">


    <!-- BACK -->
    <button
      @click="goBack"
      class="absolute top-6 left-6 flex items-center gap-2 px-4 py-2 bg-white/80 backdrop-blur-md rounded-xl shadow-sm hover:bg-white hover:shadow-md transition"
    >
      ← Back
    </button>


    <div class="max-w-7xl mx-auto">


      <!-- HEADER -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Ratings History</h1>
        <p class="text-sm text-gray-500">
          Employer performance feedback and evaluation
        </p>
      </div>


      <!-- 📊 SUMMARY CARD (NEW - IMPORTANT) -->
      <div v-if="ratings.length" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">


        <div class="bg-white p-4 rounded-xl shadow">
          <p class="text-xs text-gray-500">Avg Punctuality</p>
          <p class="text-xl font-bold text-blue-600">
            {{ avg('punctuality') }}
          </p>
        </div>


        <div class="bg-white p-4 rounded-xl shadow">
          <p class="text-xs text-gray-500">Avg Work</p>
          <p class="text-xl font-bold text-blue-600">
            {{ avg('work_quality') }}
          </p>
        </div>


        <div class="bg-white p-4 rounded-xl shadow">
          <p class="text-xs text-gray-500">Avg Attitude</p>
          <p class="text-xl font-bold text-blue-600">
            {{ avg('attitude') }}
          </p>
        </div>


        <div class="bg-white p-4 rounded-xl shadow">
          <p class="text-xs text-gray-500">Overall Score</p>
          <p class="text-xl font-bold text-green-600">
            {{ avg('overall') }}
          </p>
        </div>


      </div>


      <!-- EMPTY -->
      <div v-if="ratings.length === 0"
        class="bg-white p-10 rounded-xl text-center text-gray-400">
        No employer ratings yet
      </div>


      <!-- TABLE -->
      <div v-else class="bg-white rounded-2xl shadow overflow-hidden">


        <div class="px-6 py-4 border-b bg-gray-50">
          <p class="font-semibold text-gray-600 text-sm">
            Employer Feedback Overview
          </p>
        </div>


        <table class="w-full text-sm">


          <thead class="text-xs text-gray-500 uppercase">
            <tr>
              <th class="p-4 text-left">Employer</th>
              <th class="p-4 text-center">Punctuality</th>
              <th class="p-4 text-center">Work</th>
              <th class="p-4 text-center">Attitude</th>
              <th class="p-4 text-center">Comm</th>
              <th class="p-4 text-center">Overall</th>
              <th class="p-4 text-left">Comment</th>
              <th class="p-4 text-center">Date</th>
            </tr>
          </thead>


          <tbody>


            <tr v-for="r in ratings" :key="r.id"
              class="border-t hover:bg-blue-50 transition">


              <!-- Employer -->
              <td class="p-4 font-semibold text-gray-700">
                {{ r.employer_name ?? r.employer?.company_name ?? 'Unknown' }}
              </td>


              <!-- Ratings -->
              <td class="p-4 text-center">⭐ {{ r.punctuality ?? 0 }}</td>
              <td class="p-4 text-center">⭐ {{ r.work_quality ?? 0 }}</td>
              <td class="p-4 text-center">⭐ {{ r.attitude ?? 0 }}</td>
              <td class="p-4 text-center">⭐ {{ r.communication ?? 0 }}</td>


              <!-- Overall Highlight -->
              <td class="p-4 text-center">
                <span
                  :class="r.overall >= 4 ? 'bg-green-100 text-green-700'
                        : r.overall >= 3 ? 'bg-yellow-100 text-yellow-700'
                        : 'bg-red-100 text-red-700'"
                  class="px-3 py-1 rounded-full font-bold text-xs"
                >
                  ⭐ {{ r.overall ?? 0 }}
                </span>
              </td>


              <!-- Comment -->
              <td class="p-4 text-gray-600 max-w-xs truncate">
                {{ r.comment || 'No comment' }}
              </td>


              <!-- Date -->
              <td class="p-4 text-center text-gray-400 text-xs">
                {{ formatDate(r.created_at) }}
              </td>


            </tr>


          </tbody>


        </table>


      </div>


    </div>
  </div>
</template>


<script setup>
import { defineProps } from 'vue'


const props = defineProps({
  ratings: { type: Array, default: () => [] }
})


function goBack() {
  window.history.back()
}


function formatDate(date) {
  return date ? new Date(date).toLocaleDateString() : ''
}


/* 🔥 NEW: AVERAGE CALCULATOR (IMPORTANT FOR PANEL REQUIREMENT) */
function avg(field) {
  if (!props.ratings.length) return 0


  const sum = props.ratings.reduce((acc, r) => {
    return acc + (r[field] ?? 0)
  }, 0)


  return (sum / props.ratings.length).toFixed(1)
}
</script>

