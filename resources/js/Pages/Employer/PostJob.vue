<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const jobOptions = ref([
  { id: 1, title: 'Factory Worker', description: 'Assembly and packaging tasks.' },
  { id: 2, title: 'Office Assistant', description: 'Clerical and admin support.' },
  { id: 3, title: 'Warehouse Helper', description: 'Inventory handling and loading.' },
])

const locationOptions = ref(['Manila', 'Cebu', 'Davao', 'Laguna'])
const skillOptions = ref([])
const skillsByCategory = ref({})

const form = useForm({
  job_id: '',
  title: '',
  description: '',
  location: '',
  type: '',
  slots: 1,
  closing_date: '',
  skills: [],
  new_skills: [],
})

const newJob = ref({ title: '', description: '' })
const newLocation = ref('')
const newSkill = ref({ name: '', category: '' })
const toast = ref({ show: false, message: '', type: 'success' })

const today = computed(() => new Date().toISOString().split('T')[0])
const formErrorMessages = computed(() => Object.values(form.errors).filter(Boolean))
const normalizeSkills = (data) => {
  const list = Array.isArray(data) ? data : Object.values(data || {}).flat()

  return list
    .filter(Boolean)
    .map((skill) => ({
      ...skill,
      id: Number(skill.id),
      category: skill.category || skill.skill_category?.name || skill.skillCategory?.name || 'Skill',
    }))
    .filter((skill) => Number.isInteger(skill.id))
}

watch(() => form.job_id, (id) => {
  const selected = jobOptions.value.find(job => job.id == id)
  form.title = selected?.title || ''
  form.description = selected?.description || ''
})

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

function addJob() {
  if (!newJob.value.title || !newJob.value.description) {
    showToast('Please enter job title and description.', 'error')
    return
  }

  const newId = jobOptions.value.length + 1
  jobOptions.value.push({ id: newId, title: newJob.value.title, description: newJob.value.description })
  form.job_id = newId
  newJob.value = { title: '', description: '' }
  showToast('Job template added.')
}

function addLocation() {
  const value = newLocation.value.trim()
  if (!value) {
    showToast('Please enter a location.', 'error')
    return
  }

  if (!locationOptions.value.includes(value)) {
    locationOptions.value.push(value)
  }

  form.location = value
  newLocation.value = ''
  showToast('Location selected.')
}

async function loadSkills() {
  try {
    const response = await fetch('/onboarding/skills')
    if (!response.ok) throw new Error('Failed to load skills')
    const data = await response.json()
    skillsByCategory.value = data
    skillOptions.value = normalizeSkills(data)
  } catch (error) {
    if (import.meta.env.DEV) console.error(error)
    showToast('Failed to load skills.', 'error')
  }
}

function addSkill() {
  const name = newSkill.value.name.trim()
  const category = newSkill.value.category.trim()

  if (!name || !category) {
    showToast('Please enter skill name and category.', 'error')
    return
  }

  const exists = skillOptions.value.find(skill =>
    skill.name.toLowerCase() === name.toLowerCase() &&
    String(skill.category || '').toLowerCase() === category.toLowerCase()
  )

  if (exists) {
    if (!form.skills.includes(exists.id)) {
      form.skills.push(exists.id)
    }
    newSkill.value = { name: '', category: '' }
    showToast('Existing skill selected.')
    return
  }

  const localId = `local-${Date.now()}`
  const localSkill = { id: localId, name, category, local: true }

  skillOptions.value.push(localSkill)
  form.new_skills.push({ name, category })
  newSkill.value = { name: '', category: '' }
  showToast('New skill will be saved with this job.')
}

function toggleSkill(skillId) {
  const index = form.skills.indexOf(skillId)
  if (index > -1) form.skills.splice(index, 1)
  else form.skills.push(skillId)
}

function submit() {
  form.clearErrors()

  if (!form.title || !form.location || !form.type || !form.slots || !form.closing_date) {
    showToast('Job title, location, employment type, slots, and closing date are required.', 'error')
    return
  }

  form
    .transform((data) => ({
      ...data,
      slots: Number(data.slots),
      skills: data.skills.map(Number).filter(Number.isInteger),
      new_skills: data.new_skills
        .map((skill) => ({
          name: String(skill.name || '').trim(),
          category: String(skill.category || '').trim(),
        }))
        .filter((skill) => skill.name && skill.category),
    }))
    .post('/employer/jobs', {
    onSuccess: () => showToast('Job slot published.'),
    onError: (errors) => {
      const firstError = Object.values(errors).find(Boolean)
      showToast(firstError || 'Unable to publish job slot.', 'error')
    },
  })
}

function goBack() {
  window.history.length > 1 ? window.history.back() : window.location.href = '/employer/jobs'
}

