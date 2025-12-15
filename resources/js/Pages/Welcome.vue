<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gray-100">
    <h1 class="text-3xl font-bold mb-6">Welcome to Laravel</h1>
    <p class="mb-6">Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})</p>

    <div class="flex space-x-4">
      <!-- Login Button -->
      <inertia-link
        v-if="canLogin"
        :href="route('login')"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Login
      </inertia-link>

      <!-- Register Dropdown -->
      <div class="relative" v-if="canRegister">
        <button
          @click="toggleDropdown"
          class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        >
          Register
        </button>
        <ul
          v-show="dropdownOpen"
          class="absolute mt-2 w-40 bg-white border rounded shadow-lg"
        >
          <li>
            <inertia-link
              :href="route('register.beneficiary')"
              class="block px-4 py-2 hover:bg-gray-100"
              @click="closeDropdown"
            >
              Beneficiary
            </inertia-link>
          </li>
          <li>
            <inertia-link
              :href="route('register.employer')"
              class="block px-4 py-2 hover:bg-gray-100"
              @click="closeDropdown"
            >
              Employer
            </inertia-link>
          </li>
          <li>
            <inertia-link
              :href="route('register.peso')"
              class="block px-4 py-2 hover:bg-gray-100"
              @click="closeDropdown"
            >
              PESO
            </inertia-link>
          </li>
        </ul>
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
/* Optional: smoother dropdown */
ul {
  transition: all 0.2s ease-in-out;
}
</style>
