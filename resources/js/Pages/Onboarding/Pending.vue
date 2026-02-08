<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="bg-white p-8 rounded-xl shadow max-w-md text-center">

      <h2 class="text-xl font-semibold mb-3">
        ⏳ Application Submitted
      </h2>

      <p class="text-gray-600 mb-4">
        Please wait for approval from PESO.
      </p>

      <p class="text-sm text-yellow-600 font-semibold mb-6">
        Status: Pending Approval
      </p>

      <!-- Back to Home Button -->
      <button
        type="button"
        @click="goHome"
        class="w-full bg-red-500 text-white py-2 rounded-lg hover:bg-red-600 transition"
      >
        Back to Home Page
      </button>

    </div>
  </div>
</template>

<script setup>
import { Inertia } from '@inertiajs/inertia'

const goHome = () => {
  Inertia.visit('/')
}
</script>
