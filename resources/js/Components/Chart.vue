<template>
  <div class="bg-white rounded shadow p-4">
    <div class="flex justify-between items-center mb-3">
      <h3 class="font-medium text-gray-700">{{ title }}</h3>
      <div v-if="periodOptions.length" class="ml-2">
        <select v-model="selectedPeriod" @change="onPeriodChange" class="border rounded px-2 py-1 text-sm">
          <option v-for="p in periodOptions" :key="p" :value="p">{{ p.charAt(0).toUpperCase() + p.slice(1) }}</option>
        </select>
      </div>
    </div>
    <canvas :id="chartId"></canvas>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import Chart from 'chart.js/auto'

const props = defineProps({
  chartId: { type: String, required: true },
  title: { type: String, default: '' },
  type: { type: String, default: 'bar' },
  data: { type: Object, required: true },
  options: { type: Object, default: () => ({ responsive: true }) },
  periodOptions: { type: Array, default: () => [] }
})

const emits = defineEmits(['period-change'])
const selectedPeriod = ref(props.periodOptions[0] || '')
let chartInstance = ref(null)

const onPeriodChange = () => {
  emits('period-change', selectedPeriod.value)
}

onMounted(() => {
  const ctx = document.getElementById(props.chartId)
  chartInstance.value = new Chart(ctx, { type: props.type, data: props.data, options: props.options })
})

watch(() => props.data, (newData) => {
  if(chartInstance.value){
    chartInstance.value.data = newData
    chartInstance.value.update()
  }
}, { deep: true })
</script>
