<script setup>
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { route } from 'ziggy-js'

const props = defineProps({
  user: Object,
  employer: Object,
})

const steps = [
  { number: 1, title: 'Company Information', description: 'Add your registered company details.' },
  { number: 2, title: 'Authorized Representatives', description: 'Enter the main contact persons for your company.' },
  { number: 3, title: 'Company Contact & SPES Details', description: 'Provide supporting contact details and SPES participation notes.' },
  { number: 4, title: 'Document Uploads', description: 'Upload the required onboarding documents.' },
  { number: 5, title: 'Review & Submit', description: 'Confirm your information and send your application.' },
]

const activeStep = ref(1)

const sanFernandoBarangays = [
  'Alasas', 'Baliti', 'Bulaon', 'Calulut', 'Dela Paz Norte',
  'Dela Paz Sur', 'Del Carmen', 'Del Pilar', 'Del Rosario', 'Dolores',
  'Juliana', 'Lara', 'Lourdes', 'Magliman', 'Maimpis',
  'Malino', 'Malpitic', 'Pandaras', 'Panipuan', 'Pulung Bulu',
  'Quebiawan', 'Saguin', 'San Agustin', 'San Felipe', 'San Isidro',
  'San Jose', 'San Juan', 'San Nicolas', 'San Pedro', 'Santa Lucia',
  'Santa Teresita', 'Santo Niño', 'Santo Rosario', 'Sindalan', 'Telabastagan',
]

const form = useForm({
  company_name: props.employer?.company_name || '',
  business_trade_name: props.employer?.details?.business_trade_name || '',
  nature_of_business: props.employer?.details?.nature_of_business || '',
  industry_type: props.employer?.details?.industry_type || '',
  sector: props.employer?.details?.sector || '',
  company_website: props.employer?.details?.company_website || '',
  facebook_page: props.employer?.details?.facebook_page || '',
  number_of_employees: props.employer?.details?.number_of_employees || '',
  house_number: props.employer?.details?.house_number || '',
  street: props.employer?.details?.street || '',
  barangay: sanFernandoBarangays.includes(props.employer?.details?.barangay) ? props.employer.details.barangay : '',
  city: 'San Fernando',
  province: 'Pampanga',
  zip_code: props.employer?.details?.zip_code || '',

  auth_first_name: props.employer?.details?.authorized_representative?.first_name || '',
  auth_middle_name: props.employer?.details?.authorized_representative?.middle_name || '',
  auth_last_name: props.employer?.details?.authorized_representative?.last_name || '',
  auth_suffix: props.employer?.details?.authorized_representative?.suffix || '',
  auth_position: props.employer?.details?.authorized_representative?.position || '',
  auth_mobile: props.employer?.details?.authorized_representative?.mobile || '',
  auth_email: props.employer?.details?.authorized_representative?.email || '',

  contact_first_name: props.employer?.details?.contact_person?.first_name || '',
  contact_middle_name: props.employer?.details?.contact_person?.middle_name || '',
  contact_last_name: props.employer?.details?.contact_person?.last_name || '',
  contact_suffix: props.employer?.details?.contact_person?.suffix || '',
  contact_position: props.employer?.details?.contact_person?.position || '',
  contact_mobile: props.employer?.details?.contact_person?.mobile || '',
  contact_email: props.employer?.details?.contact_person?.email || '',

  finance_first_name: props.employer?.details?.finance_officer?.first_name || '',
  finance_middle_name: props.employer?.details?.finance_officer?.middle_name || '',
  finance_last_name: props.employer?.details?.finance_officer?.last_name || '',
  finance_suffix: props.employer?.details?.finance_officer?.suffix || '',
  finance_position: props.employer?.details?.finance_officer?.position || '',
  finance_mobile: props.employer?.details?.finance_officer?.mobile || '',
  finance_email: props.employer?.details?.finance_officer?.email || '',

  company_phone: props.employer?.details?.company_contact?.telephone_number || '',
  company_mobile: props.employer?.details?.company_contact?.mobile_number || '',
  company_email_official: props.employer?.details?.company_contact?.official_company_email || '',
  alternative_email: props.employer?.details?.company_contact?.alternative_email || '',

  previous_participation: props.employer?.details?.spes_participation?.previous_participation || '',
  years_participated: props.employer?.details?.spes_participation?.years_participated || '',
  preferred_beneficiaries: props.employer?.details?.spes_participation?.preferred_beneficiaries || '',
  preferred_department: props.employer?.details?.spes_participation?.preferred_department || '',
  employment_period: props.employer?.details?.spes_participation?.employment_period || '',
  work_schedules: props.employer?.details?.spes_participation?.work_schedules || '',
  work_assignments: props.employer?.details?.spes_participation?.work_assignments || '',

  position_title: props.employer?.details?.employment_opportunities?.position_title || '',
  number_of_vacancies: props.employer?.details?.employment_opportunities?.number_of_vacancies || '',
  minimum_qualification: props.employer?.details?.employment_opportunities?.minimum_qualification || '',
  assigned_department: props.employer?.details?.employment_opportunities?.assigned_department || '',
  work_schedule: props.employer?.details?.employment_opportunities?.work_schedule || '',
  expected_duration: props.employer?.details?.employment_opportunities?.expected_duration || '',

  business_permit: null,
  mayors_permit: null,
  registration_certificate: null,
  bir_certificate: null,
  letter_of_intent: null,
  supporting_documents: [],
  declaration: false,
})

const stepProgress = computed(() => Math.round(((activeStep.value - 1) / (steps.length - 1)) * 100))

