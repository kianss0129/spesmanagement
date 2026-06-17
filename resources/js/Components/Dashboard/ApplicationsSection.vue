<!-- resources/js/Components/ApplicationsSection.vue -->

<script setup>
defineProps({
  selectedTab: String,
  applications: {
    type: Array,
    default: () => [],
  },

  formatDate: Function,

  updateApplicationStatus: Function,

  markApplicationQualified: Function,

  viewApplication: Function,
})

function canApprove(application) {
  return application.status === 'qualified'
}

function canMarkQualified(application) {
  return application.status === 'interview_passed'
}
</script>

<template>
  <div
    v-if="selectedTab === 'applications'"
    class="space-y-6"
  >
    <div class="bg-white p-6 rounded-2xl shadow border border-gray-100">

      <!-- HEADER -->
      <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
          Applications Management
        </h2>

        <p class="text-sm text-gray-500 mt-1">
          Review and manage beneficiary applications.
        </p>
      </div>

      <!-- TABLE -->
      <div class="overflow-x-auto rounded-2xl border border-gray-200">
        <table class="w-full text-sm text-left">
          <thead class="bg-gray-100 text-gray-700">
            <tr>
              <th class="px-4 py-3">Applicant</th>
              <th class="px-4 py-3">Job Title</th>
              <th class="px-4 py-3">Employer</th>
              <th class="px-4 py-3">Applied Date</th>
              <th class="px-4 py-3">Status</th>
              <th class="px-4 py-3 text-center">Actions</th>
            </tr>
          </thead>

          <tbody>
            <tr
              v-for="application in applications"
              :key="application.id"
              class="border-b border-gray-200 hover:bg-gray-50"
            >
              <!-- APPLICANT -->
              <td class="px-4 py-3 font-medium text-gray-900">
                {{ application.applicant_name || application.beneficiary_name }}
              </td>

              <!-- JOB -->
              <td class="px-4 py-3">
                {{ application.job_title }}
              </td>

              <!-- EMPLOYER -->
              <td class="px-4 py-3">
                {{ application.employer_name }}
              </td>

              <!-- DATE -->
              <td class="px-4 py-3">
                {{ formatDate(application.created_at) }}
              </td>

              <!-- STATUS -->
              <td class="px-4 py-3">
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :class="{
                    'bg-yellow-100 text-yellow-700': application.status === 'pending',
                    'bg-blue-100 text-blue-700': application.status === 'reviewed',
                    'bg-green-100 text-green-700': application.status === 'approved',
                    'bg-red-100 text-red-700': application.status === 'rejected',
                  }"
                >
                  {{ application.status }}
                </span>
              </td>

              <!-- ACTIONS -->
              <td class="px-4 py-3">
                <div class="flex items-center justify-center gap-2 flex-wrap">

                  <!-- VIEW -->
                  <button
                    @click="viewApplication(application)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs"
                  >
                    View
                  </button>

                  <button
                    v-if="canMarkQualified(application)"
                    @click="markApplicationQualified(application)"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs"
                  >
                    Mark as Qualified
                  </button>

                  <!-- APPROVE -->
                  <button
                    v-if="canApprove(application)"
                    @click="updateApplicationStatus(application.id, 'approved')"
                    class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs"
                  >
                    Approve
                  </button>

                  <!-- REJECT -->
                  <button
                    v-if="application.status !== 'rejected'"
                    @click="updateApplicationStatus(application.id, 'rejected')"
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs"
                  >
                    Reject
                  </button>

                </div>
              </td>
            </tr>

            <!-- EMPTY -->
            <tr v-if="applications.length === 0">
              <td
                colspan="6"
                class="px-4 py-6 text-center text-gray-500"
              >
                No applications found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
  </div>
</template>
