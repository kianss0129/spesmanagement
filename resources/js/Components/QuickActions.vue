<template>

  <!-- MAIN CARD -->
  <div
    class="relative overflow-hidden rounded-3xl border border-gray-100 bg-white/90 backdrop-blur-lg shadow-xl"
  >

    <!-- HEADER -->
    <div
      class="relative bg-gradient-to-r from-indigo-600 via-blue-600 to-cyan-600 px-6 py-6"
    >

      <!-- GLOW EFFECT -->
      <div
        class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full blur-3xl"
      ></div>

      <div
        class="relative flex items-center justify-between"
      >

        <div>

          <h2
            class="text-2xl font-extrabold text-white"
          >
            Quick Actions
          </h2>

          <p
            class="text-blue-100 text-sm mt-1"
          >
            Assign beneficiaries and manage applications instantly.
          </p>

        </div>

        <!-- ICON -->
        <div
          class="hidden md:flex items-center justify-center w-16 h-16 rounded-2xl bg-white/10 backdrop-blur-lg border border-white/10"
        >

          <svg
            class="w-8 h-8 text-white"
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

      </div>

    </div>

    <!-- CONTENT -->
    <div
      class="p-6"
    >

      <div
        class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start"
      >

        <!-- ASSIGN BENEFICIARY -->
        <form
          v-if="showAssignForm"
          @submit.prevent="assignBeneficiary"
          class="relative overflow-hidden rounded-3xl border border-gray-100 bg-gradient-to-br from-slate-50 to-white p-6 shadow-md"
        >

          <!-- SMALL GLOW -->
          <div
            class="absolute top-0 right-0 w-40 h-40 bg-indigo-100 rounded-full blur-3xl opacity-50"
          ></div>

          <div class="relative">

            <!-- TITLE -->
            <div
              class="flex items-center gap-3 mb-6"
            >

              <div
                class="flex items-center justify-center w-12 h-12 rounded-2xl bg-gradient-to-r from-indigo-500 to-blue-600 text-white shadow-lg"
              >

                <svg
                  class="w-6 h-6"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M18 9l-6 6-3-3"
                  />
                </svg>

              </div>

              <div>

                <h3
                  class="text-lg font-bold text-gray-800"
                >
                  Assign Beneficiary
                </h3>

                <p
                  class="text-sm text-gray-500"
                >
                  Match beneficiaries to available job listings.
                </p>

              </div>

            </div>

            <!-- JOB LISTING -->
            <div class="mb-5">

              <label
                class="block text-sm font-semibold text-gray-700 mb-2"
              >
                Job Listing ID
              </label>

              <input
                v-model="assignForm.job_listing_id"
                type="number"
                placeholder="Enter Job ID"
                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                required
              />

            </div>

            <!-- BENEFICIARY -->
            <div class="mb-6">

              <label
                class="block text-sm font-semibold text-gray-700 mb-2"
              >
                Beneficiary
              </label>

              <select
                v-model="assignForm.beneficiary_id"
                class="w-full rounded-2xl border border-gray-200 bg-white px-4 py-3 text-gray-700 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition"
                required
              >

                <option value="">
                  -- Select Beneficiary --
                </option>

                <option
                  v-for="beneficiary in beneficiaryOptions"
                  :key="beneficiary.id"
                  :value="beneficiary.id"
                >
                  #{{ beneficiary.id }} - {{ beneficiary.name }}
                </option>

              </select>

            </div>

            <!-- ACTION -->
            <div
              class="flex flex-col sm:flex-row sm:items-center gap-4"
            >

              <button
                type="submit"
                :disabled="!props.canAssign"
                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg hover:scale-105 hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
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
                    d="M5 13l4 4L19 7"
                  />
                </svg>

                Assign Beneficiary

              </button>

              <!-- MESSAGE -->
              <div
                v-if="assignMessage"
                class="text-sm font-medium"
                :class="
                  assignMessage.includes('permission') ||
                  assignMessage.includes('Failed')
                    ? 'text-red-600'
                    : 'text-green-600'
                "
              >
                {{ assignMessage }}
              </div>

            </div>

          </div>

        </form>

      </div>

    </div>

  </div>

</template>

<script setup>

import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  showAssignForm: {
    type: Boolean,
    default: true
  },

  canAssign: {
    type: Boolean,
    default: true
  },

  canSchedule: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['dataChanged'])

const applications = ref([])

const assignForm = ref({
  job_listing_id: null,
  beneficiary_id: null
})

const assignMessage = ref('')

/* ================= BENEFICIARY OPTIONS ================= */
const beneficiaryOptions = computed(() => {

  const map = {}

  applications.value.forEach(app => {

    if (app.beneficiary_id && !map[app.beneficiary_id]) {

      map[app.beneficiary_id] = {
        id: app.beneficiary_id,
        name:
          app.beneficiary_name ||
          `Beneficiary #${app.beneficiary_id}`
      }

    }

  })

  return Object.values(map)

})

/* ================= ASSIGN ================= */
async function assignBeneficiary() {

  assignMessage.value = ''

  if (!props.canAssign) {

    assignMessage.value =
      'You do not have permission to assign beneficiaries.'

    return
  }

  try {

    await axios.post(
      '/peso/assign-beneficiary',
      assignForm.value
    )

    assignMessage.value =
      'Beneficiary assigned successfully.'

    emit('dataChanged')

    /* RESET FORM */
    assignForm.value = {
      job_listing_id: null,
      beneficiary_id: null
    }

  } catch (e) {

    assignMessage.value =
      e?.response?.data?.message ||
      e?.response?.data?.errors?.beneficiary_id?.[0] ||
      e?.response?.data?.errors?.job_listing_id?.[0] ||
      'Failed to assign.'

    console.error('Assign failed', e)

  }

}

/* ================= LOAD APPLICATIONS ================= */
onMounted(async () => {

  try {

    const res = await axios.get('/peso/applications')

    applications.value = res.data

  } catch (e) {

    console.error(
      'Failed to load applications for dropdown',
      e
    )

  }

})

</script>