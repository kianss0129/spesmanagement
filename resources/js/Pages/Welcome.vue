<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center px-6">
    <div class="max-w-5xl w-full bg-white rounded-2xl shadow-xl overflow-hidden">
      <!-- Header / Hero -->
      <div class="bg-indigo-600 text-white p-10 text-center">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
          Welcome to SPES Management System
        </h1>
        <p class="text-lg opacity-90">
          Special Program for the Employment of Students
        </p>       
      </div>

      <!-- Content -->
      <div class="p-8 md:p-10 grid md:grid-cols-2 gap-8">
        <!-- Left: About -->
        <div>
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            About SPES
          </h2>
          <p class="text-gray-600 leading-relaxed mb-4">
            The <strong>Special Program for the Employment of Students (SPES)</strong>
            is a government initiative that helps students earn income while
            gaining real-world work experience during school breaks.
          </p>
          <p class="text-gray-600 leading-relaxed">
            This system simplifies registration, application tracking, employer
            coordination, and PESO monitoring in one secure platform.
          </p>
        </div>

        <!-- Right: Features -->
        <div>
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">
            System Features
          </h2>
          <ul class="space-y-3">
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">Student (Beneficiary) online registration</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">Employer job posting and applicant review</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">PESO dashboard, validation, and analytics</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="text-indigo-600 font-bold">✔</span>
              <span class="text-gray-700">Secure role-based access control</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-gray-50 p-8 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-gray-700 text-center md:text-left">
          Access the system by logging in or registering as a new user.
        </p>

        <div class="flex gap-3 items-center">
          <!-- Login -->
          <InertiaLink
            v-if="canLogin"
            :href="route('login')"
            class="px-6 py-3 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition"
          >
            Login
          </InertiaLink>

          <!-- Register Dropdown -->
          <div class="relative" v-if="canRegister">
            <button
              @click="toggleDropdown"
              class="px-6 py-3 rounded-lg border border-indigo-600 text-indigo-600 font-medium hover:bg-indigo-50 transition"
            >
              Register
            </button>

            <ul
              v-show="dropdownOpen"
              class="absolute right-0 mt-2 w-44 bg-white border rounded-lg shadow-lg z-10"
            >
              <li>
                <InertiaLink
                  :href="route('register.beneficiary')"
                  class="block px-4 py-2 hover:bg-gray-100"
                  @click="closeDropdown"
                >
                  Beneficiary
                </InertiaLink>
              </li>
              <li>
                <InertiaLink
                  :href="route('register.employer')"
                  class="block px-4 py-2 hover:bg-gray-100"
                  @click="closeDropdown"
                >
                  Employer
                </InertiaLink>
              </li>
              <li>
                <InertiaLink
                  :href="route('register.peso')"
                  class="block px-4 py-2 hover:bg-gray-100"
                  @click="closeDropdown"
                >
                  PESO
                </InertiaLink>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link as InertiaLink } from '@inertiajs/inertia-vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
  laravelVersion: String,
  phpVersion: String,
});

const dropdownOpen = ref(false);

function toggleDropdown() {
  dropdownOpen.value = !dropdownOpen.value;
}

function closeDropdown() {
  dropdownOpen.value = false;
}
</script>

<style scoped>
ul {
  transition: all 0.2s ease-in-out;
}
</style>