<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, Link } from '@inertiajs/inertia-vue3'
import { route } from 'ziggy-js'

const showTypeModal = ref(true)
const showTermsModal = ref(false)
const agreedToTerms = ref(false)
const selectedType = ref('')

// Inertia form
const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  type: '',
})

onMounted(() => {
  showTypeModal.value = true
})

// Select beneficiary type
function selectType(type) {
  selectedType.value = type
  form.type = type
  showTypeModal.value = false
}

// Computed label
const typeLabel = computed(() => {
  if (selectedType.value === 'student') return 'Student'
  if (selectedType.value === 'osy') return 'Out-of-School Youth (OSY)'
  if (selectedType.value === 'dependent') return 'Dependent of Displaced Workers'
  return ''
})

// Submit form
function submit() {
  if (!agreedToTerms.value) {
    alert('You must agree to the Terms & Conditions.')
    return
  }

  form.post(route('register.beneficiary.store'), {
    onSuccess: () => {
      console.log('Registration successful')
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    },
  })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">

    <!-- Beneficiary Type Modal -->
    <div v-if="showTypeModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-xl w-96">
        <h3 class="text-xl font-semibold mb-4 text-center">Select Beneficiary Type</h3>

        <button class="btn w-full" @click="selectType('student')">Student</button>
        <button class="btn w-full mt-2" @click="selectType('osy')">Out-of-School Youth (OSY)</button>
        <button class="btn w-full mt-2" @click="selectType('dependent')">Dependent of Displaced Workers</button>
      </div>
    </div>

    <!-- Registration Form -->
    <div v-if="!showTypeModal" class="w-full max-w-md bg-white p-6 rounded shadow">
      <h1 class="text-xl font-bold mb-2 text-center">Beneficiary Registration</h1>
      <p class="text-sm text-center mb-4">
        Registering as: <strong>{{ typeLabel }}</strong>
      </p>

      <form @submit.prevent="submit">

        <!-- Full Name -->
        <input
          id="name"
          name="name"
          autocomplete="name"
          v-model="form.name"
          class="input"
          placeholder="Full Name"
        />
        <div class="error" v-if="form.errors.name">{{ form.errors.name }}</div>

        <!-- Email -->
        <input
          id="email"
          name="email"
          autocomplete="email"
          v-model="form.email"
          class="input"
          placeholder="Email"
        />
        <div class="error" v-if="form.errors.email">{{ form.errors.email }}</div>

        <!-- Password -->
        <input
          id="password"
          name="password"
          type="password"
          autocomplete="new-password"
          v-model="form.password"
          class="input"
          placeholder="Password"
        />
        <div class="error" v-if="form.errors.password">{{ form.errors.password }}</div>

        <!-- Confirm Password -->
        <input
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          autocomplete="new-password"
          v-model="form.password_confirmation"
          class="input"
          placeholder="Confirm Password"
        />
        <div class="error" v-if="form.errors.password_confirmation">{{ form.errors.password_confirmation }}</div>

        <!-- Hidden Beneficiary Type -->
        <input
          type="hidden"
          id="type"
          name="type"
          v-model="form.type"
        />

        <!-- Terms & Conditions -->
        <div class="mt-3 flex items-center gap-2 text-sm">
          <input
            id="terms"
            name="terms"
            type="checkbox"
            v-model="agreedToTerms"
          />
          <label for="terms">
            I agree to the
            <button
              type="button"
              class="text-blue-600 underline"
              @click="showTermsModal = true"
            >
              Terms & Conditions
            </button>
          </label>
        </div>

        <!-- Register Button -->
        <button
          class="btn-primary w-full mt-4"
          type="submit"
          :disabled="form.processing || !agreedToTerms"
        >
          Register
        </button>

        <p class="text-sm text-center mt-3">
          Already have an account?
          <Link :href="route('login')" class="text-blue-600 underline">Login</Link>
        </p>
      </form>
    </div>

    <!-- Terms Modal -->
    <div v-if="showTermsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl p-6 max-w-lg">
        <h3 class="text-xl font-semibold mb-4 text-center">SPES Terms & Conditions</h3>
        <p>1. Eligibility: Only students, OSY, dependents allowed.</p>
        <p>2. Participation: Must follow PESO rules.</p>
        <p>3. Conduct: Misconduct causes removal.</p>
        <p>4. Data Privacy: Used for SPES only.</p>
        <p>5. Agreement: Accept all policies.</p>
        <button class="btn-primary mt-4" @click="showTermsModal = false">Close</button>
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

.btn {
  border: 1px solid #4f46e5;
  padding: 10px;
  border-radius: 6px;
}

.btn-primary {
  background: #4f46e5;
  color: white;
  padding: 10px;
  border-radius: 6px;
  transition: background 0.2s;
}
.btn-primary:disabled {
  background: #a5b4fc;
  cursor: not-allowed;
}

.error {
  color: red;
  font-size: 12px;
}
</style>