const requiredDocumentFields = [
  'business_permit',
  'mayors_permit',
  'registration_certificate',
  'bir_certificate',
  'letter_of_intent',
]

const hasRequiredDocuments = computed(() => {
  return requiredDocumentFields.every((field) => form[field] instanceof File)
})

const canAdvance = computed(() => {
  if (activeStep.value === 1) {
    return (
      form.company_name &&
      form.nature_of_business &&
      form.industry_type &&
      form.sector &&
      form.house_number &&
      form.street &&
      form.barangay &&
      form.city &&
      form.province
    )
  }

  if (activeStep.value === 2) {
    return (
      form.auth_first_name &&
      form.auth_last_name &&
      form.auth_position &&
      form.auth_mobile &&
      form.auth_email &&
      form.contact_first_name &&
      form.contact_last_name &&
      form.contact_position &&
      form.contact_mobile &&
      form.contact_email
    )
  }

  if (activeStep.value === 3) {
    return (
      form.company_mobile &&
      form.company_email_official &&
      form.previous_participation &&
      form.preferred_beneficiaries &&
      form.position_title &&
      form.number_of_vacancies
    )
  }

  if (activeStep.value === 4) {
    return hasRequiredDocuments.value
  }

  return true
})

const toast = ref({
  show: false,
  type: 'success',
  message: '',
})

let toastTimer = null

function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true

  if (toastTimer) {
    clearTimeout(toastTimer)
  }

  toastTimer = setTimeout(() => {
    toast.value.show = false
  }, 4000)
}

function setActiveStep(number) {
  if (number <= activeStep.value) {
    activeStep.value = number
  }
}

function nextStep() {
  if (activeStep.value === steps.length) {
    return
  }

  if (!canAdvance.value) {
    showToast('Please complete the required fields before continuing.', 'error')
    return
  }

  activeStep.value += 1
}

function previousStep() {
  if (activeStep.value > 1) {
    activeStep.value -= 1
  }
}

function formatPhoneInput(field, event) {
  const digits = String(event.target.value).replace(/\D/g, '').slice(0, 10)
  form[field] = digits

  if (form.errors[field]) {
    form.clearErrors(field)
  }
}

function validateFile(file) {
  if (!file) {
    return 'No file selected.'
  }

  const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png']
  const extension = file.name.split('.').pop()?.toLowerCase()

  if (!allowedExtensions.includes(extension)) {
    return 'Only PDF, JPG, JPEG, and PNG files are allowed.'
  }

  const maxSize = 10 * 1024 * 1024

  if (file.size > maxSize) {
    return 'File size must not exceed 10MB.'
  }

  return null
}

function setFile(field, event) {
  const file = event.target.files?.[0] ?? null

  if (!file) {
    form[field] = null
    return
  }

  const error = validateFile(file)

  if (error) {
    form[field] = null
    event.target.value = ''
    showToast(error, 'error')
    return
  }

  form[field] = file

  if (form.errors[field]) {
    form.clearErrors(field)
  }
}

function removeRequiredDocument(field) {
  form[field] = null

  if (form.errors[field]) {
    form.clearErrors(field)
  }
}

function handleSupportingFiles(event) {
  const files = Array.from(event.target.files || [])
  const validFiles = []

  files.forEach((file) => {
    const error = validateFile(file)

    if (error) {
      showToast(error, 'error')
      return
    }

    validFiles.push(file)
  })

  form.supporting_documents = [
    ...form.supporting_documents,
    ...validFiles,
  ]

  event.target.value = ''
}

function handleSupportingDrop(event) {
  event.preventDefault()

  const files = Array.from(event.dataTransfer?.files || [])
  const validFiles = []

  files.forEach((file) => {
    const error = validateFile(file)

    if (error) {
      showToast(error, 'error')
      return
    }

    validFiles.push(file)
  })

  form.supporting_documents = [
    ...form.supporting_documents,
    ...validFiles,
  ]
}

function preventDrop(event) {
  event.preventDefault()
}

function removeSupportingDocument(index) {
  form.supporting_documents.splice(index, 1)
}

function getFirstError(errors) {
  const firstKey = Object.keys(errors || {})[0]

  if (!firstKey) {
    return 'Failed to submit application. Please review the highlighted errors.'
  }

  const message = errors[firstKey]

  if (Array.isArray(message)) {
    return message[0]
  }

  return message
}

function goToErrorStep(firstKey) {
  if (
    [
      'business_permit',
      'mayors_permit',
      'registration_certificate',
      'bir_certificate',
      'letter_of_intent',
      'supporting_documents',
      'supporting_documents.0',
      'supporting_documents.1',
      'supporting_documents.2',
      'supporting_documents.*',
    ].includes(firstKey)
  ) {
    activeStep.value = 4
    return
  }

  if (firstKey === 'declaration') {
    activeStep.value = 5
    return
  }

  if (
    [
      'company_mobile',
      'company_email_official',
      'alternative_email',
      'previous_participation',
      'years_participated',
      'preferred_beneficiaries',
      'available_spes_slots',
      'preferred_department',
      'employment_period',
      'work_schedules',
      'work_assignments',
      'position_title',
      'number_of_vacancies',
      'minimum_qualification',
      'assigned_department',
      'work_schedule',
      'expected_duration',
    ].includes(firstKey)
  ) {
    activeStep.value = 3
    return
  }

  if (
    [
      'auth_first_name',
      'auth_middle_name',
      'auth_last_name',
      'auth_suffix',
      'auth_position',
      'auth_mobile',
      'auth_email',
      'contact_first_name',
      'contact_middle_name',
      'contact_last_name',
      'contact_suffix',
      'contact_position',
      'contact_mobile',
      'contact_email',
      'finance_first_name',
      'finance_middle_name',
      'finance_last_name',
      'finance_suffix',
      'finance_position',
      'finance_mobile',
      'finance_email',
    ].includes(firstKey)
  ) {
    activeStep.value = 2
    return
  }

  activeStep.value = 1
}

