<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-600">

    <!-- HERO SECTION -->
    <div class="text-white text-center py-20 px-6">
      <h1 class="text-4xl md:text-5xl font-extrabold mb-4">
        SPES Management System
      </h1>
      <p class="text-lg md:text-xl opacity-90 max-w-2xl mx-auto">
        Empowering students through employment opportunities while simplifying
        management for employers and PESO administrators.
      </p>

      <div class="mt-8 flex justify-center gap-4">
        <Link
          v-if="canLogin"
          :href="route('login')"
          class="px-8 py-3 rounded-xl bg-white text-indigo-600 font-semibold shadow hover:scale-105 transition"
        >
          Login
        </Link>

        <button
          v-if="canRegister"
          @click="openRegisterModal"
          class="px-8 py-3 rounded-xl border border-white text-white hover:bg-white hover:text-indigo-600 transition"
        >
          Get Started
        </button>
      </div>
    </div>

    <!-- FEATURES SECTION -->
    <div class="bg-white py-16 px-6 md:px-12">
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-12">
        System Features
      </h2>

      <div class="grid md:grid-cols-3 gap-8">
        <div class="p-6 rounded-xl shadow hover:shadow-lg transition">
          <h3 class="text-xl font-semibold mb-2 text-indigo-600">Student Portal</h3>
          <p class="text-gray-600">
            Easy registration and application tracking for beneficiaries.
          </p>
        </div>

        <div class="p-6 rounded-xl shadow hover:shadow-lg transition">
          <h3 class="text-xl font-semibold mb-2 text-indigo-600">Employer Access</h3>
          <p class="text-gray-600">
            Post jobs and manage applicants efficiently.
          </p>
        </div>

        <div class="p-6 rounded-xl shadow hover:shadow-lg transition">
          <h3 class="text-xl font-semibold mb-2 text-indigo-600">PESO Dashboard</h3>
          <p class="text-gray-600">
            Monitor, validate, and analyze program performance.
          </p>
        </div>
      </div>
    </div>

    <!-- ABOUT SECTION -->
    <div class="bg-gray-100 py-16 px-6 md:px-12 text-center">
      <h2 class="text-3xl font-bold text-gray-800 mb-6">About SPES</h2>
      <p class="max-w-3xl mx-auto text-gray-600 leading-relaxed">
        The Special Program for the Employment of Students (SPES) provides
        opportunities for students to gain work experience and earn income
        during school breaks. This system digitizes the entire process—from
        registration to monitoring—making it faster, transparent, and secure.
      </p>
    </div>

    <!-- FOOTER -->
    <div class="bg-indigo-700 text-white text-center py-6">
      <p class="text-sm opacity-80">
        © 2026 SPES Management System | All Rights Reserved
      </p>
    </div>

    <!-- REGISTER MODAL -->
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
            👨‍🎓
            <span>Beneficiary</span>
          </button>

          <button
            @click="selectEmployer"
            class="flex flex-col items-center p-4 border rounded-lg hover:bg-indigo-50 transition w-32"
          >
            🏢
            <span>Employer</span>
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
