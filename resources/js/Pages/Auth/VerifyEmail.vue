<script setup>
import { computed } from 'vue'
import {
  Head,
  Link,
  useForm,
  usePage,
} from '@inertiajs/vue3'

const props = defineProps({
  status: String,
})

const form = useForm({})

const submit = () => {
  form.post(route('verification.send'))
}

const verificationLinkSent = computed(() => {
  return props.status === 'verification-link-sent'
})

const alreadyVerified = computed(() => {
  return props.status === 'already-verified'
})

const userEmail = computed(() => {
  return usePage().props.auth.user.email
})
</script>

<template>
  <Head title="Email Verification - SPES System" />

  <div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden flex items-center justify-center px-4"
    style="background-image: url('/images/spes-bg.jpg');"
  >

    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-gradient-to-br from-black/80 via-black/60 to-blue-900/70"></div>

    <!-- BLUR EFFECT -->
    <div class="absolute top-0 left-0 w-80 h-80 bg-blue-500/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-0 right-0 w-80 h-80 bg-cyan-400/20 rounded-full blur-3xl"></div>

    <!-- CARD -->
    <div
      class="relative z-10 w-full max-w-lg bg-white/10 backdrop-blur-2xl border border-white/20 rounded-3xl shadow-2xl p-10 animate-fade"
    >

      <!-- LOGO -->
      <div class="flex justify-center mb-6">
        <img
          src="/images/spes-logo.png"
          alt="SPES Logo"
          class="w-24 h-24 object-contain drop-shadow-2xl"
        />
      </div>


      <!-- TITLE -->
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-white mb-3">
          Verify Your Email
        </h2>

        <p class="text-gray-300 leading-relaxed">
          We’ve sent a verification link to:
        </p>

        <div
          class="mt-4 inline-flex items-center px-4 py-2 rounded-xl bg-white/10 border border-white/20 text-cyan-300 font-semibold break-all"
        >
          {{ userEmail }}
        </div>

        <p class="text-sm text-gray-400 mt-6 leading-relaxed">
          Please check your inbox and click the verification link
          to complete your registration.
        </p>

        <p class="text-sm text-gray-400 mt-3">
          If you don’t see the email, check your spam or junk folder.
        </p>
      </div>

      <!-- SUCCESS MESSAGE -->
      <transition name="fade">
        <div
          v-if="verificationLinkSent"
          class="mt-6 bg-green-500/20 border border-green-400/30 text-green-300 px-4 py-3 rounded-xl text-sm text-center"
        >
          ✔ A new verification link has been sent successfully.
        </div>
      </transition>

      <transition name="fade">
        <div
          v-if="alreadyVerified"
          class="mt-6 bg-blue-500/20 border border-blue-400/30 text-blue-200 px-4 py-3 rounded-xl text-sm text-center"
        >
          This email address is already verified.
        </div>
      </transition>

      <transition name="fade">
        <div
          v-if="form.errors.email"
          class="mt-6 bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-xl text-sm text-center"
        >
          {{ form.errors.email }}
        </div>
      </transition>

      <!-- RESEND -->
      <form
        @submit.prevent="submit"
        class="mt-8"
      >
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-gradient-to-r from-blue-600 to-cyan-500 hover:scale-[1.02] hover:shadow-2xl transition-all duration-300 text-white py-3 rounded-xl font-bold tracking-wide disabled:opacity-50"
        >
          <span v-if="form.processing">
            Sending...
          </span>

          <span v-else>
            Resend Verification Email
          </span>
        </button>
      </form>

      <!-- BACK LOGIN -->
      <div class="text-center mt-6">
        <Link
          :href="route('login')"
          class="text-sm text-cyan-300 hover:text-white transition"
        >
          ← Back to Login
        </Link>
      </div>

    </div>
  </div>
</template>

<style scoped>
.animate-fade {
  animation: fadeIn 0.8s ease;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