function normalizeUrl(value) {
  const url = String(value || '').trim()

  if (!url) {
    return ''
  }

  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }

  if (url.includes('.') && !url.includes(' ')) {
    return `https://${url}`
  }

  return ''
}

function submitOnboarding() {
  form.clearErrors()

  if (!hasRequiredDocuments.value) {
    activeStep.value = 4
    showToast('Please upload all required documents before submitting.', 'error')
    return
  }

  if (!form.declaration) {
    activeStep.value = 5
    showToast('Please confirm the declaration before submitting.', 'error')
    return
  }

  showToast('Submitting employer onboarding application...', 'info')

  form
    .transform((data) => ({
      company_name: data.company_name || '',
      business_trade_name: data.business_trade_name || '',
      nature_of_business: data.nature_of_business || '',
      industry_type: data.industry_type || '',
      sector: data.sector || '',
      company_website: normalizeUrl(data.company_website),
      facebook_page: normalizeUrl(data.facebook_page),
      number_of_employees: data.number_of_employees || '',
      house_number: data.house_number || '',
      street: data.street || '',
      barangay: data.barangay || '',
      city: 'San Fernando',
      province: 'Pampanga',
      zip_code: data.zip_code || '',

      auth_first_name: data.auth_first_name || '',
      auth_middle_name: data.auth_middle_name || '',
      auth_last_name: data.auth_last_name || '',
      auth_suffix: data.auth_suffix || '',
      auth_position: data.auth_position || '',
      auth_mobile: data.auth_mobile || '',
      auth_email: data.auth_email || '',

      contact_first_name: data.contact_first_name || '',
      contact_middle_name: data.contact_middle_name || '',
      contact_last_name: data.contact_last_name || '',
      contact_suffix: data.contact_suffix || '',
      contact_position: data.contact_position || '',
      contact_mobile: data.contact_mobile || '',
      contact_email: data.contact_email || '',

      finance_first_name: data.finance_first_name || '',
      finance_middle_name: data.finance_middle_name || '',
      finance_last_name: data.finance_last_name || '',
      finance_suffix: data.finance_suffix || '',
      finance_position: data.finance_position || '',
      finance_mobile: data.finance_mobile || '',
      finance_email: data.finance_email || '',

      company_phone: data.company_phone || '',
      company_mobile: data.company_mobile || '',
      company_email_official: data.company_email_official || '',
      alternative_email: data.alternative_email || '',

      previous_participation: data.previous_participation || '',
      years_participated: data.years_participated || '',
      preferred_beneficiaries: data.preferred_beneficiaries || '',
      available_spes_slots: data.preferred_beneficiaries || '',
      preferred_department: data.preferred_department || '',
      employment_period: data.employment_period || '',
      work_schedules: data.work_schedules || '',
      work_assignments: data.work_assignments || '',

      position_title: data.position_title || '',
      number_of_vacancies: data.number_of_vacancies || '',
      minimum_qualification: data.minimum_qualification || '',
      assigned_department: data.assigned_department || '',
      work_schedule: data.work_schedule || '',
      expected_duration: data.expected_duration || '',

      business_permit: data.business_permit,
      mayors_permit: data.mayors_permit,
      registration_certificate: data.registration_certificate,
      bir_certificate: data.bir_certificate,
      letter_of_intent: data.letter_of_intent,
      supporting_documents: data.supporting_documents || [],

      declaration: data.declaration ? 1 : 0,
    }))
    .post(route('onboarding.submit'), {
      forceFormData: true,
      preserveScroll: true,

      onBefore: () => {
        return hasRequiredDocuments.value && form.declaration
      },

      onSuccess: () => {
        showToast('Employer onboarding submitted successfully. Redirecting to dashboard...', 'success')

        setTimeout(() => {
          window.location.href = route('dashboard')
        }, 1500)
      },

      onError: (errors) => {
        console.error('EMPLOYER ONBOARDING ERRORS:', errors)
        console.table(errors)

        const firstKey = Object.keys(errors || {})[0]
        const firstError = getFirstError(errors)

        showToast(firstError, 'error')

        if (firstKey) {
          goToErrorStep(firstKey)
        }

        window.scrollTo({
          top: 0,
          behavior: 'smooth',
        })
      },
    })
}
</script>

