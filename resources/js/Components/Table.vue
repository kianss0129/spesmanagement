<template>
  <table class="min-w-full border-collapse border border-gray-200">
    <thead class="bg-gray-100">
      <tr>
        <th v-for="col in columns" :key="col.key" class="border px-4 py-2 text-left">{{ col.label }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="row in rows" :key="row.id" class="border-b">
        <td v-for="col in columns" :key="col.key" class="px-4 py-2">{{ row[col.key] }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script setup>
const props = defineProps({
  columns: { type: Array, required: true }, // [{key:'name',label:'Name'}]
  rows: { type: Array, required: true }
});
</script>
