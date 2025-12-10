<template>
  <canvas :id="chartId"></canvas>
</template>

<script setup>
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';
import { ref, watch } from 'vue';

const props = defineProps({
  chartId: { type: String, required: true },
  type: { type: String, default: 'bar' },
  data: { type: Object, required: true },
  options: { type: Object, default: () => ({ responsive: true }) }
});

let chartInstance = ref(null);

onMounted(() => {
  const ctx = document.getElementById(props.chartId);
  chartInstance.value = new Chart(ctx, { type: props.type, data: props.data, options: props.options });
});

watch(() => props.data, (newData) => {
  if(chartInstance.value){
    chartInstance.value.data = newData;
    chartInstance.value.update();
  }
}, { deep: true });
</script>
