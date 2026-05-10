<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'

import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import ChangePassword from '@/Pages/Profile/Partials/ChangePassword.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'
import SectionBorder from '@/Components/SectionBorder.vue'

const page = usePage()
const sessions = page.props.sessions ?? []

const activeTab = ref('profile')
const ratings = ref([])

const activeClass =
  "block px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold"

const inactiveClass =
  "block px-4 py-2 rounded-lg hover:bg-blue-100"

/* ================================
   LOAD RATINGS
================================ */
async function loadRatings() {
  try {
    const res = await axios.get('/api/beneficiary/ratings')
    ratings.value = res.data || []
  } catch (err) {
    console.error('Failed to load ratings', err)
    ratings.value = []
  }
}

onMounted(() => {
  loadRatings()
})
</script>

<template>
<div class="min-h-screen bg-gray-100 flex flex-col">

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

        <!-- RATINGS TAB -->
        <div v-if="activeTab === 'ratings'">
          <h2 class="text-xl font-semibold mb-6">Your Ratings</h2>

          <div v-if="ratings.length === 0" class="text-gray-500">
            No ratings yet.
          </div>

          <div
            v-for="r in ratings"
            :key="r.id"
            class="mb-6 p-5 border rounded-xl shadow-sm"
          >
            <!-- Company Name -->
            <p class="font-semibold text-lg">
              {{ r.company_name }}
            </p>

            <!-- Stars -->
            <div class="flex text-yellow-400 text-lg mt-2">
              <span v-for="i in 5" :key="i">
                <span v-if="i <= r.score">★</span>
                <span v-else class="text-gray-300">★</span>
              </span>
            </div>

            <!-- Comment -->
            <p v-if="r.comment" class="text-sm text-gray-600 mt-3">
              {{ r.comment }}
            </p>

            <!-- Date -->
            <p class="text-xs text-gray-400 mt-2">
              {{ new Date(r.created_at).toLocaleDateString() }}
            </p>
          </div>
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