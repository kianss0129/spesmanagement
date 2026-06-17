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
  'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg shadow-blue-500/30 scale-[1.02]'

const inactiveClass =
  'text-slate-600 hover:bg-blue-100/70 hover:text-slate-900'
</script>

<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
  >

    <!-- BACKGROUND -->
    <div
      class="absolute inset-0
             bg-gradient-to-br
             from-[#dbeafe]
             via-[#e0f2fe]
             to-[#c7d2fe]"
    />

    <!-- SOFT GLOW -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">

      <div
        class="absolute w-[420px] h-[420px]
               bg-blue-400/30 blur-3xl rounded-full
               -top-32 -left-32"
      />

      <div
        class="absolute w-[420px] h-[420px]
               bg-cyan-300/30 blur-3xl rounded-full
               bottom-0 right-0"
      />

    </div>

    <div class="relative z-10 min-h-screen flex flex-col">

      <!-- HEADER -->
      <div
        class="backdrop-blur-xl bg-white/50
               border-b border-white/40
               px-6 md:px-10 py-5
               flex flex-col md:flex-row
               md:items-center md:justify-between gap-4"
      >

        <div>
          <h1
            class="text-3xl font-extrabold
                   text-slate-800 tracking-tight"
          >
            Account Settings
          </h1>

          <p class="text-slate-600 mt-1">
            Manage your profile, security, and account preferences.
          </p>
        </div>

        <!-- DASHBOARD BUTTON -->
        <Link
          :href="route('dashboard')"
          class="inline-flex items-center gap-2
                 bg-white/80 hover:bg-white
                 border border-white/60
                 text-slate-700
                 px-5 py-3 rounded-2xl
                 shadow-md hover:shadow-lg
                 transition duration-300"
        >
          ← Back to Dashboard
        </Link>

      </div>

      <!-- MAIN -->
      <div class="flex flex-1 flex-col md:flex-row">

        <!-- SIDEBAR -->
        <aside
          class="w-full md:w-72
                 backdrop-blur-2xl
                 bg-white/40
                 border-r border-white/40
                 md:min-h-screen p-6"
        >

          <h2
            class="text-xs font-bold
                   tracking-[0.2em]
                   text-blue-600 uppercase mb-5"
          >
            Settings Menu
          </h2>

          <nav class="space-y-3">

            <!-- PROFILE -->
            <button
              @click="activeTab = 'profile'"
              :class="[
                activeTab === 'profile'
                  ? activeClass
                  : inactiveClass,
                'w-full text-left px-5 py-4 rounded-2xl transition-all duration-300 font-semibold'
              ]"
            >
              👤 Profile Information
            </button>

            <!-- PASSWORD -->
            <button
              @click="activeTab = 'password'"
              :class="[
                activeTab === 'password'
                  ? activeClass
                  : inactiveClass,
                'w-full text-left px-5 py-4 rounded-2xl transition-all duration-300 font-semibold'
              ]"
            >
              🔒 Update Password
            </button>

          

            <!-- DELETE -->
            <button
              @click="activeTab = 'delete'"
              :class="[
                activeTab === 'delete'
                  ? 'bg-red-500 text-white shadow-lg shadow-red-500/30'
                  : 'text-red-500 hover:bg-red-100/80',
                'w-full text-left px-5 py-4 rounded-2xl transition-all duration-300 font-semibold'
              ]"
            >
              🗑 Delete Account
            </button>

          </nav>

        </aside>

        <!-- CONTENT -->
        <main class="flex-1 p-5 md:p-10">

          <div
            class="max-w-5xl mx-auto
                   bg-white/55 backdrop-blur-2xl
                   border border-white/60
                   rounded-3xl shadow-2xl
                   p-6 md:p-10"
          >

            <!-- PROFILE -->
            <transition name="fade" mode="out-in">
              <div
                v-if="activeTab === 'profile'"
                key="profile"
              >

                <div class="mb-8">
                  <h2 class="text-2xl font-bold text-slate-800">
                    Profile Information
                  </h2>

                  <p class="text-slate-600 mt-1">
                    Update your personal account details.
                  </p>
                </div>

                <UpdateProfileInformationForm
                  :user="$page.props.auth.user"
                />

                <SectionBorder />

              </div>
            </transition>

            <!-- PASSWORD -->
            <transition name="fade" mode="out-in">
              <div
                v-if="activeTab === 'password'"
                key="password"
              >

                <div class="mb-8">
                  <h2 class="text-2xl font-bold text-slate-800">
                    Update Password
                  </h2>

                  <p class="text-slate-600 mt-1">
                    Ensure your account stays secure.
                  </p>
                </div>

                <ChangePassword />

              </div>
            </transition>

            <!-- SESSIONS -->
            <transition name="fade" mode="out-in">
              <div
                v-if="activeTab === 'sessions'"
                key="sessions"
              >

                <div class="mb-8">
                  <h2 class="text-2xl font-bold text-slate-800">
                    Active Sessions
                  </h2>

                  <p class="text-slate-600 mt-1">
                    Manage devices currently logged into your account.
                  </p>
                </div>

                <LogoutOtherBrowserSessionsForm
                  :sessions="sessions"
                />

              </div>
            </transition>

            <!-- DELETE -->
            <transition name="fade" mode="out-in">
              <div
                v-if="activeTab === 'delete'"
                key="delete"
              >

                <div class="mb-8">
                  <h2 class="text-2xl font-bold text-red-500">
                    Delete Account
                  </h2>

                  <p class="text-slate-600 mt-1">
                    Permanently remove your account and all associated data.
                  </p>
                </div>

                <DeleteUserForm />

              </div>
            </transition>

          </div>

        </main>

      </div>

    </div>
  </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: all 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.fade-enter-to,
.fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}
</style>