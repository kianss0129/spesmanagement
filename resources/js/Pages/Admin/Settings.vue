<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import ChangePassword from '@/Pages/Profile/Partials/ChangePassword.vue'
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'
import SectionBorder from '@/Components/SectionBorder.vue'

const page = usePage()
const sessions = page.props.sessions ?? []

const activeTab = ref('profile')

const activeClass =
  "block px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold"

const inactiveClass =
  "block px-4 py-2 rounded-lg hover:bg-blue-100"
</script>

<template>
<div class="min-h-screen bg-gray-100 flex flex-col">

  <!-- HEADER -->
  <div class="bg-white shadow px-8 py-5 flex items-center justify-between">
    <h1 class="text-2xl font-bold text-blue-700">
      Account Settings
    </h1>

   <Link
     :href="route('dashboard')"
  class="text-sm text-blue-600 hover:underline"
>
  ← Back to Dashboard
</Link>
  </div>

  <div class="flex flex-1">

    <!-- SIDEBAR -->
    <div class="w-64 bg-white shadow-md p-6 border-r sticky top-0 h-screen">

      <h2 class="text-sm font-semibold text-gray-500 uppercase mb-4">
        Settings Menu
      </h2>

      <nav class="space-y-2">

        <button
          @click="activeTab = 'profile'"
          :class="activeTab === 'profile' ? activeClass : inactiveClass"
        >
          👤 Profile Information
        </button>

        <button
          @click="activeTab = 'password'"
          :class="activeTab === 'password' ? activeClass : inactiveClass"
        >
          🔒 Update Password
        </button>

       

        <button
          @click="activeTab = 'delete'"
          :class="activeTab === 'delete' ? activeClass : inactiveClass"
        >
          🗑 Delete Account
        </button>

      </nav>

    </div>

    <!-- CONTENT -->
    <div class="flex-1 p-10">

      <div class="bg-white p-8 rounded-2xl shadow-lg max-w-4xl">

        <!-- PROFILE TAB -->
        <div v-if="activeTab === 'profile'">
          <UpdateProfileInformationForm :user="$page.props.auth.user" />
          <SectionBorder />
        </div>

        <!-- PASSWORD TAB -->
        <div v-if="activeTab === 'password'">
  <ChangePassword />
</div>

        <!-- SESSIONS TAB -->
        <div v-if="activeTab === 'sessions'">
          <LogoutOtherBrowserSessionsForm :sessions="sessions" />
        </div>

        <!-- DELETE TAB -->
        <div v-if="activeTab === 'delete'">
          <DeleteUserForm />
        </div>

      </div>

    </div>

  </div>

</div>
</template>