<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

      <h1 class="text-2xl font-bold text-blue-700 mb-2">
        Forgot Password
      </h1>

      <p class="text-sm text-gray-600 mb-6">
        Enter your email and we’ll send you a password reset link.
      </p>

      <form @submit.prevent="submit">
        <div class="mb-5">
          <label class="block text-sm text-gray-600 mb-1">
            Email
          </label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg border border-gray-300 px-4 py-2
                   focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <p v-if="form.errors.email" class="text-xs text-red-500 mt-1">
            {{ form.errors.email }}
          </p>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium
                 py-2.5 rounded-lg transition"
        >
          Send Reset Link
        </button>
      </form>

      <div class="text-sm text-center mt-6">
        <Link href="/login" class="text-blue-600 hover:underline">
          Back to Login
        </Link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
  email: '',
})

function submit() {
  form.post('/forgot-password')
}
</script>