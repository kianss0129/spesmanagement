<template>
  <div class="space-y-6">

    <!-- 1️⃣ Beneficiary Header -->
    <div class="flex items-center space-x-4">
      <img
        v-if="beneficiary.profile_photo_url"
        :src="beneficiary.profile_photo_url"
        alt="Profile Photo"
        class="w-20 h-20 rounded-full object-cover"
      />
      <div>
        <h1 class="text-2xl font-bold">{{ beneficiary.name }}</h1>
        <p class="text-sm text-gray-500">{{ beneficiary.email }}</p>
        <p class="text-sm text-gray-500">{{ beneficiary.contact_number || 'N/A' }}</p>
      </div>
    </div>

    <!-- 2️⃣ Average Rating -->
    <div class="mb-4">
      <h2 class="font-semibold text-lg">Average Rating: {{ average }} / 5</h2>
    </div>

    <!-- 3️⃣ Personal Information -->
    <div class="border p-4 rounded">
      <h2 class="font-semibold mb-2">Personal Information</h2>
      <p><strong>Birthdate:</strong> {{ beneficiary.birthdate || 'N/A' }}</p>
      <p><strong>Gender:</strong> {{ beneficiary.gender || 'N/A' }}</p>
      <p><strong>Address:</strong> {{ beneficiary.address || 'N/A' }}</p>
    </div>

    <!-- 4️⃣ School Information -->
    <div class="border p-4 rounded" v-if="beneficiary.school">
      <h2 class="font-semibold mb-2">School Information</h2>
      <p><strong>School:</strong> {{ beneficiary.school.name || 'N/A' }}</p>
      <p><strong>Program:</strong> {{ beneficiary.school.program || 'N/A' }}</p>
      <p><strong>Year Level:</strong> {{ beneficiary.school.year_level || 'N/A' }}</p>
      <p><strong>Student ID:</strong> {{ beneficiary.school.student_id || 'N/A' }}</p>
    </div>

    <!-- 5️⃣ PESO Office -->
    <div class="border p-4 rounded" v-if="beneficiary.pesoOffice">
      <h2 class="font-semibold mb-2">Assigned PESO Office</h2>
      <p><strong>Office Name:</strong> {{ beneficiary.pesoOffice.name || 'N/A' }}</p>
      <p><strong>Officer In Charge:</strong> {{ beneficiary.pesoOffice.officer_name || 'N/A' }}</p>
      <p><strong>Address:</strong> {{ beneficiary.pesoOffice.address || 'N/A' }}</p>
      <p><strong>Contact:</strong> {{ beneficiary.pesoOffice.contact_number || 'N/A' }}</p>
    </div>

    <!-- 6️⃣ SPES Work History -->
    <div class="border p-4 rounded" v-if="beneficiary.workHistory && beneficiary.workHistory.length">
      <h2 class="font-semibold mb-2">SPES Work History</h2>
      <table class="w-full border-collapse border">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-2 py-1 text-left">Employer</th>
            <th class="border px-2 py-1 text-left">Position</th>
            <th class="border px-2 py-1 text-left">Duration</th>
            <th class="border px-2 py-1 text-left">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="work in beneficiary.workHistory" :key="work.id">
            <td class="border px-2 py-1">{{ work.employer?.name || 'N/A' }}</td>
            <td class="border px-2 py-1">{{ work.position || 'N/A' }}</td>
            <td class="border px-2 py-1">{{ work.duration || 'N/A' }}</td>
            <td class="border px-2 py-1">{{ work.status || 'N/A' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 7️⃣ Employer Ratings -->
    <div class="border p-4 rounded" v-if="ratings && ratings.length">
      <h2 class="font-semibold mb-2">Employer Ratings</h2>
      <div v-for="rating in ratings" :key="rating.id" class="border p-3 rounded mb-2">
        <p><strong>Employer:</strong> {{ rating.employer?.name || 'N/A' }}</p>
        <p><strong>Application:</strong> {{ rating.application?.jobListing?.title || 'N/A' }}</p>
        <p><strong>Punctuality:</strong> {{ rating.punctuality }}</p>
        <p><strong>Work Attitude:</strong> {{ rating.work_attitude }}</p>
        <p><strong>Output Quality:</strong> {{ rating.output_quality }}</p>
        <p><strong>Communication:</strong> {{ rating.communication }}</p>
        <p><strong>Overall:</strong> {{ rating.overall }}</p>
        <p><strong>Comment:</strong> {{ rating.comment || 'N/A' }}</p>
      </div>
    </div>
    <div v-else>
      <p>No ratings available yet.</p>
    </div>

  </div>
</template>

<script setup>
import { defineProps } from 'vue'

// Props from Inertia
const props = defineProps({
  beneficiary: Object,
  ratings: Array,
  average: Number,
})

// Destructure for template
const { beneficiary, ratings, average } = props
</script>
