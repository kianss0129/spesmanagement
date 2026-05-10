<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch, computed, ref } from 'vue'
import { Plus, Briefcase, MapPin, CalendarDays } from 'lucide-vue-next'

const jobOptions = ref([
  { id: 1, title: 'Factory Worker', description: 'Assembly and packaging tasks.' },
  { id: 2, title: 'Office Assistant', description: 'Clerical and admin support.' },
  { id: 3, title: 'Warehouse Helper', description: 'Inventory handling and loading.' }
])

const locationOptions = ref(['Manila', 'Cebu', 'Davao', 'Laguna'])

/* NEW LOCATION INPUT */
const newLocation = ref('')

const form = useForm({
  job_id: '',
  title: '',
  description: '',
  location: '',
  type: '',
  slots: 1,
  closing_date: '',
})

/* NEW JOB */
const newJob = ref({
  title: '',
  description: ''
})

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

/* AUTO FILL JOB */
watch(() => form.job_id, (id) => {
  const selected = jobOptions.value.find(job => job.id == id)


  form.title = selected?.title || ''
  form.description = selected?.description || ''
})

/* ADD JOB */
function addJob() {
  if (!newJob.value.title || !newJob.value.description) {
    showToast('Please enter job title and description', 'error')
    return
  }


  const newId = jobOptions.value.length + 1


  jobOptions.value.push({
    id: newId,
    title: newJob.value.title,
    description: newJob.value.description
  })


  form.job_id = newId


  newJob.value.title = ''
  newJob.value.description = ''


  showToast('Job added successfully ✅')
}
/* ADD LOCATION */
function addLocation() {
  if (!newLocation.value.trim()) {
    showToast('Please enter a location', 'error')
    return
  }


  const exists = locationOptions.value.includes(newLocation.value.trim())


  if (exists) {
    showToast('Location already exists', 'error')
    return
  }


  locationOptions.value.push(newLocation.value.trim())
  form.location = newLocation.value.trim()


  newLocation.value = ''


  showToast('Location added successfully 📍')
}


/* SUBMIT */
function submit() {
  if (!form.closing_date) {
    showToast('Please select closing date', 'error')
    return
  }


  form.post('/employer/jobs', {
    onSuccess: () => {
      showToast('Job posted successfully 🎉')
    },
    onError: () => {
      showToast('Something went wrong ❌', 'error')
    }
  })
}

</script>

<template>
  <div class="min-h-screen bg-blue-50 flex items-center justify-center p-6">
    <div class="w-full max-w-xl bg-white shadow-xl rounded-2xl overflow-hidden">

      <div class="bg-gradient-to-r from-blue-600 to-blue-500 text-white p-5">
        <h1 class="text-2xl font-bold">Post a Job</h1>
        <p class="text-blue-100 text-sm">Create employment opportunity with a modern hiring experience</p>

      </div>

      <form @submit.prevent="submit" class="p-6 space-y-5">

        <!-- ADD JOB -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-700 flex items-center gap-2">
            <Plus class="w-5 h-5 text-blue-600" />
            Add New Job
          </h2>


          <div class="grid md:grid-cols-2 gap-4">
            <input v-model="newJob.title" type="text" placeholder="Enter job title" class="modern-input" />
            <textarea v-model="newJob.description" rows="2" placeholder="Enter job description" class="modern-input"></textarea>
          </div>


          <button type="button" @click="addJob" class="modern-btn mt-4">
            Add Job
          </button>
        </div>


        <!-- JOB -->
        <div>
          <label class="modern-label">Job Title</label>
          <select v-model="form.job_id" class="modern-input">
            <option value="">Select Job</option>
            <option v-for="job in jobOptions" :key="job.id" :value="job.id">
              {{ job.title }}
            </option>
          </select>
        </div>

        <!-- DESCRIPTION -->
        <div>
          <label class="modern-label">Description</label>
          <textarea v-model="form.description" rows="4" readonly class="modern-input"></textarea>
        </div>

         <!-- LOCATION -->
        <div>
          <label class="modern-label">Location</label>
          <select v-model="form.location" class="modern-input">
            <option value="">Select Location</option>
            <option v-for="loc in locationOptions" :key="loc" :value="loc">
              {{ loc }}
            </option>
          </select>
        </div>

        <!-- ADD LOCATION -->
        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6">
          <h2 class="text-lg font-semibold mb-4 text-gray-700 flex items-center gap-2">
            <MapPin class="w-5 h-5 text-blue-600" />
            Add New Location
          </h2>


          <input
            v-model="newLocation"
            type="text"
            placeholder="Enter new location"
            class="modern-input"
          />


          <button type="button" @click="addLocation" class="modern-btn mt-3">
            Add Location
          </button>
        </div>

        <!-- TYPE + SLOTS -->
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="modern-label">Employment Type</label>
            <select v-model="form.type" class="modern-input">
              <option value="">Select Type</option>
              <option value="Full-time">Full-time</option>
              <option value="Part-time">Part-time</option>
            </select>
          </div>


          <div>
            <label class="modern-label">Slots</label>
            <input v-model="form.slots" type="number" min="1" class="modern-input" />
          </div>
        </div>

         <!-- CLOSING DATE -->
        <div>
          <label class="modern-label flex items-center gap-2">
            <CalendarDays class="w-4 h-4" />
            Closing Date
          </label>
          <input v-model="form.closing_date" type="date" :min="today" class="modern-input" />
        </div>


        <!-- SUBMIT -->
        <button class="modern-btn w-full" :disabled="form.processing">
          {{ form.processing ? 'Submitting...' : 'Publish Job Post' }}
        </button>


      </form>
    </div>


    <!-- TOAST -->
    <transition name="slide-fade">
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

<<style scoped>
.modern-label {
  display: flex;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 8px;
  color: #374151;
}


.modern-input {
  width: 100%;
  padding: 12px 16px;
  border-radius: 14px;
  border: 1px solid #dbeafe;
  transition: 0.3s;
}


.modern-input:focus {
  outline: none;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
}


.modern-btn {
  background: linear-gradient(to right, #2563eb, #4f46e5);
  color: white;
  padding: 12px 20px;
  border-radius: 14px;
  font-weight: 600;
}


.modern-btn:hover {
  transform: translateY(-2px);
}
</style>
