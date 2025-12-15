<template>
  <div>
    <input type="file" :multiple="multiple" @change="handleFiles" class="border p-2 rounded w-full"/>
    <ul class="mt-2">
      <li v-for="(file,index) in files" :key="index">{{ file.name }}</li>
    </ul>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({ multiple: { type: Boolean, default: false } });
const emits = defineEmits(['update:files']);

const files = ref([]);

function handleFiles(event){
  files.value = Array.from(event.target.files);
  emits('update:files', files.value);
}
</script>
s