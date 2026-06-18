<template>
  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg')"
  >
    <!-- OVERLAYS -->
    <div class="absolute inset-0 bg-slate-950/70"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 via-slate-900/50 to-transparent"></div>

    <div class="relative z-10 flex flex-col min-h-screen">

      <!-- ================= NAVBAR ================= -->
      <header class="sticky top-0 z-50 backdrop-blur-xl bg-white/10 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-6 py-4">

          <div class="flex justify-between items-center">

            <!-- LOGO -->
            <div class="flex items-center gap-3">
              <div class="bg-white p-2 rounded-xl shadow-lg">
                <img
                  src="/images/spes-logo.png"
                  class="w-10 h-10"
                />
              </div>

              <div>
                <h1 class="font-bold text-white text-lg">
                  SPES Management
                </h1>

                <p class="text-white/60 text-sm">
                  Student Employment Program
                </p>
              </div>
            </div>

            <!-- NAVIGATION -->
           <nav class="hidden md:flex gap-10 text-white font-medium">

  <button
    @click="activeTab = 'home'"
    :class="activeTab === 'home' ? 'text-blue-400' : 'text-white'"
    class="transition hover:text-blue-300"
  >
    Home
  </button>

  <button
    @click="activeTab = 'about'"
    :class="activeTab === 'about' ? 'text-blue-400' : 'text-white'"
    class="transition hover:text-blue-300"
  >
    About
  </button>

  <button
    @click="activeTab = 'manual'"
    :class="activeTab === 'manual' ? 'text-blue-400' : 'text-white'"
    class="transition hover:text-blue-300"
  >
    User Manual
  </button>

  <button
    @click="activeTab = 'contact'"
    :class="activeTab === 'contact' ? 'text-blue-400' : 'text-white'"
    class="transition hover:text-blue-300"
  >
    Contact
  </button>

</nav>
            <!-- ACTION BUTTONS -->
            <div class="flex gap-3">

              <Link
                v-if="canLogin"
                :href="route('login')"
                class="px-5 py-2 rounded-xl bg-white/10 border border-white/20 text-white hover:bg-white/20 transition"
              >
                Login
              </Link>

              <button
                v-if="canRegister"
                @click="openRegisterModal"
                class="px-5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white shadow-lg transition"
              >
                Register
              </button>

            </div>

          </div>
        </div>
      </header>
<!-- ================= CONTENT SWITCHER ================= -->
<section class="flex-1 flex items-center">

  <!-- HOME TAB -->
  <div
    v-if="activeTab === 'home'"
    class="max-w-7xl mx-auto px-6 w-full"
  >
    <div class="grid lg:grid-cols-2 gap-16 items-center">

      <div>

        <h1 class="text-white text-5xl lg:text-7xl font-extrabold leading-tight">
          Welcome to
          <span class="text-blue-500">
            SPES Management System
          </span>
        </h1>

        <p class="mt-8 text-lg text-slate-300 max-w-xl leading-relaxed">
          Simplify student applications, employer management,
          and PESO monitoring through one centralized system.
        </p>

        <div class="mt-10 flex flex-wrap gap-4">

          <Link
            :href="route('login')"
            class="px-8 py-4 bg-blue-600 hover:bg-blue-700 rounded-2xl text-white font-semibold shadow-xl transition hover:scale-105"
          >
            Get Started
          </Link>

          <button
            @click="openRegisterModal"
            class="px-8 py-4 border border-white/30 rounded-2xl text-white hover:bg-white/10 transition"
          >
            Register Now
          </button>

        </div>

      </div>

    </div>
  </div>

  <!-- IMPORTED PAGES -->
  <div
    v-else
    class="w-full px-6 py-10"
  >
    <component :is="tabs[activeTab]" />
  </div>

</section>

      <transition name="fade">
        <div
          v-if="showRegisterModal"
          class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 px-4 py-8"
        >
          <div class="w-full max-w-xl rounded-3xl border border-white/10 bg-slate-900/90 p-8 shadow-2xl backdrop-blur-xl">
            <div class="flex items-start justify-between gap-4">
              <div>
                <h2 class="text-3xl font-extrabold text-white">Register as</h2>
                <p class="mt-2 text-sm text-slate-400">
                  Choose whether you want to sign up as a beneficiary or an employer.
                </p>
              </div>

              <button
                @click="showRegisterModal = false"
                class="text-slate-400 hover:text-white"
                aria-label="Close dialog"
              >
                ✕
              </button>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
              <button
                @click="selectBeneficiary"
                class="w-full rounded-3xl bg-blue-600 px-6 py-4 text-white font-semibold shadow-lg hover:bg-blue-700 transition"
              >
                Beneficiary
              </button>

              <button
                @click="selectEmployer"
                class="w-full rounded-3xl bg-green-600 px-6 py-4 text-white font-semibold shadow-lg hover:bg-green-700 transition"
              >
                Employer
              </button>
            </div>
          </div>
        </div>
      </transition>

      <!-- ================= FEATURES ================= -->
      <section class="bg-black/30 backdrop-blur-lg border-t border-white/10">

        <div class="max-w-7xl mx-auto">

          <div class="grid md:grid-cols-3">

            <div class="p-8 border-r border-white/10 hover:bg-white/5 transition">
              <h3 class="text-white font-bold text-lg">
                Student Beneficiaries
              </h3>

              <p class="text-slate-300 mt-3">
                Apply, track applications, and manage requirements.
              </p>
            </div>

            <div class="p-8 border-r border-white/10 hover:bg-white/5 transition">
              <h3 class="text-white font-bold text-lg">
                Employers
              </h3>

              <p class="text-slate-300 mt-3">
                Manage and recruit applicants efficiently.
              </p>
            </div>

            <div class="p-8 hover:bg-white/5 transition">
              <h3 class="text-white font-bold text-lg">
                PESO Administration
              </h3>

              <p class="text-slate-300 mt-3">
                Simplify monitoring and generate reports faster.
              </p>
            </div>

          </div>

        </div>

      </section>

    </div>

  </div>
</template>

<script setup>
import { ref } from "vue"
import { Link } from "@inertiajs/vue3"
import { Inertia } from "@inertiajs/inertia"

import About from "@/Pages/About.vue"
import Contact from "@/Pages/Contact.vue"
import Manual from "@/Pages/Manual.vue"

defineProps({
  canLogin: Boolean,
  canRegister: Boolean,
})

const route = window.route

const showRegisterModal = ref(false)

const activeTab = ref("home")

const tabs = {
  about: About,
  manual: Manual,
  contact: Contact,
}

function openRegisterModal() {
  showRegisterModal.value = true
}

function selectBeneficiary() {
  Inertia.visit(route("register.beneficiary"))
}

function selectEmployer() {
  Inertia.visit(route("register.employer"))
}
</script>
