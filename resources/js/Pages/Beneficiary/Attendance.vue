<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'

const attendance = ref([])
const loading = ref(false)

const form = ref({
  date: '',
  time_in: '',
  time_out: ''
})

const proofFile = ref(null)

/* MODAL */
const showModal = ref(false)
const selectedFile = ref(null)

/* TOAST */
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success')

function triggerToast(msg, type = 'success'){
  toastMessage.value = msg
  toastType.value = type
  showToast.value = true
  setTimeout(() => showToast.value = false, 2500)
}

/* 🔙 MODERN BACK BUTTON */
function goBack(){
  window.history.back()
  // router.visit('/dashboard') // optional
}

/* SUBMIT */
async function submitDTR(){
  try {
    loading.value = true

    const fd = new FormData()
    Object.entries(form.value).forEach(([k,v]) => fd.append(k,v))

    if(proofFile.value?.files[0]){
      fd.append('proof', proofFile.value.files[0])
    }

    await axios.post('/api/beneficiary/dtr', fd)

    triggerToast('DTR submitted successfully')

    form.value = { date: '', time_in: '', time_out: '' }
    proofFile.value.value = null

    fetchAttendance()

  } catch (e) {
    const message = e.response?.data?.message || 'Failed to submit DTR'
    triggerToast(message, 'error')
    console.error('DTR submit error:', e.response?.data || e)
  } finally {
    loading.value = false
  }
}

/* FETCH */
async function fetchAttendance(){
  const res = await axios.get('/api/beneficiary/attendance')
  attendance.value = res.data ?? []
}

/* MODAL */
function openFile(url){
  selectedFile.value = url
  showModal.value = true
}

function closeModal(){
  showModal.value = false
  selectedFile.value = null
}

onMounted(fetchAttendance)
</script>

<template>
  <div class="min-h-screen bg-gray-50 p-6">

    <!-- HEADER -->
    <div class="max-w-6xl mx-auto mb-6">

      <!-- 🔙 MODERN BACK BUTTON + TITLE -->
      <div class="flex items-center gap-4 mb-2">

        <button
          @click="goBack"
          class="flex items-center gap-2 px-3 py-2 bg-white shadow-md rounded-xl hover:bg-indigo-50 hover:shadow-lg transition group"
        >
          <!-- ICON -->
          <svg xmlns="http://www.w3.org/2000/svg"
               class="w-5 h-5 text-gray-600 group-hover:text-indigo-600 transition transform group-hover:-translate-x-1"
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

        <h1 class="text-3xl font-bold text-gray-800">Attendance & DTR</h1>
      </div>

      <p class="text-gray-500 text-sm">
        Track your daily time record and proof uploads
      </p>
    </div>

    <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- FORM CARD -->
      <div class="bg-white rounded-2xl shadow p-5">
        <h2 class="font-semibold text-gray-700 mb-4">Submit DTR</h2>

        <div class="space-y-3">
          <input v-model="form.date" type="date" class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-400"/>
          <input v-model="form.time_in" type="time" class="w-full p-2 border rounded-lg"/>
          <input v-model="form.time_out" type="time" class="w-full p-2 border rounded-lg"/>
          <input ref="proofFile" type="file" class="w-full text-sm"/>

          <button
            @click="submitDTR"
            :disabled="loading"
            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg transition"
          >
            {{ loading ? 'Submitting...' : 'Submit DTR' }}
          </button>
        </div>
      </div>

      <!-- TABLE CARD -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow p-5">

        <h2 class="font-semibold text-gray-700 mb-4">Attendance Records</h2>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left border-b text-gray-500">
                <th class="py-2">Date</th>
                <th>Status</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Proof</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="a in attendance" :key="a.id" class="border-b hover:bg-gray-50">
                <td class="py-2 font-medium">{{ a.date }}</td>

                <td>
                  <span class="px-2 py-1 text-xs rounded-full"
                    :class="a.status === 'present'
                      ? 'bg-green-100 text-green-700'
                      : 'bg-yellow-100 text-yellow-700'">
                    {{ a.status }}
                  </span>
                </td>

                <td>{{ a.time_in }}</td>
                <td>{{ a.time_out || '-' }}</td>

                <td>
                  <button
                    v-if="a.notes"
                    @click="openFile(`/storage/${a.notes}`)"
                    class="text-indigo-600 hover:underline text-sm"
                  >
                    View
                  </button>
                  <span v-else class="text-gray-400 text-xs">No file</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>

    <!-- MODAL -->
    <div v-if="showModal" class="fixed inset-0 bg-black/60 flex items-center justify-center z-50" @click.self="closeModal">
      <div class="bg-white rounded-2xl p-4 w-full max-w-3xl shadow-lg">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-semibold">Proof File</h2>
          <button @click="closeModal" class="text-red-500">✖</button>
        </div>

        <img :src="selectedFile" class="w-full max-h-[500px] object-contain rounded-lg"/>
      </div>
    </div>

    <!-- TOAST -->
    <div
      v-if="showToast"
      class="fixed bottom-6 right-6 px-4 py-3 rounded-xl text-white shadow-lg"
      :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'"
    >
      {{ toastMessage }}
    </div>

  </div>

</template>

