<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  job: {
    type: Object,
    required: true,
  },
})

const toast = ref({ show: false, message: '', type: 'success' })
const useCustomJob = ref(true)
const useCustomLocation = ref(true)
const skillOptions = ref([])
const newSkill = ref({ name: '', category: '' })

const today = computed(() => new Date().toISOString().split('T')[0])
const formErrorMessages = computed(() => Object.values(form.errors).filter(Boolean))
const normalizeDate = (value) => value ? String(value).slice(0, 10) : ''
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

const jobOptions = [
  { id: 1, title: 'Factory Worker', description: 'Assembly and packaging tasks.' },
  { id: 2, title: 'Office Assistant', description: 'Clerical and admin support.' },
  { id: 3, title: 'Warehouse Helper', description: 'Inventory handling and loading.' },
]

const form = useForm({
  job_id: '',
  title: props.job.title || '',
  description: props.job.description || '',
  location: props.job.location || '',
  type: props.job.type || '',
  slots: props.job.slots || 1,
  closing_date: normalizeDate(props.job.closing_date),
  skills: props.job.skills?.map(skill => Number(skill.id)).filter(Number.isInteger) || [],
  new_skills: [],
})

watch(() => form.job_id, (id) => {
  if (useCustomJob.value) return
  const selected = jobOptions.find(job => job.id == id)
  form.title = selected?.title || form.title
  form.description = selected?.description || form.description
})

function showToast(message, type = 'success') {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, 3000)
}

async function loadSkills() {
  try {
    const response = await fetch('/onboarding/skills')
    if (!response.ok) throw new Error('Failed to load skills')
    const data = await response.json()
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

  if (form.closing_date < today.value) {
    showToast('Closing date must be today or later.', 'error')
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
    .put(`/employer/jobs/${props.job.id}`, {
    onSuccess: () => showToast('Job slot updated.'),
    onError: (errors) => {
      if (import.meta.env.DEV) console.error('Job update failed:', errors)
      const firstError = Object.values(errors).find(Boolean)
      showToast(firstError || 'Unable to update job slot.', 'error')
    },
  })
}

onMounted(loadSkills)
</script>

<template>
  <main class="min-h-screen bg-slate-100 px-4 py-6 text-slate-900 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl space-y-6">
      <header>
        <a href="/employer/jobs" class="mb-4 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
          Back
        </a>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">Work Opportunities</p>
        <h1 class="mt-2 text-3xl font-bold text-slate-950">Edit Job Slot</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
          Keep slot details accurate so CPESO can match beneficiaries to the right work setting.
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
          <h2 class="text-lg font-bold text-slate-900">1. Job Details</h2>
          <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="useCustomJob ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'" @click="useCustomJob = true">
              Custom
            </button>
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!useCustomJob ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'" @click="useCustomJob = false">
              Template
            </button>
          </div>

          <div class="mt-4 grid gap-4 md:grid-cols-2">
            <label v-if="!useCustomJob" class="block md:col-span-2">
              <span class="label">Template</span>
              <select v-model="form.job_id" class="input">
                <option value="">Select a job template</option>
                <option v-for="job in jobOptions" :key="job.id" :value="job.id">{{ job.title }}</option>
              </select>
            </label>
            <label class="block">
              <span class="label">Job Title</span>
              <input v-model="form.title" type="text" class="input">
              <p v-if="form.errors.title" class="error-text">{{ form.errors.title }}</p>
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
            <label class="block md:col-span-2">
              <span class="label">Description</span>
              <textarea v-model="form.description" rows="4" class="input"></textarea>
              <p v-if="form.errors.description" class="error-text">{{ form.errors.description }}</p>
            </label>
          </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">2. Placement Details</h2>
          <div class="mt-4 flex flex-wrap gap-2">
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="useCustomLocation ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'" @click="useCustomLocation = true">
              Custom Location
            </button>
            <button type="button" class="rounded-lg px-3 py-2 text-sm font-semibold" :class="!useCustomLocation ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700'" @click="useCustomLocation = false">
              Preset
            </button>
          </div>

          <div class="mt-4 grid gap-4 md:grid-cols-3">
            <label class="block md:col-span-2">
              <span class="label">Location</span>
              <input v-if="useCustomLocation" v-model="form.location" type="text" class="input">
              <select v-else v-model="form.location" class="input">
                <option value="">Select location</option>
                <option>Manila</option>
                <option>Cebu</option>
                <option>Davao</option>
                <option>Laguna</option>
              </select>
              <p v-if="form.errors.location" class="error-text">{{ form.errors.location }}</p>
            </label>
            <label class="block">
              <span class="label">Available Slots</span>
              <input v-model="form.slots" type="number" min="1" class="input">
              <p v-if="form.errors.slots" class="error-text">{{ form.errors.slots }}</p>
            </label>
            <label class="block">
              <span class="label">Closing Date</span>
              <input v-model="form.closing_date" type="date" :min="today" class="input">
              <p v-if="form.errors.closing_date" class="error-text">{{ form.errors.closing_date }}</p>
            </label>
          </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-bold text-slate-900">3. Skills Needed</h2>
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
            <p class="text-sm font-semibold text-slate-800">Add skill locally to this form</p>
            <div class="mt-3 grid gap-3 md:grid-cols-[1fr_1fr_auto]">
              <input v-model="newSkill.name" type="text" class="input bg-white" placeholder="Skill name">
              <input v-model="newSkill.category" type="text" class="input bg-white" placeholder="Category">
              <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-black" @click="addSkill">Add</button>
            </div>
          </div>
        </section>

        <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
          <a href="/employer/jobs" class="rounded-lg border border-slate-300 bg-white px-5 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
          <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50">
            {{ form.processing ? 'Updating...' : 'Update Job Slot' }}
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
