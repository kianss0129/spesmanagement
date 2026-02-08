<template>
  <div class="min-h-screen bg-blue-600">

    <!-- Main Container (FULL SCREEN) -->
    <div class="w-full h-screen bg-white flex flex-col">

      <!-- Header -->
      <div class="bg-indigo-600 text-white p-10 text-center">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
          Welcome to SPES Management System
        </h1>
        <p class="text-lg opacity-90">
          Special Program for the Employment of Students
        </p>
      </div>

      <!-- Content -->
      <div class="flex-1 p-8 md:p-10 grid md:grid-cols-2 gap-8 bg-gray-100">

        <!-- About SPES Box -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700">
            About SPES
          </h2>
          <p class="text-gray-600 leading-relaxed mb-4">
            <strong>Special Program for the Employment of Students (SPES)</strong>
            is a government initiative that helps students earn income while gaining
            real-world work experience during school breaks.
          </p>
          <p class="text-gray-600 leading-relaxed">
            This system simplifies registration, application tracking, employer
            coordination, and PESO monitoring in one secure platform.
          </p>
        </div>

        <!-- System Features Box -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
          <h2 class="text-2xl font-semibold mb-4 text-indigo-700">
            System Features
          </h2>

          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">
                Student (Beneficiary) online registration
              </span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">
                Employer job posting and applicant review
              </span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">
                PESO dashboard, validation, and analytics
              </span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">
                Secure role-based access control
              </span>
            </li>
          </ul>
        </div>

      </div>

      <!-- Actions -->
      <div class="bg-gray-50 p-6 flex justify-between items-center">
        <p class="text-gray-700">
          Access the system by logging in or registering as a new user.
        </p>

        <div class="flex gap-3">
          <Link
            v-if="canLogin"
            :href="route('login')"
            class="px-6 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition"
          >
            Login
          </Link>

          <button
            v-if="canRegister"
            @click="openRegisterModal"
            class="px-6 py-3 rounded-lg border border-indigo-600 text-indigo-600 hover:bg-indigo-50 transition"
          >
            Register
          </button>
        </div>
      </div>

    </div>

    <!-- Register Type Modal -->
    <div
      v-if="showRegisterModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-xl p-6 w-96 relative">
        <h3 class="text-xl font-semibold mb-4 text-center">
          Choose Registration Type
        </h3>

        <div class="flex justify-around gap-4">
          <button
            @click="selectBeneficiary"
            class="flex flex-col items-center p-4 border rounded-lg hover:bg-indigo-50 transition w-32"
          >
            <svg class="w-10 h-10 text-indigo-600 mb-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M10 10a3 3 0 100-6 3 3 0 000 6zM2 18a8 8 0 0116 0H2z"/>
            </svg>
            Beneficiary
          </button>

          <button
            @click="selectEmployer"
            class="flex flex-col items-center p-4 border rounded-lg hover:bg-indigo-50 transition w-32"
          >
            <svg class="w-10 h-10 text-indigo-600 mb-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M3 3h14v14H3V3z"/>
            </svg>
            Employer
          </button>
        </div>

        <button
          @click="closeRegisterModal"
          class="absolute top-2 right-2 text-lg font-bold"
        >
          ×
        </button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import { Link } from '@inertiajs/vue3'

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})

const route = window.route
const showRegisterModal = ref(false)

function openRegisterModal() {
  showRegisterModal.value = true
}

function closeRegisterModal() {
  showRegisterModal.value = false
}

function selectBeneficiary() {
  closeRegisterModal()
  Inertia.visit(route('register.beneficiary'))
}

function selectEmployer() {
  closeRegisterModal()
  Inertia.visit(route('register.employer'))
}
</script>
