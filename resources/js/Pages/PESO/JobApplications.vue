<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-100 p-6">

    <!-- Top Section -->
    <div
      class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between"
    >

      <!-- Left -->
      <div class="flex items-center gap-4">

        <!-- Back Button -->
        <button
          @click="goBack"
          class="group flex items-center gap-2 rounded-2xl border border-gray-200 bg-white px-5 py-3 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-600 hover:shadow-lg"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 transition-transform duration-300 group-hover:-translate-x-1"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15 19l-7-7 7-7"
            />
          </svg>

          Back to Dashboard
        </button>

        <!-- Divider -->
        <div class="hidden h-10 w-px bg-gray-200 lg:block"></div>

        <!-- Title -->
        <div>
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">
            Applicants
          </h1>

          <p class="mt-1 text-sm text-gray-500">
            Manage applicants for

            <span class="font-semibold text-indigo-600">
              {{ job?.title || 'Job Position' }}
            </span>
          </p>
        </div>
      </div>

      <!-- Stats -->
      <div class="flex flex-wrap gap-4">

        <!-- Total -->
        <div
          class="rounded-3xl border border-gray-100 bg-white px-6 py-5 shadow-sm transition hover:shadow-md"
        >
          <p class="text-sm font-medium text-gray-500">
            Total Applicants
          </p>

          <h2 class="mt-2 text-3xl font-bold text-gray-900">
            {{ applications.length }}
          </h2>
        </div>

        <!-- Pending -->
        <div
          class="rounded-3xl border border-yellow-100 bg-yellow-50 px-6 py-5 shadow-sm transition hover:shadow-md"
        >
          <p class="text-sm font-medium text-yellow-700">
            Pending
          </p>

          <h2 class="mt-2 text-3xl font-bold text-yellow-800">
            {{ pendingCount }}
          </h2>
        </div>

        <!-- Approved -->
        <div
          class="rounded-3xl border border-green-100 bg-green-50 px-6 py-5 shadow-sm transition hover:shadow-md"
        >
          <p class="text-sm font-medium text-green-700">
            Approved
          </p>

          <h2 class="mt-2 text-3xl font-bold text-green-800">
            {{ approvedCount }}
          </h2>
        </div>

        <!-- Rejected -->
        <div
          class="rounded-3xl border border-red-100 bg-red-50 px-6 py-5 shadow-sm transition hover:shadow-md"
        >
          <p class="text-sm font-medium text-red-700">
            Rejected
          </p>

          <h2 class="mt-2 text-3xl font-bold text-red-800">
            {{ rejectedCount }}
          </h2>
        </div>

      </div>
    </div>

    <!-- Table Container -->
    <div
      class="overflow-hidden rounded-[28px] border border-gray-200 bg-white shadow-sm"
    >

      <!-- Header -->
      <div
        class="flex flex-col gap-4 border-b border-gray-100 px-6 py-5 md:flex-row md:items-center md:justify-between"
      >

        <div>
          <h2 class="text-xl font-semibold text-gray-900">
            Applicant List
          </h2>

          <p class="mt-1 text-sm text-gray-500">
            Review applicant details and monitor application status.
          </p>
        </div>

        <!-- Search -->
        <div class="relative w-full md:w-80">

          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>

          <input
            v-model="search"
            type="text"
            placeholder="Search applicant..."
            class="w-full rounded-2xl border border-gray-200 bg-gray-50 py-3 pl-12 pr-4 text-sm outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-4 focus:ring-indigo-100"
          />
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">

        <table class="min-w-full">

          <thead class="bg-gray-50">
            <tr class="text-left text-sm font-semibold uppercase tracking-wide text-gray-500">
              <th class="px-6 py-4">Applicant</th>
              <th class="px-6 py-4">Email</th>
              <th class="px-6 py-4">Status</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">

            <tr
              v-for="app in filteredApplications"
              :key="app.id"
              class="group transition-all duration-200 hover:bg-indigo-50/40"
            >

              <!-- Applicant -->
              <td class="px-6 py-5">

                <div class="flex items-center gap-4">

                  <!-- Avatar -->
                  <div
                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 text-lg font-bold text-white shadow-sm"
                  >
                    {{
                      (app.beneficiary?.user?.name || 'N')
                        .charAt(0)
                        .toUpperCase()
                    }}
                  </div>

                  <!-- Name -->
                  <div>
                    <p
                      class="font-semibold text-gray-900 transition group-hover:text-indigo-600"
                    >
                      {{ app.beneficiary?.user?.name ?? 'N/A' }}
                    </p>

                    <p class="text-sm text-gray-500">
                      Applicant ID #{{ app.id }}
                    </p>
                  </div>

                </div>
              </td>

              <!-- Email -->
              <td class="px-6 py-5">
                <span class="text-gray-700">
                  {{ app.beneficiary?.user?.email ?? 'N/A' }}
                </span>
              </td>

              <!-- Status -->
              <td class="px-6 py-5">

                <span
                  class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                  :class="statusClass(app.status)"
                >
                  <span
                    class="h-2.5 w-2.5 rounded-full"
                    :class="dotClass(app.status)"
                  ></span>

                  {{ app.status || 'Pending' }}
                </span>

              </td>

            </tr>

            <!-- Empty State -->
            <tr v-if="filteredApplications.length === 0">

              <td colspan="3" class="px-6 py-20 text-center">

                <div class="mx-auto flex max-w-sm flex-col items-center">

                  <!-- Icon -->
                  <div
                    class="mb-5 flex h-24 w-24 items-center justify-center rounded-full bg-gray-100"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="h-12 w-12 text-gray-400"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                      stroke-width="1.5"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M17 20h5V4H2v16h5m10 0v-4a3 3 0 00-3-3H10a3 3 0 00-3 3v4m10 0H7"
                      />
                    </svg>
                  </div>

                  <h3 class="text-xl font-semibold text-gray-900">
                    No applicants found
                  </h3>

                  <p class="mt-2 text-sm leading-relaxed text-gray-500">
                    Applicants who apply for this job will appear here.
                  </p>

                </div>

              </td>

            </tr>

          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  job: Object,

  applications: {
    type: Array,
    default: () => []
  }
})

const job = props.job || {}
const applications = props.applications || []

const search = ref('')

/* BACK BUTTON */
const goBack = () => {
  router.visit('/dashboard')
}

/* FILTER SEARCH */
const filteredApplications = computed(() => {
  return applications.filter(app => {
    const name =
      app.beneficiary?.user?.name?.toLowerCase() || ''

    const email =
      app.beneficiary?.user?.email?.toLowerCase() || ''

    const keyword = search.value.toLowerCase()

    return (
      name.includes(keyword) ||
      email.includes(keyword)
    )
  })
})

/* COUNTS */
const pendingCount = computed(() => {
  return applications.filter(
    app => (app.status || 'Pending') === 'Pending'
  ).length
})

const approvedCount = computed(() => {
  return applications.filter(
    app => app.status === 'Approved'
  ).length
})

const rejectedCount = computed(() => {
  return applications.filter(
    app => app.status === 'Rejected'
  ).length
})

/* STATUS COLORS */
const statusClass = (status) => {
  switch (status) {
    case 'Approved':
      return 'bg-green-100 text-green-700'

    case 'Rejected':
      return 'bg-red-100 text-red-700'

    default:
      return 'bg-yellow-100 text-yellow-700'
  }
}

const dotClass = (status) => {
  switch (status) {
    case 'Approved':
      return 'bg-green-500'

    case 'Rejected':
      return 'bg-red-500'

    default:
      return 'bg-yellow-500'
  }
}
</script>