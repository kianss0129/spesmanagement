<template>
  <div>
    <h1>Approved Beneficiaries</h1>

    <table v-if="beneficiaries.length">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Approved At</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="b in beneficiaries" :key="b.id">
          <td>{{ b.name }}</td>
          <td>{{ b.email }}</td>
          <td>{{ b.approved_at }}</td>
        </tr>
      </tbody>
    </table>

    <p v-else>No approved beneficiaries found.</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const beneficiaries = ref([])

async function loadApprovedBeneficiaries() {
  try {
    const res = await axios.get('/peso/beneficiaries/approved')
    beneficiaries.value = res.data
    console.log('Loaded:', res.data) // optional debug
  } catch (error) {
    console.error('Error loading approved beneficiaries:', error)
  }
}

onMounted(() => {
  loadApprovedBeneficiaries()
})
</script>