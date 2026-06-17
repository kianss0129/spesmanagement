<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
  profilePhoto: {
    type: String,
    default: '/images/default-profile.png',
  },
  menuOpen: Boolean,
  toggleMenu: Function,
  logout: Function,
})
</script>

<template>
  <div
    class="sticky top-0 z-[100] overflow-visible bg-white/90 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-4 shadow-sm"
  >
    <div class="flex items-center justify-between gap-4">

      <!-- LEFT -->
      <div class="min-w-0">
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500">Overview and quick access for your role.</p>
      </div>

      <!-- RIGHT -->
      <div class="flex items-center gap-3">

        <!-- NOTIFICATION BELL -->
        <Link
          href="/peso/notifications"
          title="Notifications"
          class="relative w-10 h-10 rounded-full bg-white border border-gray-200 shadow-sm hover:bg-blue-50 hover:border-blue-300 flex items-center justify-center transition"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        </Link>

        <!-- PROFILE BUTTON -->
        <div ref="profileMenu" class="relative inline-block">
          <button
            type="button"
            @click.stop="toggleMenu"
            class="flex items-center justify-center rounded-full overflow-hidden border border-gray-300 bg-white shadow-sm hover:shadow-md transition"
          >
            <img
              :src="profilePhoto"
              alt="Profile"
              class="w-10 h-10 object-cover"
            />
          </button>

          <!-- DROPDOWN -->
          <transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
          >
            <div
              v-if="menuOpen"
              class="absolute right-0 top-12 w-48 bg-white rounded-2xl shadow-2xl border border-gray-200 z-[9999] overflow-hidden"
              @click.stop
            >
              <a
                href="/admin/settings"
                class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition"
              >
                Profile Settings
              </a>
              <button
                type="button"
                @click="logout"
                class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition"
              >
                Logout
              </button>
            </div>
          </transition>
        </div>

      </div>
    </div>
  </div>
</template>
