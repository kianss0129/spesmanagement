<template>
  <div>
    <h1 class="text-2xl font-bold mb-4">Assign Beneficiary</h1>
    <form @submit.prevent="assign">
      <label>Beneficiary ID:</label>
      <input v-model="beneficiaryId" type="text" class="border p-2 rounded"/>
      
      <label>Job ID:</label>
      <input v-model="jobId" type="text" class="border p-2 rounded"/>
      
      <button class="bg-blue-500 text-white px-4 py-2 rounded mt-2">Assign</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Inertia } from '@inertiajs/inertia';

const beneficiaryId = ref('');
const jobId = ref('');

const assign = async () => {
  await axios.post('/peso/assign-beneficiary', {
    beneficiary_id: beneficiaryId.value,
    job_id: jobId.value
  });
  alert('Assigned!');
};
</script>
