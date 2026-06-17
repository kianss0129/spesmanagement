<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  roles: {
    type: Array,
    default: () => [],
  },
})

const page = usePage()
const success = computed(() => page.props.flash?.success)
const error = computed(() => page.props.flash?.error)

const toast = ref({
  show: false,
  message: '',
  type: 'success',
})

const form = useForm({
  user_id: '',
  role: '',
})

const pesoForm = useForm({
  name: '',
  email: '',
  password: '',
  role: 'PESO Official',
})

const search = ref('')
const userPage = ref(1)
const usersPerPage = 10
const showModal = ref(false)
const modalType = ref(null)
const selectedUserId = ref(null)

const filteredUsers = computed(() => {
  if (!search.value) return props.users

  const query = search.value.toLowerCase()
  return props.users.filter((user) =>
    String(user.name || '').toLowerCase().includes(query) ||
    String(user.email || '').toLowerCase().includes(query) ||
    roleLabel(user).toLowerCase().includes(query) ||
    statusLabel(user).toLowerCase().includes(query)
  )
})
const userTotalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / usersPerPage)))
const paginatedUsers = computed(() => {
  const start = (userPage.value - 1) * usersPerPage
  return filteredUsers.value.slice(start, start + usersPerPage)
})

watch(search, () => {
  userPage.value = 1
})

watch(filteredUsers, () => {
  if (userPage.value > userTotalPages.value) {
    userPage.value = userTotalPages.value
  }
})

const summaryCards = computed(() => [
  { label: 'Admins', value: countByRole(['admin', 'super admin']) },
  { label: 'CPESO Staff', value: countByRole(['peso', 'cpeso']) },
  { label: 'Employers', value: countByRole(['employer']) },
  { label: 'Beneficiaries', value: countByRole(['beneficiary']) },
])

function roleLabel(user) {
  if (Array.isArray(user?.roles) && user.roles.length) {
    return user.roles.map((role) => (typeof role === 'string' ? role : role?.name)).filter(Boolean).join(', ')
  }

  return user?.role || 'No Role'
}

function countByRole(keywords) {
  return props.users.filter((user) => {
    const role = roleLabel(user).toLowerCase()
    return keywords.some((keyword) => role.includes(keyword))
  }).length
}

function statusLabel(user) {
  return user?.status || (user?.deleted_at ? 'Disabled' : 'Active')
}

function statusClass(user) {
  const status = statusLabel(user).toLowerCase()
  if (status.includes('disabled') || status.includes('inactive')) return 'bg-red-100 text-red-800'
  if (status.includes('pending')) return 'bg-amber-100 text-amber-800'
  return 'bg-green-100 text-green-800'
}

function showToast(message, type = 'success') {
  toast.value.message = message
  toast.value.type = type
  toast.value.show = true

  setTimeout(() => {
    toast.value.show = false
  }, 3000)
}

function submit() {
  form.post(route('admin.roles.assign'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
      showToast('Role updated successfully')
    },
    onError: () => {
      showToast('Failed to update role', 'error')
    },
  })
}

function createPesoAccount() {
  pesoForm.post(route('peso.users.create-peso'), {
    preserveScroll: true,
    onSuccess: (pageResult) => {
      pesoForm.reset()
      showToast(pageResult.props.flash?.success || 'CPESO staff account created')
    },
    onError: () => {
      showToast('Failed to create account', 'error')
    },
  })
}

function editRole(user) {
  form.user_id = user.id
  form.role = user.role || ''
}

function openModal(type, id) {
  modalType.value = type
  selectedUserId.value = id
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  selectedUserId.value = null
}

function confirmAction() {
  if (modalType.value === 'remove') {
    router.delete(route('admin.roles.remove', selectedUserId.value), {
      preserveScroll: true,
      onSuccess: () => showToast('Role removed'),
    })
  }

  if (modalType.value === 'disable') {
    router.delete(route('admin.users.destroy', selectedUserId.value), {
      preserveScroll: true,
      onSuccess: () => showToast('User disabled'),
    })
  }

  closeModal()
}

function handleKey(event) {
  if (event.key === 'Escape') closeModal()
}

onMounted(() => {
  window.addEventListener('keydown', handleKey)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', handleKey)
})
</script>

