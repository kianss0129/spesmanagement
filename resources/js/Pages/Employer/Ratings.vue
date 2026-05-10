<template>
  <div class="max-w-5xl mx-auto p-6 bg-white rounded-xl shadow">
    <h1 class="text-2xl font-bold mb-6">Rate Beneficiaries</h1>

    <table class="w-full text-left border">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">Beneficiary</th>
          <th class="p-2 border">Punctuality</th>
          <th class="p-2 border">Work Quality</th>
          <th class="p-2 border">Attitude</th>
          <th class="p-2 border">Communication</th>
          <th class="p-2 border">Overall</th>
          <th class="p-2 border">Comment</th>
          <th class="p-2 border">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="beneficiaries.length === 0">
          <td class="p-2 border text-center" colspan="8">No beneficiaries found.</td>
        </tr>
        <tr v-for="b in beneficiaries" :key="b.id">
          <td class="p-2 border">{{ b.user.first_name }} {{ b.user.last_name }}</td>

          <td class="p-2 border">
            <select v-model="b.ratings.punctuality" class="border p-1 rounded w-full">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </td>

          <td class="p-2 border">
            <select v-model="b.ratings.output_quality" class="border p-1 rounded w-full">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </td>

          <td class="p-2 border">
            <select v-model="b.ratings.work_attitude" class="border p-1 rounded w-full">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </td>

          <td class="p-2 border">
            <select v-model="b.ratings.communication" class="border p-1 rounded w-full">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </td>

          <td class="p-2 border">
            <select v-model="b.ratings.overall" class="border p-1 rounded w-full">
              <option v-for="n in 5" :key="n" :value="n">{{ n }}</option>
            </select>
          </td>

          <td class="p-2 border">
            <input
              type="text"
              v-model="b.ratings.comment"
              class="border p-1 rounded w-full"
              placeholder="Optional comment"
            />
          </td>

          <td class="p-2 border">
            <button
              @click="submitRating(b)"
              class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
              :disabled="b.submitting"
            >
              {{ b.submitting ? 'Submitting...' : 'Submit' }}
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const beneficiaries = ref([])

// Fetch beneficiaries for the logged-in employer
onMounted(async () => {
  try {
    const res = await axios.get('/employer/beneficiaries')

    // Map ratings and ensure user exists
    beneficiaries.value = res.data
      .filter(b => b.user) // ensure no null users
      .map(b => ({
        ...b,
        ratings: {
          punctuality: 5,
          output_quality: 5,    // matches DB
          work_attitude: 5,     // matches DB
          communication: 5,
          overall: 5,
          comment: ''
        },
        submitting: false
      }))
  } catch (err) {
    console.error('Failed to load beneficiaries:', err)
    beneficiaries.value = []
  }
})

async function submitRating(b) {
  b.submitting = true
  try {
    await axios.post('/employer/ratings', {
      beneficiary_id: b.id,
      ...b.ratings
    })
    alert(`Rating submitted for ${b.user.first_name} ${b.user.last_name}!`)
  } catch (err) {
    console.error(err)
    alert('Failed to submit rating. Please try again.')
  } finally {
    b.submitting = false
  }
}
</script>

<style>
table th,
table td {
  text-align: center;
}
</style>