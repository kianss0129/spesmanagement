

<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-2xl w-full p-0 my-8 shadow-2xl">
      <!-- Header -->
      <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-6 text-white flex justify-between items-start">
        <div>
          <h2 class="text-2xl font-bold">{{ beneficiary?.first_name }} {{ beneficiary?.last_name }}</h2>
          <p class="text-indigo-100 mt-1">{{ beneficiary?.email }}</p>
        </div>
        <button
          @click="$emit('close')"
          class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>


      <!-- Content -->
      <div v-if="loading" class="p-8 text-center">
        <div class="inline-block">
          <div class="w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
        </div>
        <p class="text-slate-600 mt-2">Loading profile...</p>
      </div>

      <div v-else-if="error" class="p-6">
        <div class="rounded-lg bg-red-50 border border-red-200 p-4">
          <p class="font-semibold text-red-800">Error Loading Beneficiary</p>
          <p class="text-red-700 text-sm mt-2">{{ error }}</p>
          <button
            @click="loadBeneficiary"
            class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
          >
            Try Again
          </button>
        </div>
      </div>

      <div v-else class="p-6 space-y-6 max-h-96 overflow-y-auto">
        <!-- Basic Info -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p class="text-xs font-semibold text-slate-600 uppercase">Phone</p>
            <p class="text-slate-900">{{ beneficiary?.contact_number || beneficiary?.phone || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-600 uppercase">Email</p>
            <p class="text-slate-900">{{ beneficiary?.email }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-600 uppercase">Birthdate</p>
            <p class="text-slate-900">{{ beneficiary?.birthdate || beneficiary?.birth_date || 'N/A' }}</p>
          </div>
          <div>
            <p class="text-xs font-semibold text-slate-600 uppercase">Gender</p>
            <p class="text-slate-900">{{ beneficiary?.gender || beneficiary?.sex || 'N/A' }}</p>
          </div>
        </div>


        <!-- Address -->
        <div class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">📍 Address</h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-slate-600 font-semibold">Barangay</p>
              <p class="text-slate-900">{{ beneficiary?.barangay || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">City</p>
              <p class="text-slate-900">{{ beneficiary?.city || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">Province</p>
              <p class="text-slate-900">{{ beneficiary?.province || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">Full Address</p>
              <p class="text-slate-900">{{ beneficiary?.address || 'N/A' }}</p>
            </div>
          </div>
        </div>


        <!-- Beneficiary Category -->
        <div class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">📋 Beneficiary Category</h3>
          <div class="flex items-center gap-3">
            <span v-if="beneficiary?.category" class="px-4 py-2 rounded-full font-semibold text-white" :class="{
              'bg-blue-500': beneficiary?.category?.toLowerCase() === 'student',
              'bg-purple-500': beneficiary?.category?.toLowerCase() === 'osy',
              'bg-orange-500': beneficiary?.category?.toLowerCase() === 'dependent of displaced worker',
              'bg-slate-500': !['student', 'osy', 'dependent of displaced worker'].includes(beneficiary?.category?.toLowerCase())
            }">
              {{ beneficiary?.category }}
            </span>
            <span v-else class="text-slate-600">Not specified</span>
          </div>
        </div>


        <!-- Education -->
        <div class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">🎓 Education</h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-slate-600 font-semibold">Highest Attainment</p>
              <p class="text-slate-900">{{ beneficiary?.highest_attainment || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">School</p>
              <p class="text-slate-900">{{ beneficiary?.school?.name || beneficiary?.school_name || 'N/A' }}</p>
            </div>
          </div>
        </div>


        <!-- Skills -->
        <div class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">💡 Skills</h3>
          <div v-if="beneficiary?.skills && beneficiary.skills.length > 0" class="flex flex-wrap gap-2">
            <span
              v-for="skill in beneficiary.skills"
              :key="skill.id"
              class="inline-block px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm font-medium"
            >
              {{ skill.name }}
            </span>
          </div>
          <p v-else class="text-slate-600">No skills added</p>
        </div>


        <!-- Employment Info -->
        <div class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">💼 Employment</h3>
          <div class="grid grid-cols-2 gap-3 text-sm">
            <div>
              <p class="text-xs text-slate-600 font-semibold">Status</p>
              <p class="text-slate-900 capitalize">{{ beneficiary?.employment_status || 'Not assigned' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">Current Employer</p>
              <p class="text-slate-900">{{ beneficiary?.employer?.company_name || 'N/A' }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-600 font-semibold">Current Job</p>
              <p class="text-slate-900" v-if="beneficiary?.job_id">
                <!-- Will show job title from job_listing relationship if available -->
                Job ID: {{ beneficiary?.job_id }}
              </p>
              <p v-else class="text-slate-900">N/A</p>
            </div>
          </div>
        </div>


        <!-- Previous Employment -->
        <div v-if="beneficiary?.previous_spes" class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-2">📊 Previous SPES</h3>
          <p class="text-slate-700">
            Has participated in SPES before ({{ beneficiary?.spes_count }} time(s))
          </p>
        </div>


        <!-- Family Info -->
        <div v-if="beneficiary?.father_name || beneficiary?.mother_name" class="border-t border-slate-200 pt-4">
          <h3 class="font-semibold text-slate-900 mb-3">👨‍👩‍👧‍👦 Family</h3>
          <div class="text-sm space-y-2">
            <div v-if="beneficiary?.father_name">
              <p class="text-xs text-slate-600 font-semibold">Father</p>
              <p class="text-slate-900">{{ beneficiary?.father_name }}</p>
            </div>
            <div v-if="beneficiary?.mother_name">
              <p class="text-xs text-slate-600 font-semibold">Mother</p>
              <p class="text-slate-900">{{ beneficiary?.mother_name }}</p>
            </div>
          </div>
        </div>


        <!-- Approval Status -->
        <div class="border-t border-slate-200 pt-4 bg-slate-50 -mx-6 -mb-6 px-6 py-4 rounded-b-lg">
          <div class="flex justify-between items-center">
            <div>
              <p class="text-xs font-semibold text-slate-600 uppercase">Approval Status</p>
              <p class="text-slate-900 capitalize font-semibold">{{ beneficiary?.approval_status }}</p>
            </div>
            <div class="text-right">
              <p class="text-xs font-semibold text-slate-600 uppercase">Applications</p>
              <p class="text-slate-900 text-lg font-bold">{{ beneficiary?.applications?.length || 0 }}</p>
            </div>
          </div>
        </div>
      </div>


      <!-- Footer with Actions -->
      <div v-if="!loading" class="border-t border-slate-200 p-6 bg-slate-50 flex gap-3">
        <button
          @click="$emit('close')"
          class="flex-1 px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300 transition font-medium"
        >
          Close
        </button>
        <button
          v-if="!beneficiary?.employer_id"
          @click="assignBeneficiary"
          class="flex-1 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition font-medium"
        >
          Assign to Job
        </button>
        <div v-else class="flex-1 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg font-medium flex items-center justify-center">
          ✓ Already Assigned
        </div>
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'


// Props & Emits
const props = defineProps({
  beneficiaryId: {
    type: [String, Number],
    required: true
  }
})


const emit = defineEmits(['close', 'assign'])


// State
const beneficiary = ref(null)
const loading = ref(false)
const error = ref(null)


// Methods
const loadBeneficiary = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await fetch(`/admin/beneficiaries/${props.beneficiaryId}`)
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: Failed to load beneficiary profile`)
    }
    
    const data = await response.json()
    beneficiary.value = data
  } catch (err) {
    error.value = err.message || 'Failed to load beneficiary profile'
    console.error('Error loading beneficiary:', err)
  } finally {
    loading.value = false
  }
}


const assignBeneficiary = () => {
  emit('assign', beneficiary.value)
}


// Lifecycle
onMounted(() => {
  loadBeneficiary()
})
</script>



