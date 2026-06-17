<template>

  <div
    v-if="selectedTab === 'jobs'"
    class="space-y-6"
  >

    <!-- MAIN CARD -->
    <div
      class="space-y-6"
    >

      <!-- HEADER -->
      <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4"
      >

      

        <div
          class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 w-full"
        >

          <div>

            <h2
              class="text-2xl md:text-3xl font-bold text-gray-800"
            >
              Job Listing & Application Management
            </h2>

            <p
              class="text-sm text-gray-500 mt-1"
            >
              Manage job listings, monitor applications, and review applicants.
            </p>

          </div>

          <!-- STATS -->
          <div
            class="flex items-center gap-4"
          >

            <div
              class="bg-white border border-gray-200 rounded-2xl px-5 py-3 shadow-sm min-w-[150px]"
            >

              <div
                class="text-xs uppercase tracking-wide text-gray-400"
              >
                Total Jobs
              </div>

              <div
                class="text-2xl font-bold text-blue-600"
              >
                {{ jobListings.length }}
              </div>

            </div>

          </div>

        </div>

      </div>

      <!-- CONTENT -->
      <div
        class="space-y-6"
      >

        <!-- QUICK ACTIONS -->
        <QuickActions
          v-if="isAdmin || isPesoAdmin || isPesoUser"
          :can-assign="isAdmin || isPesoAdmin"
          :can-schedule="isAdmin || isPesoAdmin"
          @data-changed="loadData"
          class="mb-8"
        />

        <!-- TABLE CONTAINER -->
        <div
          class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden"
        >

          <div
            class="overflow-x-auto"
          >

            <table
              class="w-full min-w-full"
            >

              <!-- HEAD -->
              <thead
                class="bg-blue-50"
              >
              

                <tr>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Job ID
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Job Title
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Employer
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Slots
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Closing Date
                  </th>

                  

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Applications
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-gray-700"
                  >
                    Actions
                  </th>

                </tr>

              </thead>

              <!-- BODY -->
              <tbody
                class="divide-y divide-gray-100 bg-white"
              >

                <tr
                  v-for="job in jobListings"
                  :key="job.id"
                  class="hover:bg-blue-50/40 transition"
                >

                  <!-- JOB ID -->
                  <td
                    class="px-6 py-5"
                  >

                    <div
                      class="w-11 h-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold shadow"
                    >
                      {{ job.id }}
                    </div>

                  </td>

                  <!-- TITLE -->
                  <td
                    class="px-6 py-5"
                  >

                    <div>

                      <p
                        class="font-bold text-gray-800"
                      >
                        {{ job.title }}
                      </p>

                      <p
                        class="text-xs text-gray-400 mt-1"
                      >
                        Job Listing
                      </p>

                    </div>

                  </td>

                  <!-- EMPLOYER -->
                  <td
                    class="px-6 py-5"
                  >

                    <span
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                    >
                      {{ job.employer_name }}
                    </span>

                  </td>

                  <!-- SLOTS -->
                  <td
                    class="px-6 py-5"
                  >

                    <div
                      class="font-semibold text-gray-700"
                    >
                      {{ job.slots ?? 'N/A' }}
                    </div>

                  </td>

                  <!-- DATE -->
                  <td
                    class="px-6 py-5"
                  >

                    <div
                      class="text-sm text-gray-600"
                    >
                      {{
                        job.closing_date
                          ? formatDate(job.closing_date)
                          : 'N/A'
                      }}
                    </div>

                  </td>

                 
                  <!-- APPLICATIONS -->
                  <td
                    class="px-6 py-5"
                  >

                    <div
                      class="inline-flex items-center gap-2 rounded-full bg-emerald-100 px-3 py-1"
                    >

                      <span
                        class="w-2 h-2 rounded-full bg-emerald-500"
                      ></span>

                      <span
                        class="font-bold text-emerald-700"
                      >
                        {{ job.applications_count }}
                      </span>

                    </div>

                  </td>

                  <!-- ACTION -->
                  <td
                    class="px-6 py-5"
                  >

                    <div class="flex gap-2">
                      <button
                        @click="openMatchesModal(job)"
                        class="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition"
                        v-if="isAdmin"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"
                          />
                        </svg>
                        Matches
                      </button>

                      <button
                        @click="viewApplications(job.id)"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded-xl text-sm font-medium transition"
                      >
                        <svg
                          class="w-4 h-4"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M15 12H9m12 0A9 9 0 113 12a9 9 0 0118 0z"
                          />
                        </svg>
                        Apps
                      </button>
                    </div>

                  </td>

                </tr>

                <!-- EMPTY -->
                <tr
                  v-if="jobListings.length === 0"
                >

                  <td
                    colspan="7"
                    class="px-6 py-16 text-center"
                  >

                    <div
                      class="flex flex-col items-center"
                    >

                      <div
                        class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mb-4"
                      >

                        <svg
                          class="w-12 h-12 text-gray-400"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 17v-2a4 4 0 014-4h4"
                          />
                        </svg>

                      </div>

                      <h3
                        class="text-xl font-bold text-gray-700"
                      >
                        No Job Listings Found
                      </h3>

                      <p
                        class="text-sm text-gray-400 mt-2"
                      >
                        There are currently no available jobs.
                      </p>

                    </div>

                  </td>

                </tr>

              </tbody>

            </table>

          </div>

        </div>

      </div>

    </div>

  </div>

  <!-- MATCHING BENEFICIARIES MODAL -->
  <div
    v-if="showMatchesModal"
    class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    @click="closeMatchesModal"
  >
    <div
      class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto"
      @click.stop
    >
      <!-- Modal Header -->
      <div class="sticky top-0 bg-blue-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <div>
          <h3 class="text-xl font-bold text-gray-800">
            Matching Beneficiaries
          </h3>
          <p class="text-sm text-gray-500 mt-1">
            {{ selectedJob?.title }} - {{ matchingBeneficiaries.length }} matches found
          </p>
        </div>
        <button
          @click="closeMatchesModal"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-6">
        <div v-if="loadingMatches" class="text-center py-8">
          <div class="inline-block animate-spin">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
          </div>
          <p class="text-gray-500 mt-2">Loading matches...</p>
        </div>

        <div v-else-if="matchingBeneficiaries.length === 0" class="text-center py-8">
          <p class="text-gray-500">No matching beneficiaries found for this job.</p>
        </div>

        <div v-else class="space-y-3">
          <div
            v-for="beneficiary in matchingBeneficiaries"
            :key="beneficiary.id"
            class="border border-gray-200 rounded-lg p-4 hover:bg-blue-50/30 transition"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold">
                    {{ beneficiary.name.charAt(0).toUpperCase() }}
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-800">{{ beneficiary.name }}</h4>
                    <p class="text-sm text-gray-500">{{ beneficiary.email }}</p>
                  </div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-3 text-sm">
                  <div>
                    <span class="text-gray-500">Location:</span>
                    <p class="font-medium text-gray-700">{{ beneficiary.city || beneficiary.location || 'N/A' }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Contact:</span>
                    <p class="font-medium text-gray-700">{{ beneficiary.phone || beneficiary.contact_number || 'N/A' }}</p>
                  </div>
                  <div>
                    <span class="text-gray-500">Match Score:</span>
                    <div class="flex items-center gap-2 mt-1">
                      <span class="text-lg font-bold text-blue-600">{{ matchScoreLabel(beneficiary.match_score) }}</span>
                      <span
                        class="px-2 py-1 rounded text-xs font-semibold"
                        :class="getMatchBadgeClass(beneficiary.match_score)"
                      >
                        {{ matchLevelLabel(beneficiary.match_score) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <button
                @click="assignBeneficiaryToJob(beneficiary)"
                class="ml-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
              >
                Assign
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>

import QuickActions from '@/Components/QuickActions.vue'
import axios from 'axios'
import { onMounted, watch, ref } from 'vue'

const props = defineProps({
  selectedTab: String,
  isAdmin: Boolean,
  isPesoAdmin: Boolean,
  isPesoUser: Boolean,
  jobListings: Array,
  formatDate: Function,
  viewApplications: Function,
  loadData: Function
})

// Modal state
const showMatchesModal = ref(false)
const selectedJob = ref(null)
const matchingBeneficiaries = ref([])
const loadingMatches = ref(false)

// Load matched jobs with skill matching scores
const loadMatchedJobs = async () => {
  try {
    // Determine endpoint based on user role
    const endpoint = props.isAdmin ? '/admin/jobs/matched' : '/jobs/matched'
    
    if (import.meta.env.DEV) {
      console.log('Loading matched jobs from:', endpoint)
      console.log('Is Admin:', props.isAdmin)
    }
    
    const response = await axios.get(endpoint)
    if (import.meta.env.DEV) {
      console.log('Matched jobs response:', response.data)
    }
    
    if (response.data && Array.isArray(response.data)) {
      // Update jobListings with matched data
      // This will merge match_score and match_level into the existing jobs
      props.jobListings.forEach(job => {
        const matchedJob = response.data.find(j => j.id === job.id)
        if (matchedJob) {
          job.match_score = matchedJob.match_score ?? null
          job.match_level = matchedJob.match_level || matchLevelLabel(job.match_score)

          if (import.meta.env.DEV) {
            console.log(`Job ${job.id}: ${matchScoreLabel(job.match_score)} (${job.match_level})`)
          }
        }
      })
    }
  } catch (error) {
    console.error('Error loading matched jobs:', error.response?.data || error.message)
  }
}

const getMatchColorClass = (matchLevel) => {
  if (!matchLevel) return 'bg-gray-100 text-gray-700'
  
  const level = String(matchLevel).toLowerCase()
  
  if (level === 'high') {
    return 'bg-green-100 text-green-700'
  } else if (level === 'medium') {
    return 'bg-yellow-100 text-yellow-700'
  } else if (level === 'low') {
    return 'bg-red-100 text-red-700'
  }
  
  return 'bg-gray-100 text-gray-700'
}

const getMatchBadgeClass = (matchScore) => {
  if (matchScore === null || matchScore === undefined || Number.isNaN(Number(matchScore))) {
    return 'bg-blue-100 text-blue-700'
  }

  if (matchScore >= 80) {
    return 'bg-green-100 text-green-700'
  } else if (matchScore >= 50) {
    return 'bg-yellow-100 text-yellow-700'
  } else {
    return 'bg-red-100 text-red-700'
  }
}

const matchScoreLabel = (matchScore) => {
  return matchScore === null || matchScore === undefined || Number.isNaN(Number(matchScore))
    ? 'Suggested match'
    : `${Math.round(Number(matchScore))}%`
}

const matchLevelLabel = (matchScore) => {
  if (matchScore === null || matchScore === undefined || Number.isNaN(Number(matchScore))) {
    return 'Suggested'
  }

  return matchScore >= 80 ? 'High' : matchScore >= 50 ? 'Medium' : 'Low'
}

const openMatchesModal = async (job) => {
  selectedJob.value = job
  showMatchesModal.value = true
  loadingMatches.value = true
  matchingBeneficiaries.value = []

  try {
    if (import.meta.env.DEV) {
      console.log(`Fetching matching beneficiaries for job ${job.id}`)
    }
    const response = await axios.get(`/admin/jobs/${job.id}/matching-beneficiaries`)
    if (import.meta.env.DEV) {
      console.log('Matching beneficiaries response:', response.data)
    }
    
    if (response.data && response.data.suggestions) {
      // Sort by match_score in descending order (highest match first)
      const sorted = response.data.suggestions.sort((a, b) => (b.match_score || 0) - (a.match_score || 0))
      matchingBeneficiaries.value = sorted
      if (import.meta.env.DEV) {
        console.log(`Found ${sorted.length} matching beneficiaries`)
      }
    }
  } catch (error) {
    console.error('Error loading matching beneficiaries:', error.response?.data || error.message)
    matchingBeneficiaries.value = []
  } finally {
    loadingMatches.value = false
  }
}

const closeMatchesModal = () => {
  showMatchesModal.value = false
  selectedJob.value = null
  matchingBeneficiaries.value = []
}

const assignBeneficiaryToJob = async (beneficiary) => {
  try {
    await axios.post(`/admin/beneficiaries/${beneficiary.id}/assign`, {
      job_id: selectedJob.value.id
    })

    // Show success message
    alert(`${beneficiary.name} assigned successfully!`)
    
    // Refresh data and close modal
    closeMatchesModal()
    props.loadData()
  } catch (error) {
    console.error('Error assigning beneficiary:', error)
    alert('Failed to assign beneficiary. Please try again.')
  }
}

// Load matched jobs when component mounts or jobs update
onMounted(() => {
  if (props.jobListings && props.jobListings.length > 0) {
    loadMatchedJobs()
  }
})

watch(() => props.jobListings, () => {
  if (props.jobListings && props.jobListings.length > 0) {
    loadMatchedJobs()
  }
})

</script>
