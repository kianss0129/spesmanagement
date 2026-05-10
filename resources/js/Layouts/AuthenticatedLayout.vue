<template>
  <div>
    <!-- Topbar -->
    <div class="flex justify-between items-center bg-white shadow px-6 py-4 relative">

      <!-- LEFT: Search -->
      <div class="relative w-1/3">
        <input
          v-model="search"
          @focus="showSearch = true"
          type="text"
          placeholder="Search..."
          class="w-full border rounded-lg px-4 py-2"
        />

        <!-- Search Dropdown -->
        <div v-if="showSearch" class="absolute bg-white shadow w-full mt-2 rounded-lg p-3 z-50">
          <p class="text-sm text-gray-500" v-if="search === ''">
            Type to search...
          </p>
          <p v-else class="text-sm">
            Searching for: <strong>{{ search }}</strong>
          </p>
        </div>
      </div>

      <!-- RIGHT: Icons -->
      <div class="flex items-center gap-6">

        <!-- Notification Bell -->
        <div class="relative">
          <button @click="toggleNotifications">
            🔔
          </button>

          <!-- Notification Dropdown -->
          <div
            v-if="showNotifications"
            class="absolute right-0 mt-2 w-72 bg-white shadow rounded-lg p-4 z-50"
          >
            <h3 class="font-semibold mb-2">Notifications</h3>

            <div v-if="$page.props.notifications.length === 0" class="text-sm text-gray-500">
              No notifications
            </div>

            <div
              v-for="notification in $page.props.notifications"
              :key="notification.id"
              class="text-sm border-b py-2"
            >
              {{ notification.data.message ?? 'New Notification' }}
            </div>
          </div>
        </div>

        <!-- Profile -->
        <div class="relative">
          <button @click="toggleProfile">
            <img
  :src="$page.props.auth.user.profile_photo_url"
  alt="Profile Photo"
  class="w-10 h-10 rounded-full object-cover border"
/>
          </button>

          <!-- Profile Dropdown -->
          <div
            v-if="showProfile"
            class="absolute right-0 mt-2 w-48 bg-white shadow rounded-lg p-3 z-50"
          >
            <p class="text-sm font-semibold">
              {{ $page.props.auth.user.name }}
            </p>
            <p class="text-xs text-gray-500 mb-2">
              {{ $page.props.auth.user.email }}
            </p>

            <button
              @click="logout"
              class="text-red-600 text-sm hover:underline"
            >
              Logout
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Layout -->
    <div class="flex">
      <aside class="w-64 bg-gray-800 text-white min-h-screen p-4">
        <ul class="space-y-2">
          <li><Link href="/admin/dashboard">Dashboard</Link></li>
          <li><Link href="/peso/beneficiaries/pending">Pending Beneficiaries</Link></li>
          <li><Link href="/peso/employers/pending">Pending Employers</Link></li>
        </ul>
      </aside>

      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const search = ref('')
const showSearch = ref(false)
const showNotifications = ref(false)
const showProfile = ref(false)

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value
  showProfile.value = false
}

const toggleProfile = () => {
  showProfile.value = !showProfile.value
  showNotifications.value = false
}

const logout = () => {
  router.post('/logout')
}
</script>