<template>
  <section class="space-y-6">
    <header class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
      <p class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-600">CPESO Access Control</p>
      <h1 class="mt-2 text-2xl font-bold text-slate-900 sm:text-3xl">Users & Roles</h1>
      <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-600">
        Manage system users, staff accounts, and role assignments.
      </p>
    </header>

    <transition name="fade">
      <div v-if="toast.show" class="fixed right-5 top-5 z-50">
        <div
          class="rounded-lg px-5 py-3 text-sm font-semibold text-white shadow-lg"
          :class="toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'"
        >
          {{ toast.message }}
        </div>
      </div>
    </transition>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="card in summaryCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
        <p class="text-sm font-semibold text-slate-600">{{ card.label }}</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">{{ card.value }}</p>
      </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Create CPESO Staff Account</h2>
        <p class="mt-1 text-sm text-slate-500">Add a new staff account for system operations.</p>

        <form class="mt-5 grid gap-4" @submit.prevent="createPesoAccount">
          <input v-model="pesoForm.name" type="text" placeholder="Full name" class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
          <input v-model="pesoForm.email" type="email" placeholder="Email address" class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
          <input v-model="pesoForm.password" type="password" placeholder="Password" class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
          <button type="submit" class="rounded-lg bg-blue-600 px-5 py-3 text-sm font-semibold text-white hover:bg-blue-700">
            Create Account
          </button>
        </form>
      </div>

      <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-lg font-bold text-slate-900">Edit User Role</h2>
        <p class="mt-1 text-sm text-slate-500">Choose a user and assign the correct access level.</p>

        <form class="mt-5 grid gap-4" @submit.prevent="submit">
          <select v-model="form.user_id" class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
            <option value="">Select user</option>
            <option v-for="user in users" :key="user.id" :value="user.id">
              {{ user.name }} - {{ user.email }}
            </option>
          </select>

          <select v-model="form.role" class="rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none">
            <option value="">Select role</option>
            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
          </select>

          <button type="submit" class="rounded-lg bg-slate-900 px-5 py-3 text-sm font-semibold text-white hover:bg-slate-800">
            Save Role
          </button>
        </form>
      </div>
    </section>

    <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
      <div class="flex flex-col gap-4 border-b border-slate-200 p-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-lg font-bold text-slate-900">User Table</h2>
          <p class="mt-1 text-sm text-slate-500">Search and manage user access.</p>
        </div>
        <input
          v-model="search"
          type="search"
          placeholder="Search name, email, role, or status"
          class="w-full rounded-lg border border-slate-300 px-4 py-2 text-sm focus:border-blue-500 focus:outline-none lg:max-w-sm"
        >
      </div>

      <div class="hidden grid-cols-[1.1fr_1.2fr_0.8fr_0.7fr_1fr] gap-4 border-b border-slate-200 bg-slate-50 px-5 py-3 text-xs font-semibold uppercase tracking-[0.12em] text-slate-500 xl:grid">
        <span>Name</span>
        <span>Email</span>
        <span>Role</span>
        <span>Status</span>
        <span>Action</span>
      </div>

      <div
        v-for="user in paginatedUsers"
        :key="user.id"
        class="grid gap-4 border-b border-slate-200 px-5 py-5 last:border-b-0 xl:grid-cols-[1.1fr_1.2fr_0.8fr_0.7fr_1fr] xl:items-center"
      >
        <p class="font-semibold text-slate-900">{{ user.name }}</p>
        <p class="break-words text-sm text-slate-700">{{ user.email }}</p>
        <span class="w-fit rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-800">{{ roleLabel(user) }}</span>
        <span class="w-fit rounded-full px-3 py-1 text-xs font-semibold" :class="statusClass(user)">{{ statusLabel(user) }}</span>
        <div class="flex flex-wrap gap-2">
          <button class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-100" @click="editRole(user)">
            Edit Role
          </button>
          <button
            v-if="roleLabel(user) !== 'No Role'"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50"
            @click="openModal('remove', user.id)"
          >
            Remove Role
          </button>
          <button class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-100" @click="openModal('disable', user.id)">
            Disable
          </button>
        </div>
      </div>

      <div
        v-if="filteredUsers.length > 0"
        class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 text-sm text-slate-600 sm:flex-row sm:items-center sm:justify-between"
      >
        <span>
          Showing {{ ((userPage - 1) * usersPerPage) + 1 }}-{{ Math.min(userPage * usersPerPage, filteredUsers.length) }} of {{ filteredUsers.length }} users
        </span>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="userPage === 1"
            @click="userPage = Math.max(1, userPage - 1)"
          >
            Previous
          </button>
          <button
            type="button"
            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="userPage === userTotalPages"
            @click="userPage = Math.min(userTotalPages, userPage + 1)"
          >
            Next
          </button>
        </div>
      </div>

      <div v-if="filteredUsers.length === 0" class="px-5 py-12 text-center">
        <p class="text-sm font-semibold text-slate-700">No users found.</p>
        <p class="mt-1 text-sm text-slate-500">Try another name, email, role, or status.</p>
      </div>
    </section>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" @click.self="closeModal">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-lg font-bold text-slate-900">{{ modalType === 'disable' ? 'Disable User' : 'Remove Role' }}</h3>
        <p class="mt-3 text-sm text-slate-600">
          {{ modalType === 'disable' ? 'Disable this user account?' : "Remove this user's role?" }}
        </p>
        <div class="mt-6 flex justify-end gap-2">
          <button class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700" @click="closeModal">Cancel</button>
          <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700" @click="confirmAction">
            Confirm
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
