<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

import { route } from 'ziggy-js'

const showTermsModal = ref(false)
const agreedToTerms = ref(false)

const form = useForm({
  name: '',
  company_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  accept_terms: false, // explicitly bind checkbox
})

const submit = () => {
  if (!form.accept_terms) {
    alert('You must agree to the Terms & Conditions.')
    return
  }

  form.post(route('register.employer.store'))
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-4 text-center">Employer Registration</h1>

      <form @submit.prevent="submit">
        <!-- Company / Employer Name -->
        <label for="company_name" class="block text-sm font-medium text-gray-700">
          Company / Employer Name
        </label>
        <input
          v-model="form.company_name"
          name="company_name"
          id="company_name"
          autocomplete="organization"
          type="text"
          placeholder="Company / Employer Name"
          class="input"
        />
        <div v-if="form.errors.company_name" class="error">{{ form.errors.company_name }}</div>

        <!-- Full Name -->
        <label for="name" class="block text-sm font-medium text-gray-700 mt-2">
          Full Name
        </label>
        <input
          v-model="form.name"
          name="name"
          id="name"
          autocomplete="name"
          type="text"
          placeholder="Full Name"
          class="input"
        />
        <div v-if="form.errors.name" class="error">{{ form.errors.name }}</div>

        <!-- Email -->
        <label for="email" class="block text-sm font-medium text-gray-700 mt-2">
          Email
        </label>
        <input
          v-model="form.email"
          name="email"
          id="email"
          autocomplete="email"
          type="email"
          placeholder="Email"
          class="input"
        />
        <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>

        <!-- Password -->
        <label for="password" class="block text-sm font-medium text-gray-700 mt-2">
          Password
        </label>
        <input
          v-model="form.password"
          name="password"
          id="password"
          autocomplete="new-password"
          type="password"
          placeholder="Password"
          class="input"
        />
        <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>

        <!-- Password Confirmation -->
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mt-2">
          Confirm Password
        </label>
        <input
          v-model="form.password_confirmation"
          name="password_confirmation"
          id="password_confirmation"
          autocomplete="new-password"
          type="password"
          placeholder="Confirm Password"
          class="input"
        />

        <!-- Terms & Conditions Checkbox -->
        <div class="mt-3 flex items-center gap-2 text-sm">
          <input
            type="checkbox"
            name="accept_terms"
            id="accept_terms"
            v-model="form.accept_terms"
            class="form-checkbox"
          />
          <label for="accept_terms" class="text-sm">
            I agree to the
            <button
              type="button"
              id="open_terms_button"
              class="text-blue-600 underline"
              @click="showTermsModal = true"
              aria-label="Open Terms and Conditions"
            >
              Terms & Conditions
            </button>
          </label>
        </div>

        <!-- Submit Button -->
        <button
          id="submit_registration"
          class="btn-primary w-full mt-4"
          :disabled="form.processing || !form.accept_terms"
        >
          Register
        </button>

        <!-- Login Link -->
        <p class="text-sm text-center mt-3">
          Already have an account?
          <Link href="/login" id="login_link" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms & Conditions Modal -->
    <div
      v-if="showTermsModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-xl p-6 w-full max-w-lg relative max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4 text-center">
          Employer Terms & Conditions
        </h3>

        <div class="space-y-3 text-sm text-gray-700">
          <p><strong>1. Eligibility:</strong> Only legitimate employers or companies may register.</p>
          <p><strong>2. Job Posting:</strong> Must provide accurate and legal job information.</p>
          <p><strong>3. Conduct:</strong> Misuse of the system may result in account suspension.</p>
          <p><strong>4. Data Privacy:</strong> Data will be used only for SPES administration.</p>
          <p><strong>5. Agreement:</strong> By registering, you accept all SPES policies.</p>
        </div>

        <div class="mt-4 text-center">
          <button
            id="close_terms_modal"
            class="px-6 py-2 bg-indigo-600 text-white rounded-lg"
            @click="showTermsModal = false"
          >
            Close
          </button>
        </div>

        <button
          id="modal_close_x"
          class="absolute top-2 right-3 text-xl text-gray-500"
          @click="showTermsModal = false"
          aria-label="Close Terms Modal"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 6px;
  margin-top: 6px;
}
.btn-primary {
  background: #059669;
  color: white;
  padding: 10px;
  border-radius: 6px;
}
.error {
  color: red;
  font-size: 12px;
}
</style>
