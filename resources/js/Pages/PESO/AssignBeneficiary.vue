<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Assign Beneficiary</h1>
    <form @submit.prevent="assign">
      <label>Beneficiary ID:</label>
      <input v-model="beneficiaryId" type="text" class="border p-2 rounded" required />
      
      <label>Job ID:</label>
      <input v-model="jobId" type="text" class="border p-2 rounded" required />
      
      <button class="bg-blue-500 text-white px-4 py-2 rounded mt-2" :disabled="loading">
        {{ loading ? 'Assigning...' : 'Assign' }}
      </button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const beneficiaryId = ref('');
const jobId = ref('');
const loading = ref(false);

const assign = async () => {
  if (!beneficiaryId.value || !jobId.value) {
    alert('Please enter both Beneficiary ID and Job ID.');
    return;
  }

  loading.value = true;

  try {
    await axios.post('/peso/assign-beneficiary', {
      beneficiary_id: beneficiaryId.value,
      job_id: jobId.value
    });
    alert('Beneficiary assigned successfully!');
    // Optionally reset inputs:
    beneficiaryId.value = '';
    jobId.value = '';
  } catch (error) {
    console.error(error);
    if (error.response?.data?.message) {
      alert('Error: ' + error.response.data.message);
    } else {
      alert('Failed to assign beneficiary. Please try again.');
    }
  } finally {
    loading.value = false;
  }
};
</script>