onMounted(loadSkills)
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
      <header>
        <button type="button" class="mb-4 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50" @click="goBack">
          Back
        </button>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Work Opportunities</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">Create Job Slot</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
          Define a SPES work opportunity that CPESO can use for beneficiary matching and placement.
        </p>
      </header>

      <form class="space-y-6" @submit.prevent="submit">
        <div v-if="formErrorMessages.length" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
          <p class="font-semibold">Please fix the highlighted fields.</p>
          <ul class="mt-2 list-disc space-y-1 pl-5">
            <li v-for="message in formErrorMessages" :key="message">{{ message }}</li>
          </ul>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">Job Details</h2>
          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label class="block">
              <span class="label">Available Jobs</span>
              <select v-model="form.job_id" class="input">
                <option value="">Select Job</option>
                <option v-for="job in jobOptions" :key="job.id" :value="job.id">{{ job.title }}</option>
              </select>
            </label>
            <label class="block">
              <span class="label">Job Title</span>
              <input v-model="form.title" type="text" class="input" placeholder="Office Assistant">
              <p v-if="form.errors.title" class="error-text">{{ form.errors.title }}</p>
            </label>
            <label class="block md:col-span-2">
              <span class="label">Description</span>
              <textarea v-model="form.description" rows="4" class="input" placeholder="Describe the daily tasks and work environment."></textarea>
              <p v-if="form.errors.description" class="error-text">{{ form.errors.description }}</p>
            </label>
          </div>

          <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-800">Add a New Job</p>
            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_1.4fr_auto]">
              <input v-model="newJob.title" type="text" class="input bg-white" placeholder="Job Title">
              <input v-model="newJob.description" type="text" class="input bg-white" placeholder="Job Description">
              <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black" @click="addJob">Add</button>
            </div>
          </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">2. Placement Details</h2>
          <div class="mt-4 grid gap-4 md:grid-cols-3">
            <label class="block md:col-span-2">
              <span class="label">Location</span>
              <select v-model="form.location" class="input">
                <option value="">Select location</option>
                <option v-for="location in locationOptions" :key="location" :value="location">{{ location }}</option>
              </select>
              <p v-if="form.errors.location" class="error-text">{{ form.errors.location }}</p>
            </label>
            <label class="block">
              <span class="label">Slots</span>
              <input v-model="form.slots" type="number" min="1" class="input">
              <p v-if="form.errors.slots" class="error-text">{{ form.errors.slots }}</p>
            </label>
            <label class="block">
              <span class="label">Employment Type</span>
              <select v-model="form.type" class="input">
                <option value="">Select type</option>
                <option value="Full-time">Full-time</option>
                <option value="Part-time">Part-time</option>
              </select>
              <p v-if="form.errors.type" class="error-text">{{ form.errors.type }}</p>
            </label>
            <label class="block">
              <span class="label">Closing Date</span>
              <input v-model="form.closing_date" type="date" :min="today" class="input">
              <p v-if="form.errors.closing_date" class="error-text">{{ form.errors.closing_date }}</p>
            </label>
            <div class="block">
              <span class="label">Add New Location</span>
              <div class="flex gap-2">
                <input v-model="newLocation" type="text" class="input" placeholder="New location">
                <button type="button" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50" @click="addLocation">Add</button>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">3. Skills Needed</h2>
          <p class="mt-1 text-sm text-slate-500">Skills help CPESO match beneficiaries to their suitable placements.</p>

          <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <label v-for="skill in skillOptions" :key="skill.id" class="flex items-start gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50">
              <input type="checkbox" :checked="skill.local || form.skills.includes(skill.id)" :disabled="skill.local" class="mt-1 h-4 w-4 rounded border-slate-300 text-blue-600 disabled:opacity-40" @change="toggleSkill(skill.id)">
              <span>
                <span class="block text-sm font-semibold text-slate-900">{{ skill.name }}</span>
                <span class="block text-xs text-slate-500">{{ skill.local ? `${skill.category} - New` : (skill.category || skill.skill_category?.name || 'Skill') }}</span>
              </span>
            </label>
          </div>
          <p v-if="form.errors.skills" class="error-text">{{ form.errors.skills }}</p>
          <p v-if="form.errors.new_skills" class="error-text">{{ form.errors.new_skills }}</p>

          <div class="mt-5 rounded-lg border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-800">Add New Skill</p>
            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
              <input v-model="newSkill.name" type="text" class="input bg-white" placeholder="Skill name">
              <input v-model="newSkill.category" type="text" class="input bg-white" placeholder="Skill Category">
              <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black" @click="addSkill">Add</button>
            </div>
          </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
          <a href="/employer/jobs" class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
          <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Publishing...' : 'Publish Job Slot' }}
          </button>
        </div>
      </form>
    </div>

    <div v-if="toast.show" class="fixed bottom-6 right-6 rounded-lg px-5 py-3 text-sm font-semibold text-white shadow-xl" :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'">
      {{ toast.message }}
    </div>
  </main>
</template>

<style scoped>
.label {
  display: block;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

.input {
  width: 100%;
  border-radius: 0.5rem;
  border: 1px solid #cbd5e1;
  padding: 0.625rem 0.875rem;
  font-size: 0.875rem;
  outline: none;
}

.input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.error-text {
  margin-top: 0.35rem;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #dc2626;
}
</style>
