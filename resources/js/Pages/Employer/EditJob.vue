<script setup>
import { useForm } from '@inertiajs/vue3'
import { ref, watch, computed } from 'vue'


const props = defineProps({ job: Object })


/* TOAST */
const toast = ref({
  show: false,
  message: '',
  type: 'success'
})


function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true


  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}


/* TODAY */
const today = computed(() => {
  return new Date().toISOString().split('T')[0]
})


/* PRESET JOB OPTIONS */
const jobOptions = [
  { id: 1, title: 'Factory Worker', description: 'Assembly and packaging tasks.' },
  { id: 2, title: 'Office Assistant', description: 'Clerical and admin support.' },
  { id: 3, title: 'Warehouse Helper', description: 'Inventory handling and loading.' }
]


/* TOGGLE CUSTOM INPUT */
const useCustomJob = ref(false)
const useCustomLocation = ref(false)


/* FORM */
const form = useForm({
  job_id: '',
  title: props.job.title,
  description: props.job.description,
  location: props.job.location,
  type: props.job.type,
  slots: props.job.slots,
  closing_date: props.job.closing_date,
})


/* AUTO FILL (only if using dropdown) */
watch(() => form.job_id, (id) => {
  if (useCustomJob.value) return


  const selected = jobOptions.find(job => job.id == id)


  form.title = selected?.title || ''
  form.description = selected?.description || ''
})


/* SUBMIT */
function submit() {


  if (!form.title || !form.location) {
    showToast('Job title and location are required', 'error')
    return
  }


  if (!form.closing_date) {
    showToast('Closing date is required', 'error')
    return
  }


  if (form.closing_date < today.value) {
    showToast('Closing date must be today or future date', 'error')
    return
  }


  form.put(`/employer/jobs/${props.job.id}`, {
    onSuccess: () => showToast('Job updated successfully ✅'),
    onError: () => showToast('Failed to update job ❌', 'error')
  })
}
</script>


<template>
  <div class="min-h-screen bg-blue-50 flex justify-center items-start py-10 px-4">


    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">


      <!-- HEADER -->
      <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-6">
        <h1 class="text-2xl font-bold">Edit Job</h1>
        <p class="text-blue-100 text-sm">Modify job details</p>
      </div>


      <form @submit.prevent="submit" class="p-6 space-y-5">


        <!-- JOB SECTION -->
        <div>
          <label class="label">Job Title</label>


          <!-- toggle -->
          <div class="flex gap-3 mb-2">
            <button type="button" @click="useCustomJob = false" :class="!useCustomJob ? 'active-tab' : 'tab'">
              Select Job
            </button>


            <button type="button" @click="useCustomJob = true" :class="useCustomJob ? 'active-tab' : 'tab'">
              Custom Job
            </button>
          </div>


          <!-- dropdown -->
          <select v-if="!useCustomJob" v-model="form.job_id" class="input">
            <option value="">Select Job</option>
            <option v-for="job in jobOptions" :key="job.id" :value="job.id">
              {{ job.title }}
            </option>
          </select>


          <!-- custom input -->
          <input
            v-else
            v-model="form.title"
            type="text"
            placeholder="Enter custom job title"
            class="input"
          />
        </div>


        <!-- DESCRIPTION -->
        <div>
          <label class="label">Description</label>
          <textarea v-model="form.description" rows="3" class="input"></textarea>
        </div>


        <!-- LOCATION -->
        <div>
          <label class="label">Location</label>


          <div class="flex gap-3 mb-2">
            <button type="button" @click="useCustomLocation = false" :class="!useCustomLocation ? 'active-tab' : 'tab'">
              Select
            </button>


            <button type="button" @click="useCustomLocation = true" :class="useCustomLocation ? 'active-tab' : 'tab'">
              Custom
            </button>
          </div>


          <input
            v-if="useCustomLocation"
            v-model="form.location"
            type="text"
            placeholder="Enter location"
            class="input"
          />


          <select v-else v-model="form.location" class="input">
            <option value="">Select Location</option>
            <option>Manila</option>
            <option>Cebu</option>
            <option>Davao</option>
            <option>Laguna</option>
          </select>
        </div>


        <!-- TYPE -->
        <div>
          <label class="label">Employment Type</label>
          <select v-model="form.type" class="input">
            <option value="">Select Type</option>
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
          </select>
        </div>


        <!-- SLOTS -->
        <div>
          <label class="label">Available Slots</label>
          <input v-model="form.slots" type="number" min="1" class="input" />
        </div>


        <!-- DATE -->
        <div>
          <label class="label">Closing Date</label>
          <input
            v-model="form.closing_date"
            type="date"
            :min="today"
            class="input"
          />
        </div>


        <!-- BUTTONS -->
        <div class="flex justify-end gap-3 pt-4">
          <a href="/employer/jobs" class="btn-outline">
            Cancel
          </a>


          <button type="submit" :disabled="form.processing" class="btn-primary">
            {{ form.processing ? 'Updating...' : 'Update Job' }}
          </button>
        </div>


      </form>
    </div>


    <!-- TOAST -->
    <transition name="fade">
      <div
        v-if="toast.show"
        :class="[
          'fixed bottom-6 right-6 px-5 py-3 rounded-xl shadow-lg text-white',
          toast.type === 'success' ? 'bg-green-500' : 'bg-red-500'
        ]"
      >
        {{ toast.message }}
      </div>
    </transition>


  </div>
</template>


<style scoped>
.label {
  display:block;
  font-size:13px;
  font-weight:600;
  margin-bottom:6px;
  color:#374151;
}


.input {
  width:100%;
  padding:10px 14px;
  border-radius:10px;
  border:1px solid #dbeafe;
  outline:none;
}


.input:focus {
  border-color:#3b82f6;
  box-shadow:0 0 0 3px rgba(59,130,246,0.15);
}


.tab {
  padding:4px 10px;
  font-size:12px;
  border:1px solid #dbeafe;
  border-radius:8px;
}


.active-tab {
  padding:4px 10px;
  font-size:12px;
  background:#2563eb;
  color:white;
  border-radius:8px;
}


.btn-primary {
  background:#2563eb;
  color:white;
  padding:8px 16px;
  border-radius:10px;
}


.btn-outline {
  border:1px solid #2563eb;
  color:#2563eb;
  padding:8px 16px;
  border-radius:10px;
}
</style>