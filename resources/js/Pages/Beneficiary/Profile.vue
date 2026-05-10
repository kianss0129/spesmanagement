<template>
  <div class="space-y-6">

    <!-- Beneficiary Header -->
    <div class="flex items-center space-x-4 border-b pb-4">
      <img
        v-if="beneficiary.profile_photo_url"
        :src="beneficiary.profile_photo_url"
        alt="Profile Photo"
        class="w-20 h-20 rounded-full object-cover border"
      />
      <div>
        <h1 class="text-2xl font-bold">
          {{ beneficiary.first_name }} {{ beneficiary.last_name }}
        </h1>
        <p class="text-sm text-gray-500">{{ beneficiary.email }}</p>
        <p class="text-sm text-gray-500">
          {{ beneficiary.phone || 'N/A' }}
        </p>
      </div>
    </div>

    <!-- Average Rating -->
    <div>
      <div v-if="average > 0">
        <h2 class="font-semibold text-lg">
          Average Rating: {{ average }} / 5
        </h2>
      </div>
      <div v-else>
        <h2 class="font-semibold text-lg text-gray-500">
          No ratings available yet.
        </h2>
      </div>
    </div>

    <!-- Editable Profile Form -->
    <form @submit.prevent="updateProfile" class="border p-6 rounded space-y-6 bg-white shadow">

      <!-- Personal Information -->
      <div>
        <h2 class="font-semibold text-lg mb-3">Personal Information</h2>

        <div class="grid grid-cols-2 gap-4">

          <div>
            <label class="block text-sm mb-1">Birthdate</label>
            <input
              type="date"
              v-model="form.birthdate"
              class="border p-2 rounded w-full"
            />
          </div>

          <div>
            <label class="block text-sm mb-1">Gender</label>
            <input
              type="text"
              v-model="form.gender"
              class="border p-2 rounded w-full"
            />
          </div>

          <div class="col-span-2">
            <label class="block text-sm mb-1">Address</label>
            <input
              type="text"
              v-model="form.address"
              class="border p-2 rounded w-full"
            />
          </div>

        </div>
      </div>

      <!-- School Information -->
      <div>
        <h2 class="font-semibold text-lg mb-3">School Information</h2>

        <div class="grid grid-cols-4 gap-4">

          <div>
            <label class="block text-sm mb-1">School</label>
            <input
              type="text"
              v-model="form.school"
              class="border p-2 rounded w-full"
            />
          </div>

          <div>
            <label class="block text-sm mb-1">Program</label>
            <input
              type="text"
              v-model="form.program"
              class="border p-2 rounded w-full"
            />
          </div>

          <div>
            <label class="block text-sm mb-1">Year Level</label>
            <input
              type="text"
              v-model="form.year_level"
              class="border p-2 rounded w-full"
            />
          </div>

          <div>
            <label class="block text-sm mb-1">Student ID</label>
            <input
              type="text"
              v-model="form.student_id"
              class="border p-2 rounded w-full"
            />
          </div>

        </div>
      </div>

      <!-- Submit Button -->
      <div>
        <button
          type="submit"
          class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded"
        >
          Save Changes
        </button>
      </div>

    </form>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'

const props = defineProps({
  beneficiary: Object,
  average: Number,
})

const form = ref({
  birthdate: props.beneficiary.birthdate || '',
  gender: props.beneficiary.gender || '',
  address: props.beneficiary.address || '',
  school: props.beneficiary.school || '',
  program: props.beneficiary.program || '',
  year_level: props.beneficiary.year_level || '',
  student_id: props.beneficiary.student_id || '',
})

function updateProfile() {
  Inertia.post('/profile/update', form.value, {
    preserveScroll: true,
    onSuccess: () => {
      alert('Profile updated successfully!')
    },
    onError: (errors) => {
      console.log(errors)
      alert('Please fix the errors.')
    }
  })
}
</script>