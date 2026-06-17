
<template>
  <div class="bg-white rounded-lg shadow-lg p-6 border border-slate-200 sticky top-6">
    <h2 class="text-lg font-semibold text-slate-900 mb-4 flex items-center gap-2">
      <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 5.707A1 1 0 013 5V3zm12-1a2 2 0 00-2-2H7a2 2 0 00-2 2v2.05a2 2 0 01-.516 1.414l-2.293 2.293a2 2 0 00-.293 2.415A2 2 0 004 10h12a2 2 0 001.897-1.184 2 2 0 00-.293-2.415L14.516 6.464A2 2 0 0014 5.05V2z" clip-rule="evenodd" />
      </svg>
      Filters
    </h2>


    <div class="space-y-4">
      <!-- Search -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
          🔍 Search
        </label>
        <input
          v-model="localFilters.search"
          type="text"
          placeholder="Insert Email"
          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
          @keyup.enter="applyFilters"
        />
      </div>


      <!-- Skills Filter -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
          💡 Skills
        </label>
        <div v-if="skillsLoading" class="text-sm text-slate-600">Loading skills...</div>
        <div v-else class="space-y-2 max-h-48 overflow-y-auto border border-slate-200 rounded-lg p-3 bg-slate-50">
          <div v-for="(categorySkills, category) in groupedSkills" :key="category">
            <div class="font-medium text-xs text-slate-700 mb-2 sticky top-0 bg-slate-50 py-1">
              {{ category }}
            </div>
            <div class="space-y-1 ml-2">
              <label
                v-for="skill in categorySkills"
                :key="skill.id"
                class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer"
              >
                <input
                  type="checkbox"
                  :value="skill.id"
                  v-model="localFilters.skills"
                  class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
                />
                <span>{{ skill.name }}</span>
              </label>
            </div>
          </div>
        </div>
        <p v-if="localFilters.skills.length > 0" class="text-xs text-indigo-600 mt-2">
          {{ localFilters.skills.length }} skills selected
        </p>
      </div>


      <!-- Employment Status Filter -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
          💼 Employment Status
        </label>
        <select
          v-model="localFilters.employment_status"
          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
        >
          <option value="">All</option>
          <option value="unemployed">Unemployed</option>
          <option value="underemployed">Underemployed</option>
          <option value="employed">Employed</option>
        </select>
      </div>


      <!-- Education Level Filter -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
          🎓 Education Level
        </label>
        <select
          v-model="localFilters.education_level"
          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
        >
          <option value="">All</option>
          <option
            v-for="level in educationLevels"
            :key="level"
            :value="level"
          >
            {{ level }}
          </option>
        </select>
      </div>


      <!-- Location Filter -->
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">
          📍 Location
        </label>
        <input
          v-model="localFilters.location"
          type="text"
          placeholder="City or barangay..."
          class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
        />
      </div>


      <!-- Exclude Assigned Toggle -->
      <div class="flex items-center gap-2 pt-2 border-t border-slate-200">
        <input
          v-model="localFilters.exclude_assigned"
          type="checkbox"
          class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500"
        />
        <label class="text-sm text-slate-700 cursor-pointer">
          Show unassigned only
        </label>
      </div>


      <!-- Buttons -->
      <div class="flex gap-2 pt-4 border-t border-slate-200">
        <button
          @click="applyFilters"
          class="flex-1 px-4 py-2 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition font-medium text-sm"
        >
          Apply
        </button>
        <button
          @click="resetFilters"
          class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition font-medium text-sm"
        >
          Reset
        </button>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, onMounted } from 'vue'


// Props & Emits
const emit = defineEmits(['update-filters', 'refresh'])


// State
const localFilters = ref({
  skills: [],
  employment_status: '',
  location: '',
  education_level: '',
  search: '',
  exclude_assigned: true
})


const allSkills = ref([])
const skillsLoading = ref(false)
const educationLevels = ref([])


// Computed
const groupedSkills = computed(() => {
  const grouped = {}
  allSkills.value.forEach(skill => {
    const categoryName = skill.category || 'Other'
    if (!grouped[categoryName]) {
      grouped[categoryName] = []
    }
    grouped[categoryName].push(skill)
  })
  return grouped
})


// Methods
const loadSkills = async () => {
  skillsLoading.value = true
  try {
    const response = await fetch('/admin/skills-for-filter')
    const data = await response.json()
   
    // Flatten grouped skills
    const skillsList = []
    Object.values(data).forEach(categorySkills => {
      if (Array.isArray(categorySkills)) {
        skillsList.push(...categorySkills)
      }
    })
    allSkills.value = skillsList
  } catch (error) {
    console.error('Error loading skills:', error)
  } finally {
    skillsLoading.value = false
  }
}


const loadEducationLevels = async () => {
  try {
    const response = await fetch('/admin/education-levels')
    const data = await response.json()
    educationLevels.value = data
  } catch (error) {
    console.error('Error loading education levels:', error)
  }
}


const applyFilters = () => {
  emit('update-filters', localFilters.value)
}


const resetFilters = () => {
  localFilters.value = {
    skills: [],
    employment_status: '',
    location: '',
    education_level: '',
    search: '',
    exclude_assigned: true
  }
  emit('update-filters', localFilters.value)
}


// Lifecycle
onMounted(() => {
  loadSkills()
  loadEducationLevels()
})
</script>



