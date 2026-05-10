<script setup>
import { ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const records = ref(page.props.records || [])

/* 🔙 BACK */
function goBack(){
  window.history.back()
}

// FILTERS
const selectedDay = ref('')
const selectedMonth = ref('')
const selectedYear = ref('')

// Toast
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

// Modal
const showModal = ref(false)
const selectedProof = ref(null)

function triggerToast(msg, type = 'success') {
  toastMessage.value = msg
  toastType.value = type
  showToast.value = true
  setTimeout(() => (showToast.value = false), 3000)
}

function openProof(proof) {
  selectedProof.value = proof
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedProof.value = null
}

function reloadPage() {
  window.location.reload()
  triggerToast('Refreshing data...')
}

const filteredRecords = computed(() => {
  return records.value.filter(r => {
    const date = new Date(r.date)

    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const year = String(date.getFullYear())

    const matchDay = selectedDay.value ? selectedDay.value === day : true
    const matchMonth = selectedMonth.value ? selectedMonth.value === month : true
    const matchYear = selectedYear.value ? selectedYear.value === year : true

    return matchDay && matchMonth && matchYear
  })
})
</script>

<template>
  <div class="p-6 max-w-6xl mx-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-start mb-6">

      <!-- LEFT: BACK + TITLE -->
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

        <div>
          <h1 class="text-3xl font-bold text-gray-800">Attendance / DTR</h1>
          <p class="text-gray-500 text-sm">View submitted daily time records</p>
        </div>

      </div>

      <!-- RIGHT -->
      <button
        @click="reloadPage"
        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg shadow"
      >
        Refresh
      </button>

    </div>

    <!-- FILTERS -->
    <div class="bg-white p-4 rounded-2xl shadow mb-6">

      <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">
          Filter Attendance
        </h2>

        <button
          @click="selectedDay = ''; selectedMonth = ''; selectedYear = ''"
          class="text-xs text-blue-600 hover:underline"
        >
          Reset
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
          <label class="text-xs text-gray-500">Day</label>
          <select v-model="selectedDay"
                  class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <option value="">All Days</option>
            <option v-for="d in 31" :key="d" :value="String(d).padStart(2,'0')">
              Day {{ d }}
            </option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-500">Month</label>
          <select v-model="selectedMonth"
                  class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400">
            <option value="">All Months</option>
            <option value="01">January</option>
            <option value="02">February</option>
            <option value="03">March</option>
            <option value="04">April</option>
            <option value="05">May</option>
            <option value="06">June</option>
            <option value="07">July</option>
            <option value="08">August</option>
            <option value="09">September</option>
            <option value="10">October</option>
            <option value="11">November</option>
            <option value="12">December</option>
          </select>
        </div>

        <div>
          <label class="text-xs text-gray-500">Year</label>
          <input v-model="selectedYear"
                 type="number"
                 placeholder="e.g. 2026"
                 class="w-full mt-1 border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-400" />
        </div>

      </div>
    </div>

    <!-- EMPTY -->
    <div v-if="filteredRecords.length === 0"
         class="text-center py-16 text-gray-400">
      📭 No attendance records found
    </div>

    <!-- CARDS -->
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="a in filteredRecords"
           :key="a.id"
           class="bg-white rounded-xl shadow-md p-5 hover:shadow-lg transition">

        <div class="text-lg font-semibold text-gray-800">
          {{ a.beneficiary_name }}
        </div>

        <div class="text-sm text-gray-500 mt-1">
          📅 {{ a.date }}
        </div>

        <div class="text-sm text-gray-700 mt-1">
          ⏰ {{ a.time_in }} - {{ a.time_out }}
        </div>

        <div v-if="a.proof" class="mt-3">
          <button @click="openProof(a.proof)"
                  class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm hover:bg-blue-200">
            📎 View Proof
          </button>
        </div>

        <div v-else class="text-xs text-gray-400 mt-2">
          No proof uploaded
        </div>

      </div>
    </div>

    <!-- MODAL -->
    <div v-if="showModal"
         class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">

      <div class="bg-white w-full max-w-2xl rounded-xl p-4 relative">

        <button @click="closeModal"
                class="absolute top-2 right-3 text-gray-500">
          ✖
        </button>

        <h2 class="text-lg font-semibold mb-3">Proof Preview</h2>

        <img v-if="selectedProof && (selectedProof.includes('.png') || selectedProof.includes('.jpg') || selectedProof.includes('.jpeg'))"
             :src="selectedProof"
             class="w-full rounded-lg" />

        <iframe v-else
                :src="selectedProof"
                class="w-full h-[500px] rounded-lg"></iframe>

      </div>
    </div>

    <!-- TOAST -->
    <div v-if="showToast"
         class="fixed bottom-6 right-6 px-5 py-3 rounded-lg text-white shadow-lg"
         :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'">
      {{ toastMessage }}
    </div>

  </div>
</template>