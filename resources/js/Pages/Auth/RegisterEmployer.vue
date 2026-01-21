<script setup>
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { route } from 'ziggy-js'

const showTermsModal = ref(false)
const agreedToTerms = ref(false)

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const submit = () => {
  if (!agreedToTerms.value) {
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
        <input v-model="form.name" type="text" placeholder="Company / Employer Name" class="input" />
        <div v-if="form.errors.name" class="error">{{ form.errors.name }}</div>

        <input v-model="form.email" type="email" placeholder="Email" class="input" />
        <div v-if="form.errors.email" class="error">{{ form.errors.email }}</div>

        <input v-model="form.password" type="password" placeholder="Password" class="input" />
        <div v-if="form.errors.password" class="error">{{ form.errors.password }}</div>

        <input v-model="form.password_confirmation" type="password" placeholder="Confirm Password" class="input" />

        <!-- Terms & Conditions -->
        <div class="mt-3 flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="agreedToTerms" />
          <span>
            I agree to the
            <button type="button" class="text-blue-600 underline" @click="showTermsModal = true">
              Terms & Conditions
            </button>
          </span>
        </div>

        <button class="btn-primary w-full mt-4" :disabled="form.processing || !agreedToTerms">
          Register
        </button>

        <p class="text-sm text-center mt-3">
          Already have an account?
          <Link href="/login" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms Modal -->
    <div v-if="showTermsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 w-full max-w-lg relative max-h-[80vh] overflow-y-auto">
        <h3 class="text-xl font-semibold mb-4 text-center">Employer Terms & Conditions</h3>

        <div class="space-y-3 text-sm text-gray-700">
          <p><strong>1. Eligibility:</strong> Only legitimate employers or companies may register.</p>
          <p><strong>2. Job Posting:</strong> Must provide accurate and legal job information.</p>
          <p><strong>3. Conduct:</strong> Misuse of the system may result in account suspension.</p>
          <p><strong>4. Data Privacy:</strong> Data will be used only for SPES administration.</p>
          <p><strong>5. Agreement:</strong> By registering, you accept all SPES policies.</p>
        </div>

        <div class="mt-4 text-center">
          <button class="px-6 py-2 bg-indigo-600 text-white rounded-lg" @click="showTermsModal = false">Close</button>
        </div>

        <button class="absolute top-2 right-3 text-xl text-gray-500" @click="showTermsModal = false">×</button>
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
  margin-top: 10px;
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
