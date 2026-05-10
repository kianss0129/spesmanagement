<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue'
import SectionBorder from '@/Components/SectionBorder.vue'
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'

const page = usePage()

const beneficiary = page.props.beneficiary
const ratings = page.props.ratings
const average = page.props.average
const sessions = page.props.sessions

const activeTab = ref('profile')

const activeClass =
  "text-left w-full px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold"

const inactiveClass =
  "text-left w-full px-4 py-2 rounded-lg hover:bg-gray-100"
</script>

<template>
<div class="min-h-screen bg-gray-100">

  <!-- HEADER -->
  <div class="bg-white shadow px-8 py-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-blue-700">
      Account Settings
    </h1>

    <Link
      :href="route('beneficiary.dashboard')"
      class="text-sm text-blue-600 hover:underline"
    >
      ← Back to Dashboard
    </Link>
  </div>

  <div class="flex">

    <!-- SIDEBAR -->
    <div class="w-64 bg-white border-r p-6 sticky top-0 h-screen">

      <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">
        Settings Menu
      </h2>

      <div class="flex flex-col space-y-2">

        <button
          @click="activeTab = 'profile'"
          :class="activeTab === 'profile' ? activeClass : inactiveClass">
          👤 Profile Information
        </button>

        <button
          @click="activeTab = 'beneficiary'"
          :class="activeTab === 'beneficiary' ? activeClass : inactiveClass">
          📄 Beneficiary Info
        </button>

        <button
          @click="activeTab = 'password'"
          :class="activeTab === 'password' ? activeClass : inactiveClass">
          🔒 Update Password
        </button>

        <button
          @click="activeTab = 'sessions'"
          :class="activeTab === 'sessions' ? activeClass : inactiveClass">
          💻 Browser Sessions
        </button>

        <button
          @click="activeTab = 'delete'"
          :class="activeTab === 'delete' ? activeClass : inactiveClass">
          🗑 Delete Account
        </button>

      </div>
    </div>

    <!-- CONTENT -->
    <div class="flex-1 p-10">
      <div class="bg-white p-8 rounded-2xl shadow-lg max-w-4xl">

        <!-- PROFILE INFO -->
        <div v-if="activeTab === 'profile'">
          <UpdateProfileInformationForm :user="$page.props.auth.user" />
        </div>

        <!-- BENEFICIARY INFO -->
        <div v-if="activeTab === 'beneficiary' && beneficiary">

          <h3 class="text-xl font-bold mb-4">Beneficiary Information</h3>

          <p><strong>Name:</strong> {{ beneficiary.name }}</p>
          <p><strong>Email:</strong> {{ beneficiary.email }}</p>
          <p><strong>Birthdate:</strong> {{ beneficiary.birthdate || 'N/A' }}</p>
          <p><strong>Gender:</strong> {{ beneficiary.gender || 'N/A' }}</p>
          <p><strong>Address:</strong> {{ beneficiary.address || 'N/A' }}</p>

          <div class="mt-4">
            <strong>Average Rating:</strong> {{ average || 0 }} / 5
          </div>

        </div>

        <!-- PASSWORD -->
        <div v-if="activeTab === 'password'">
          <UpdatePasswordForm />
        </div>

        <!-- SESSIONS -->
        <div v-if="activeTab === 'sessions'">
          <LogoutOtherBrowserSessionsForm :sessions="sessions" />
        </div>

        <!-- DELETE -->
        <div v-if="activeTab === 'delete'">
          <DeleteUserForm />
        </div>

      </div>
    </div>

  </div>
</div>
</template>