<template>
  <div class="min-h-screen bg-slate-100 py-8">
    <div
      v-if="toast.show"
      :class="`fixed top-6 right-6 z-50 rounded-3xl px-5 py-3 text-sm font-semibold text-white shadow-xl ${toast.type === 'error' ? 'bg-rose-600' : toast.type === 'info' ? 'bg-sky-600' : 'bg-emerald-600'}`"
    >
      {{ toast.message }}
    </div>

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="overflow-hidden rounded-[32px] bg-white shadow-xl">
        <div class="border-b border-slate-200 bg-slate-900 px-6 py-6 text-white sm:px-8">
          <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <p class="text-xs uppercase tracking-[0.3em] text-sky-300">
                Employer Onboarding
              </p>

              <h1 class="mt-2 text-3xl font-semibold">
                Complete your employer registration
              </h1>

              <p class="mt-2 max-w-3xl text-sm text-slate-200">
                Finish the onboarding steps so your employer account can be reviewed and approved by admin.
              </p>
            </div>

            <div class="rounded-3xl border border-white/10 bg-white/10 px-5 py-4 backdrop-blur-sm">
              <p class="text-xs uppercase tracking-[0.2em] text-sky-100">
                Progress
              </p>

              <div class="mt-3 flex items-center gap-3">
                <div class="h-2 w-40 overflow-hidden rounded-full bg-white/20">
                  <div
                    class="h-full rounded-full bg-emerald-400"
                    :style="{ width: `${stepProgress}%` }"
                  ></div>
                </div>

                <span class="text-sm font-semibold text-white">
                  {{ stepProgress }}%
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[280px,1fr] lg:px-8">
          <aside class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
            <p class="text-sm font-semibold text-slate-900">
              Onboarding steps
            </p>

            <div class="mt-4 space-y-3">
              <button
                v-for="step in steps"
                :key="step.number"
                type="button"
                class="w-full rounded-3xl border px-4 py-4 text-left transition"
                :class="{
                  'border-indigo-500 bg-indigo-50 text-slate-900': activeStep === step.number,
                  'border-slate-200 bg-white text-slate-700': activeStep !== step.number
                }"
                @click="setActiveStep(step.number)"
              >
                <div class="flex items-center gap-4">
                  <div
                    class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold"
                    :class="activeStep === step.number ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600'"
                  >
                    <span v-if="step.number < activeStep">✓</span>
                    <span v-else>{{ step.number }}</span>
                  </div>

                  <div>
                    <p class="font-semibold">
                      {{ step.title }}
                    </p>
                    <p class="text-xs text-slate-500">
                      {{ step.description }}
                    </p>
                  </div>
                </div>
              </button>
            </div>
          </aside>

          <section class="space-y-6">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
              <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                  <p class="text-sm text-slate-500">
                    Step {{ activeStep }} of {{ steps.length }}
                  </p>

                  <h2 class="mt-1 text-2xl font-semibold text-slate-900">
                    {{ steps[activeStep - 1].title }}
                  </h2>

                  <p class="mt-2 text-sm text-slate-600">
                    {{ steps[activeStep - 1].description }}
                  </p>
                </div>

                <div class="rounded-full bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-700">
                  {{ stepProgress }}% completed
                </div>
              </div>

              <div class="mt-6 space-y-6">
                <div v-if="activeStep === 1" class="grid gap-6">
                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Company Information
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">Company Name *</label>
                        <input v-model="form.company_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.company_name" class="mt-2 text-xs text-rose-600">{{ form.errors.company_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Business Trade Name</label>
                        <input v-model="form.business_trade_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.business_trade_name" class="mt-2 text-xs text-rose-600">{{ form.errors.business_trade_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Nature of Business *</label>
                        <input v-model="form.nature_of_business" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.nature_of_business" class="mt-2 text-xs text-rose-600">{{ form.errors.nature_of_business }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Industry Type *</label>
                        <input v-model="form.industry_type" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.industry_type" class="mt-2 text-xs text-rose-600">{{ form.errors.industry_type }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Sector *</label>
                        <input v-model="form.sector" type="text" placeholder="Private, Government, NGO, Cooperative" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.sector" class="mt-2 text-xs text-rose-600">{{ form.errors.sector }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Company Website</label>
                        <input v-model="form.company_website" @blur="form.company_website = normalizeUrl(form.company_website)" type="url" placeholder="https://" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.company_website" class="mt-2 text-xs text-rose-600">{{ form.errors.company_website }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Facebook Page</label>
                        <input v-model="form.facebook_page" @blur="form.facebook_page = normalizeUrl(form.facebook_page)" type="url" placeholder="https://facebook.com/page" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.facebook_page" class="mt-2 text-xs text-rose-600">{{ form.errors.facebook_page }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Number of Employees</label>
                        <input v-model="form.number_of_employees" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.number_of_employees" class="mt-2 text-xs text-rose-600">{{ form.errors.number_of_employees }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Business Address
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">House No. / Building No. *</label>
                        <input v-model="form.house_number" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.house_number" class="mt-2 text-xs text-rose-600">{{ form.errors.house_number }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Street / Sitio / Purok *</label>
                        <input v-model="form.street" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.street" class="mt-2 text-xs text-rose-600">{{ form.errors.street }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Barangay *</label>
                        <select v-model="form.barangay" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500">
                          <option value="" disabled>Select barangay</option>
                          <option v-for="brgy in sanFernandoBarangays" :key="brgy" :value="brgy">{{ brgy }}</option>
                        </select>
                        <p v-if="form.errors.barangay" class="mt-2 text-xs text-rose-600">{{ form.errors.barangay }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">City / Municipality *</label>
                        <input v-model="form.city" type="text" readonly class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-600 outline-none cursor-not-allowed" />
                        <p v-if="form.errors.city" class="mt-2 text-xs text-rose-600">{{ form.errors.city }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Province *</label>
                        <input v-model="form.province" type="text" readonly class="mt-2 w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-slate-600 outline-none cursor-not-allowed" />
                        <p v-if="form.errors.province" class="mt-2 text-xs text-rose-600">{{ form.errors.province }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">ZIP Code</label>
                        <input v-model="form.zip_code" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.zip_code" class="mt-2 text-xs text-rose-600">{{ form.errors.zip_code }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="activeStep === 2" class="grid gap-6">
                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Authorized Representative
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">First Name *</label>
                        <input v-model="form.auth_first_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.auth_first_name" class="mt-2 text-xs text-rose-600">{{ form.errors.auth_first_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Middle Name</label>
                        <input v-model="form.auth_middle_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name *</label>
                        <input v-model="form.auth_last_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.auth_last_name" class="mt-2 text-xs text-rose-600">{{ form.errors.auth_last_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Suffix</label>
                        <input v-model="form.auth_suffix" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Position / Designation *</label>
                        <input v-model="form.auth_position" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.auth_position" class="mt-2 text-xs text-rose-600">{{ form.errors.auth_position }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile Number *</label>
                        <div class="mt-2 flex rounded-2xl border border-slate-300 bg-white px-3 py-2 focus-within:border-indigo-500">
                          <span class="inline-flex items-center text-sm text-slate-500">(+63)</span>
                          <input v-model="form.auth_mobile" @input="formatPhoneInput('auth_mobile', $event)" type="tel" inputmode="numeric" maxlength="10" placeholder="10 digits" class="ml-3 w-full border-none bg-transparent text-sm text-slate-700 outline-none" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Enter the 10-digit mobile number after +63.</p>
                        <p v-if="form.errors.auth_mobile" class="mt-2 text-xs text-rose-600">{{ form.errors.auth_mobile }}</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Email Address *</label>
                        <input v-model="form.auth_email" type="email" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.auth_email" class="mt-2 text-xs text-rose-600">{{ form.errors.auth_email }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Contact Person
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">First Name *</label>
                        <input v-model="form.contact_first_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.contact_first_name" class="mt-2 text-xs text-rose-600">{{ form.errors.contact_first_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Middle Name</label>
                        <input v-model="form.contact_middle_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name *</label>
                        <input v-model="form.contact_last_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.contact_last_name" class="mt-2 text-xs text-rose-600">{{ form.errors.contact_last_name }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Suffix</label>
                        <input v-model="form.contact_suffix" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Position / Designation *</label>
                        <input v-model="form.contact_position" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.contact_position" class="mt-2 text-xs text-rose-600">{{ form.errors.contact_position }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile Number *</label>
                        <div class="mt-2 flex rounded-2xl border border-slate-300 bg-white px-3 py-2 focus-within:border-indigo-500">
                          <span class="inline-flex items-center text-sm text-slate-500">(+63)</span>
                          <input v-model="form.contact_mobile" @input="formatPhoneInput('contact_mobile', $event)" type="tel" inputmode="numeric" maxlength="10" placeholder="10 digits" class="ml-3 w-full border-none bg-transparent text-sm text-slate-700 outline-none" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Enter the 10-digit mobile number after +63.</p>
                        <p v-if="form.errors.contact_mobile" class="mt-2 text-xs text-rose-600">{{ form.errors.contact_mobile }}</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Email Address *</label>
                        <input v-model="form.contact_email" type="email" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.contact_email" class="mt-2 text-xs text-rose-600">{{ form.errors.contact_email }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Budget / Finance Officer
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">First Name</label>
                        <input v-model="form.finance_first_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Middle Name</label>
                        <input v-model="form.finance_middle_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Last Name</label>
                        <input v-model="form.finance_last_name" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Suffix</label>
                        <input v-model="form.finance_suffix" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Position / Designation</label>
                        <input v-model="form.finance_position" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile Number</label>
                        <div class="mt-2 flex rounded-2xl border border-slate-300 bg-white px-3 py-2 focus-within:border-indigo-500">
                          <span class="inline-flex items-center text-sm text-slate-500">(+63)</span>
                          <input v-model="form.finance_mobile" @input="formatPhoneInput('finance_mobile', $event)" type="tel" inputmode="numeric" maxlength="10" placeholder="10 digits" class="ml-3 w-full border-none bg-transparent text-sm text-slate-700 outline-none" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Enter the 10-digit mobile number after +63.</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Email Address</label>
                        <input v-model="form.finance_email" type="email" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="activeStep === 3" class="grid gap-6">
                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Company Contact Information
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">Telephone Number</label>
                        <input v-model="form.company_phone" type="tel" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.company_phone" class="mt-2 text-xs text-rose-600">{{ form.errors.company_phone }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Mobile Number *</label>
                        <div class="mt-2 flex rounded-2xl border border-slate-300 bg-white px-3 py-2 focus-within:border-indigo-500">
                          <span class="inline-flex items-center text-sm text-slate-500">(+63)</span>
                          <input v-model="form.company_mobile" @input="formatPhoneInput('company_mobile', $event)" type="tel" inputmode="numeric" maxlength="10" placeholder="10 digits" class="ml-3 w-full border-none bg-transparent text-sm text-slate-700 outline-none" />
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Enter the 10-digit mobile number after +63.</p>
                        <p v-if="form.errors.company_mobile" class="mt-2 text-xs text-rose-600">{{ form.errors.company_mobile }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Official Company Email *</label>
                        <input v-model="form.company_email_official" type="email" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.company_email_official" class="mt-2 text-xs text-rose-600">{{ form.errors.company_email_official }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Alternative Email</label>
                        <input v-model="form.alternative_email" type="email" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.alternative_email" class="mt-2 text-xs text-rose-600">{{ form.errors.alternative_email }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      SPES Participation Details
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">Has the company previously participated in SPES? *</label>
                        <select v-model="form.previous_participation" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500">
                          <option value="">Select status</option>
                          <option value="Yes">Yes</option>
                          <option value="No">No</option>
                        </select>
                        <p v-if="form.errors.previous_participation" class="mt-2 text-xs text-rose-600">{{ form.errors.previous_participation }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Number of Years Participated</label>
                        <input v-model="form.years_participated" type="number" min="0" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.years_participated" class="mt-2 text-xs text-rose-600">{{ form.errors.years_participated }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Preferred Number of Beneficiaries *</label>
                        <input v-model="form.preferred_beneficiaries" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.preferred_beneficiaries || form.errors.available_spes_slots" class="mt-2 text-xs text-rose-600">{{ form.errors.preferred_beneficiaries || form.errors.available_spes_slots }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Preferred Department / Assignment Area</label>
                        <input v-model="form.preferred_department" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.preferred_department" class="mt-2 text-xs text-rose-600">{{ form.errors.preferred_department }}</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Proposed Employment Period</label>
                        <input v-model="form.employment_period" type="text" placeholder="e.g. June 2026 - August 2026" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.employment_period" class="mt-2 text-xs text-rose-600">{{ form.errors.employment_period }}</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Available Work Schedules</label>
                        <textarea v-model="form.work_schedules" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm outline-none focus:border-indigo-500"></textarea>
                        <p v-if="form.errors.work_schedules" class="mt-2 text-xs text-rose-600">{{ form.errors.work_schedules }}</p>
                      </div>

                      <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-700">Description of Work Assignments</label>
                        <textarea v-model="form.work_assignments" rows="3" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 shadow-sm outline-none focus:border-indigo-500"></textarea>
                        <p v-if="form.errors.work_assignments" class="mt-2 text-xs text-rose-600">{{ form.errors.work_assignments }}</p>
                      </div>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Employment Opportunities for SPES Beneficiaries
                    </h3>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                      <div>
                        <label class="block text-sm font-medium text-slate-700">Position / Task Title *</label>
                        <input v-model="form.position_title" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.position_title" class="mt-2 text-xs text-rose-600">{{ form.errors.position_title }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Number of Vacancies *</label>
                        <input v-model="form.number_of_vacancies" type="number" min="1" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.number_of_vacancies" class="mt-2 text-xs text-rose-600">{{ form.errors.number_of_vacancies }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Minimum Qualification</label>
                        <input v-model="form.minimum_qualification" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.minimum_qualification" class="mt-2 text-xs text-rose-600">{{ form.errors.minimum_qualification }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Assigned Department</label>
                        <input v-model="form.assigned_department" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.assigned_department" class="mt-2 text-xs text-rose-600">{{ form.errors.assigned_department }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Work Schedule</label>
                        <input v-model="form.work_schedule" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.work_schedule" class="mt-2 text-xs text-rose-600">{{ form.errors.work_schedule }}</p>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Expected Duration of Assignment</label>
                        <input v-model="form.expected_duration" type="text" class="mt-2 w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 outline-none focus:border-indigo-500" />
                        <p v-if="form.errors.expected_duration" class="mt-2 text-xs text-rose-600">{{ form.errors.expected_duration }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="activeStep === 4" class="grid gap-6">
                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Documentary Requirements
                    </h3>

                    <p class="mt-3 text-sm text-slate-600">
                      Upload each required employer onboarding document separately.
                    </p>

                    <div class="mt-5 grid gap-5">
                      <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                          <label class="block text-sm font-medium text-slate-700">Business Permit *</label>
                          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setFile('business_permit', $event)" class="mt-2 w-full text-sm text-slate-600" />
                          <div v-if="form.business_permit" class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="text-sm text-slate-700">{{ form.business_permit.name }}</span>
                            <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeRequiredDocument('business_permit')">Remove</button>
                          </div>
                          <p v-if="form.errors.business_permit" class="mt-2 text-xs text-rose-600">{{ form.errors.business_permit }}</p>
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-slate-700">Mayor's Permit *</label>
                          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setFile('mayors_permit', $event)" class="mt-2 w-full text-sm text-slate-600" />
                          <div v-if="form.mayors_permit" class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="text-sm text-slate-700">{{ form.mayors_permit.name }}</span>
                            <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeRequiredDocument('mayors_permit')">Remove</button>
                          </div>
                          <p v-if="form.errors.mayors_permit" class="mt-2 text-xs text-rose-600">{{ form.errors.mayors_permit }}</p>
                        </div>
                      </div>

                      <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                          <label class="block text-sm font-medium text-slate-700">DTI / SEC / CDA Registration *</label>
                          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setFile('registration_certificate', $event)" class="mt-2 w-full text-sm text-slate-600" />
                          <div v-if="form.registration_certificate" class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="text-sm text-slate-700">{{ form.registration_certificate.name }}</span>
                            <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeRequiredDocument('registration_certificate')">Remove</button>
                          </div>
                          <p v-if="form.errors.registration_certificate" class="mt-2 text-xs text-rose-600">{{ form.errors.registration_certificate }}</p>
                        </div>

                        <div>
                          <label class="block text-sm font-medium text-slate-700">BIR Certificate of Registration *</label>
                          <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setFile('bir_certificate', $event)" class="mt-2 w-full text-sm text-slate-600" />
                          <div v-if="form.bir_certificate" class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                            <span class="text-sm text-slate-700">{{ form.bir_certificate.name }}</span>
                            <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeRequiredDocument('bir_certificate')">Remove</button>
                          </div>
                          <p v-if="form.errors.bir_certificate" class="mt-2 text-xs text-rose-600">{{ form.errors.bir_certificate }}</p>
                        </div>
                      </div>

                      <div>
                        <label class="block text-sm font-medium text-slate-700">Letter of Intent to Participate in SPES *</label>
                        <input type="file" accept=".pdf,.jpg,.jpeg,.png" @change="setFile('letter_of_intent', $event)" class="mt-2 w-full text-sm text-slate-600" />
                        <div v-if="form.letter_of_intent" class="mt-2 flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                          <span class="text-sm text-slate-700">{{ form.letter_of_intent.name }}</span>
                          <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeRequiredDocument('letter_of_intent')">Remove</button>
                        </div>
                        <p v-if="form.errors.letter_of_intent" class="mt-2 text-xs text-rose-600">{{ form.errors.letter_of_intent }}</p>
                      </div>
                    </div>

                    <div v-if="activeStep === 4 && !hasRequiredDocuments" class="rounded-3xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                      Please upload all required documents before submitting your application.
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Optional Supporting Documents
                    </h3>

                    <p class="mt-3 text-sm text-slate-600">
                      Add extra documents such as company profile, organizational chart, or other supporting files.
                    </p>

                    <div class="mt-5 rounded-3xl border-2 border-dashed border-slate-300 bg-white p-8 text-center" @dragover.prevent="preventDrop" @drop.prevent="handleSupportingDrop">
                      <p class="text-sm font-semibold text-slate-900">
                        Drag and drop supporting files here
                      </p>

                      <p class="mt-2 text-sm text-slate-500">
                        or
                      </p>

                      <label class="mt-4 inline-flex cursor-pointer items-center rounded-3xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Choose files
                        <input type="file" multiple accept=".pdf,.jpg,.jpeg,.png" @change="handleSupportingFiles" class="sr-only" />
                      </label>
                    </div>

                    <p v-if="form.errors.supporting_documents" class="mt-4 text-xs text-rose-600">{{ form.errors.supporting_documents }}</p>
                    <p v-if="form.errors['supporting_documents.*']" class="mt-2 text-xs text-rose-600">{{ form.errors['supporting_documents.*'] }}</p>

                    <div v-if="form.supporting_documents.length" class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-4">
                      <p class="text-sm font-semibold text-slate-900">
                        Supporting Files
                      </p>

                      <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        <li v-for="(file, index) in form.supporting_documents" :key="index" class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3">
                          <span>{{ file.name }}</span>
                          <button type="button" class="text-indigo-600 hover:text-indigo-800" @click="removeSupportingDocument(index)">Remove</button>
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>

                <div v-if="activeStep === 5" class="space-y-6">
                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Review & Submit
                    </h3>

                    <p class="mt-2 text-sm text-slate-600">
                      Review all details below before submitting your SPES employer onboarding application.
                    </p>

                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">Company Information</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>Name:</strong> {{ form.company_name }}</p>
                        <p class="text-sm text-slate-700"><strong>Trade Name:</strong> {{ form.business_trade_name || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Nature of Business:</strong> {{ form.nature_of_business }}</p>
                        <p class="text-sm text-slate-700"><strong>Industry Type:</strong> {{ form.industry_type }}</p>
                        <p class="text-sm text-slate-700"><strong>Sector:</strong> {{ form.sector }}</p>
                        <p class="text-sm text-slate-700"><strong>Website:</strong> {{ form.company_website || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Facebook:</strong> {{ form.facebook_page || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Employees:</strong> {{ form.number_of_employees || '—' }}</p>
                      </div>

                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">Business Address</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>House / Building:</strong> {{ form.house_number }}</p>
                        <p class="text-sm text-slate-700"><strong>Street:</strong> {{ form.street }}</p>
                        <p class="text-sm text-slate-700"><strong>Barangay:</strong> {{ form.barangay }}</p>
                        <p class="text-sm text-slate-700"><strong>City / Municipality:</strong> {{ form.city }}</p>
                        <p class="text-sm text-slate-700"><strong>Province:</strong> {{ form.province }}</p>
                        <p class="text-sm text-slate-700"><strong>ZIP Code:</strong> {{ form.zip_code || '—' }}</p>
                      </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">Authorized Representative</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>{{ form.auth_first_name }} {{ form.auth_middle_name }} {{ form.auth_last_name }} {{ form.auth_suffix }}</strong></p>
                        <p class="text-sm text-slate-700"><strong>Position:</strong> {{ form.auth_position }}</p>
                        <p class="text-sm text-slate-700"><strong>Mobile:</strong> {{ form.auth_mobile }}</p>
                        <p class="text-sm text-slate-700"><strong>Email:</strong> {{ form.auth_email }}</p>
                      </div>

                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">Contact Person</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>{{ form.contact_first_name }} {{ form.contact_middle_name }} {{ form.contact_last_name }} {{ form.contact_suffix }}</strong></p>
                        <p class="text-sm text-slate-700"><strong>Position:</strong> {{ form.contact_position }}</p>
                        <p class="text-sm text-slate-700"><strong>Mobile:</strong> {{ form.contact_mobile }}</p>
                        <p class="text-sm text-slate-700"><strong>Email:</strong> {{ form.contact_email }}</p>
                      </div>
                    </div>

                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                      <p class="text-xs uppercase text-slate-500">Budget / Finance Officer</p>
                      <p class="mt-2 text-sm text-slate-700"><strong>{{ form.finance_first_name }} {{ form.finance_middle_name }} {{ form.finance_last_name }} {{ form.finance_suffix }}</strong></p>
                      <p class="text-sm text-slate-700"><strong>Position:</strong> {{ form.finance_position || '—' }}</p>
                      <p class="text-sm text-slate-700"><strong>Mobile:</strong> {{ form.finance_mobile || '—' }}</p>
                      <p class="text-sm text-slate-700"><strong>Email:</strong> {{ form.finance_email || '—' }}</p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">Company Contact</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>Telephone:</strong> {{ form.company_phone || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Mobile:</strong> {{ form.company_mobile }}</p>
                        <p class="text-sm text-slate-700"><strong>Official Email:</strong> {{ form.company_email_official }}</p>
                        <p class="text-sm text-slate-700"><strong>Alternative Email:</strong> {{ form.alternative_email || '—' }}</p>
                      </div>

                      <div class="rounded-3xl bg-white p-4 shadow-sm">
                        <p class="text-xs uppercase text-slate-500">SPES Participation</p>
                        <p class="mt-2 text-sm text-slate-700"><strong>Previously participated:</strong> {{ form.previous_participation }}</p>
                        <p class="text-sm text-slate-700"><strong>Years:</strong> {{ form.years_participated || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Preferred beneficiaries:</strong> {{ form.preferred_beneficiaries }}</p>
                        <p class="text-sm text-slate-700"><strong>Department / Assignment:</strong> {{ form.preferred_department || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Employment period:</strong> {{ form.employment_period || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Work schedules:</strong> {{ form.work_schedules || '—' }}</p>
                        <p class="text-sm text-slate-700"><strong>Work assignments:</strong> {{ form.work_assignments || '—' }}</p>
                      </div>
                    </div>

                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                      <p class="text-xs uppercase text-slate-500">Employment Opportunities</p>
                      <p class="mt-2 text-sm text-slate-700"><strong>Title:</strong> {{ form.position_title }}</p>
                      <p class="text-sm text-slate-700"><strong>Vacancies:</strong> {{ form.number_of_vacancies }}</p>
                      <p class="text-sm text-slate-700"><strong>Minimum qualification:</strong> {{ form.minimum_qualification || '—' }}</p>
                      <p class="text-sm text-slate-700"><strong>Assigned department:</strong> {{ form.assigned_department || '—' }}</p>
                      <p class="text-sm text-slate-700"><strong>Schedule:</strong> {{ form.work_schedule || '—' }}</p>
                      <p class="text-sm text-slate-700"><strong>Duration:</strong> {{ form.expected_duration || '—' }}</p>
                    </div>

                    <div class="rounded-3xl bg-white p-4 shadow-sm">
                      <p class="text-xs uppercase text-slate-500">Uploaded Documents</p>

                      <ul class="mt-3 space-y-2 text-sm text-slate-700">
                        <li v-if="form.business_permit" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>Business Permit:</strong> {{ form.business_permit.name }}
                        </li>

                        <li v-if="form.mayors_permit" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>Mayor's Permit:</strong> {{ form.mayors_permit.name }}
                        </li>

                        <li v-if="form.registration_certificate" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>Registration Certificate:</strong> {{ form.registration_certificate.name }}
                        </li>

                        <li v-if="form.bir_certificate" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>BIR Certificate:</strong> {{ form.bir_certificate.name }}
                        </li>

                        <li v-if="form.letter_of_intent" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>Letter of Intent:</strong> {{ form.letter_of_intent.name }}
                        </li>

                        <li v-for="(file, index) in form.supporting_documents" :key="index" class="rounded-2xl bg-slate-50 px-4 py-3">
                          <strong>Supporting Document:</strong> {{ file.name }}
                        </li>
                      </ul>
                    </div>
                  </div>

                  <div class="rounded-3xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-lg font-semibold text-slate-900">
                      Declaration and Certification
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-slate-700">
                      I hereby certify that all information provided in this registration form is true, complete, and accurate to the best of my knowledge. I understand that submission of false or misleading information may result in the denial, suspension, or cancellation of our participation in the Special Program for Employment of Students (SPES).
                    </p>

                    <p class="mt-3 text-sm leading-6 text-slate-700">
                      Furthermore, the company agrees to comply with all applicable SPES guidelines, policies, and requirements established by the Public Employment Service Office (PESO) and the City Government of San Fernando, Pampanga.
                    </p>

                    <label class="mt-5 flex items-start gap-3 text-sm text-slate-700">
                      <input type="checkbox" v-model="form.declaration" class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                      <span>I hereby certify that the information above is true, complete, and accurate.</span>
                    </label>

                    <p v-if="form.errors.declaration" class="mt-2 text-xs text-rose-600">{{ form.errors.declaration }}</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-between">
              <button
                type="button"
                @click="previousStep"
                class="inline-flex items-center justify-center rounded-3xl border border-slate-300 bg-white px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100"
                :disabled="activeStep === 1"
              >
                Back
              </button>

              <div class="flex flex-col gap-3 sm:flex-row">
                <button
                  v-if="activeStep < steps.length"
                  type="button"
                  @click="nextStep"
                  :disabled="!canAdvance"
                  class="inline-flex items-center justify-center rounded-3xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  Next
                </button>

                <button
                  v-else
                  type="button"
                  @click="submitOnboarding"
                  :disabled="form.processing || !form.declaration || !hasRequiredDocuments"
                  class="inline-flex items-center justify-center rounded-3xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {{ form.processing ? 'Submitting...' : 'Submit Application' }}
                </button>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
button:disabled {
  cursor: not-allowed;
}
</style>