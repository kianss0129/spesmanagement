<script setup>
import { usePage } from '@inertiajs/vue3'
import { ref } from 'vue'

const { applications } = usePage().props

/* 📄 MODAL */
const showCertificateModal = ref(false)
const selectedCertificate = ref(null)

/* STATUS */
function statusColor(app) {

  if (app.is_assigned)
    return 'bg-green-100 text-green-700 border-green-200'

  if (app.status === 'completed')
    return 'bg-emerald-100 text-emerald-700 border-emerald-200'

  if (app.status === 'ongoing')
    return 'bg-blue-100 text-blue-700 border-blue-200'

  if (app.status === 'exam')
    return 'bg-blue-100 text-blue-700 border-blue-200'

  if (app.status === 'interview')
    return 'bg-purple-100 text-purple-700 border-purple-200'

  if (app.status === 'rejected')
    return 'bg-red-100 text-red-600 border-red-200'

  return 'bg-gray-100 text-gray-600 border-gray-200'
}

/* 🔙 BACK */
function goBack() {

  window.history.length > 1
    ? window.history.back()
    : router.visit('/beneficiary')
}

/* 📄 VIEW CERTIFICATE */
function viewCertificate(path) {

  selectedCertificate.value = path
  showCertificateModal.value = true
}

/* ❌ CLOSE MODAL */
function closeModal() {

  showCertificateModal.value = false
  selectedCertificate.value = null
}
</script>

<template>
<div class="p-6 max-w-6xl mx-auto">

  <!-- BACK BUTTON -->
  <button
    @click="goBack"
    class="flex items-center gap-2 mb-6 px-4 py-2 bg-white/70 backdrop-blur
           hover:bg-white shadow rounded-xl text-sm font-medium
           transition active:scale-95"
  >
    <span class="text-lg">←</span>
    Back
  </button>

  <!-- HEADER -->
  <div class="mb-8">

    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">
      My Applications
    </h1>

    <p class="text-sm text-gray-500 mt-1">
      Track your job journey in one place
    </p>

  </div>

  <!-- EMPTY -->
  <div
    v-if="!applications.length"
    class="bg-white border border-gray-100 p-12 rounded-2xl shadow-sm text-center"
  >
    <p class="text-gray-500 text-sm">
      No applications yet
    </p>
  </div>

  <!-- GRID -->
  <div
    v-else
    class="grid md:grid-cols-2 lg:grid-cols-3 gap-6"
  >

    <!-- CARD -->
    <div
      v-for="app in applications"
      :key="app.id"
      class="group bg-white border border-gray-100 rounded-2xl p-5
             hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
    >

      <!-- TOP -->
      <div class="flex items-start justify-between gap-3">

        <div>

          <h2
            class="text-lg font-semibold text-gray-800 group-hover:text-blue-600 transition"
          >
            {{ app.job_title }}
          </h2>

          <p class="text-sm text-gray-500 mt-1">
            🏢 {{ app.employer }}
          </p>

        </div>

        <!-- STATUS -->
        <span
          class="text-xs font-semibold px-3 py-1 rounded-full border"
          :class="statusColor(app)"
        >
          {{ app.is_assigned ? 'Assigned' : app.status }}
        </span>

      </div>

      <!-- DIVIDER -->
      <div class="my-4 border-t border-gray-100"></div>

      <!-- META -->
      <div class="flex items-center justify-between text-xs text-gray-400 mb-4">

        <span>Application ID</span>

        <span class="font-medium text-gray-500">
          #{{ app.id }}
        </span>

      </div>

      <!-- BUTTONS -->
      <div class="flex flex-col gap-3">

        <!-- ✅ CERTIFICATE BUTTON -->
        <button
          v-if="app.certificate_path"
          @click="viewCertificate(app.certificate_path)"
          class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-xl text-sm font-medium transition"
        >
          View Certificate
        </button>

      </div>

    </div>

  </div>

  <!-- 📄 CERTIFICATE MODAL -->
  <div
    v-if="showCertificateModal"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4"
    @click.self="closeModal"
  >

    <div
      class="bg-white rounded-2xl w-full max-w-5xl h-[85vh] overflow-hidden shadow-2xl"
    >

      <!-- HEADER -->
      <div class="flex items-center justify-between p-4 border-b">

        <h2 class="text-xl font-bold text-gray-800">
          Certificate of Completion
        </h2>

        <button
          @click="closeModal"
          class="text-red-500 text-2xl hover:scale-110 transition"
        >
          ✖
        </button>

      </div>

      <!-- CONTENT -->
      <div class="w-full h-full bg-gray-100">

        <!-- PDF -->
        <iframe
          v-if="selectedCertificate.endsWith('.pdf')"
          :src="`/storage/${selectedCertificate}`"
          class="w-full h-full"
        ></iframe>

        <!-- IMAGE -->
        <div
          v-else
          class="flex items-center justify-center h-full p-4"
        >

          <img
            :src="`/storage/${selectedCertificate}`"
            class="max-w-full max-h-full rounded-xl shadow-lg"
          />

        </div>

      </div>

    </div>

  </div>

</div>
</template>
