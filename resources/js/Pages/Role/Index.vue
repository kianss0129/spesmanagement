<script setup>
import { useForm, usePage, router } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'


const props = defineProps({
    users: Array,
    roles: Array
})


const page = usePage()


const success = computed(() => page.props.flash?.success)
const error = computed(() => page.props.flash?.error)


/* ===============================
   TOAST NOTIFICATION
================================= */
const toast = ref({
    show: false,
    message: '',
    type: 'success'
})


function showToast(message, type = 'success') {
    toast.value.message = message
    toast.value.type = type
    toast.value.show = true


    setTimeout(() => {
        toast.value.show = false
    }, 3000)
}


/* ===============================
   ASSIGN ROLE FORM
================================= */
const form = useForm({
    user_id: '',
    role: ''
})


function submit() {
    form.post(route('admin.roles.assign'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            showToast('Role assigned successfully', 'success')
        },
        onError: () => {
            showToast('Failed to assign role', 'error')
        }
    })
}


/* ===============================
   PESO CREATE FORM
================================= */
const pesoForm = useForm({
    name: '',
    email: '',
    password: '',
    role: 'PESO Official'
})


function createPesoAccount() {
    pesoForm.post(route('peso.users.create-peso'), {
        preserveScroll: true,
        onSuccess: (page) => {
            pesoForm.reset()
            showToast(page.props.flash?.success || 'PESO user created', 'success')
        },
        onError: () => {
            showToast('Failed to create PESO user', 'error')
        }
    })
}


/* ===============================
   MODAL STATE
================================= */
const showModal = ref(false)
const modalType = ref(null)
const selectedUserId = ref(null)


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
            onSuccess: () => showToast('Role removed', 'success')
        })
    }


    if (modalType.value === 'delete') {
        router.delete(route('admin.users.destroy', selectedUserId.value), {
            preserveScroll: true,
            onSuccess: () => showToast('User deleted', 'success')
        })
    }


    closeModal()
}


/* ESC KEY */
function handleKey(e) {
    if (e.key === 'Escape') closeModal()
}


onMounted(() => window.addEventListener('keydown', handleKey))
onBeforeUnmount(() => window.removeEventListener('keydown', handleKey))
</script>


<template>
<div class="max-w-6xl mx-auto p-6">


    <h1 class="text-3xl font-bold text-blue-700 mb-6">
        Role Management
    </h1>


    <!-- BACK -->
    <button
        @click="$inertia.visit(route('dashboard'))"
        class="text-blue-600 border border-blue-600 px-4 py-2 rounded mb-4 hover:bg-blue-50">
        ← Back
    </button>


    <!-- FLASH -->
    <div v-if="success" class="bg-blue-100 p-3 rounded mb-3 text-blue-700">
        {{ success }}
    </div>


    <div v-if="error" class="bg-red-100 p-3 rounded mb-3 text-red-700">
        {{ error }}
    </div>


    <!-- ===============================
         TOAST NOTIFICATION
    ================================ -->
    <transition name="fade">
        <div v-if="toast.show"
             class="fixed top-5 right-5 z-50">


            <div
                :class="[
                    'px-4 py-3 rounded-lg shadow text-white text-sm',
                    toast.type === 'success' ? 'bg-green-600' : 'bg-red-600'
                ]">


                {{ toast.message }}


            </div>


        </div>
    </transition>


    <!-- ===============================
         CREATE PESO
    ================================ -->
    <div class="mb-8 bg-white p-6 rounded-2xl shadow border border-blue-100">


        <h2 class="text-lg font-semibold text-blue-700 mb-4">
            Create PESO Official Account
        </h2>


        <form @submit.prevent="createPesoAccount" class="flex flex-wrap gap-3">


            <input v-model="pesoForm.name"
                   type="text"
                   placeholder="Name"
                   class="border p-2 rounded-lg w-60" />


            <input v-model="pesoForm.email"
                   type="email"
                   placeholder="Email"
                   class="border p-2 rounded-lg w-60" />


            <input v-model="pesoForm.password"
                   type="password"
                   placeholder="Password"
                   class="border p-2 rounded-lg w-48" />


            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg">
                Create Account
            </button>


        </form>
    </div>


    <!-- ===============================
         ASSIGN ROLE
    ================================ -->
    <div class="mb-8 bg-white p-6 rounded-2xl shadow border border-blue-100">


        <h2 class="text-lg font-semibold text-blue-700 mb-4">
            Assign Role
        </h2>


        <form @submit.prevent="submit" class="flex flex-wrap gap-3">


            <select v-model="form.user_id" class="border p-2 rounded-lg w-60">
                <option value="">Select user</option>
                <option v-for="u in users" :key="u.id" :value="u.id">
                    {{ u.name }} ({{ u.email }})
                </option>
            </select>


            <select v-model="form.role" class="border p-2 rounded-lg w-48">
                <option value="">Select role</option>
                <option v-for="r in roles" :key="r" :value="r">
                    {{ r }}
                </option>
            </select>


            <button class="bg-blue-600 text-white px-5 py-2 rounded-lg">
                Assign Role
            </button>


        </form>
    </div>


    <!-- ===============================
         USERS TABLE
    ================================ -->
    <div class="bg-white rounded-2xl shadow border overflow-hidden">


        <table class="w-full text-sm">
            <thead class="bg-blue-50 text-blue-700">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>


            <tbody>
                <tr v-for="user in users" :key="user.id" class="border-t hover:bg-blue-50">


                    <td class="p-3">{{ user.name }}</td>
                    <td class="p-3">{{ user.email }}</td>


                    <td class="p-3">
                        <span class="bg-blue-100 px-2 py-1 rounded text-xs">
                            {{ user.role || 'No Role' }}
                        </span>
                    </td>


                    <td class="p-3 space-x-2">


                        <button
                            v-if="user.role"
                            @click="openModal('remove', user.id)"
                            class="border border-blue-500 text-blue-600 px-3 py-1 rounded text-xs">
                            Remove Role
                        </button>


                        <button
                            @click="openModal('delete', user.id)"
                            class="bg-red-600 text-white px-3 py-1 rounded text-xs">
                            Delete User
                        </button>


                    </td>
                </tr>
            </tbody>
        </table>
    </div>


    <!-- ===============================
         MODAL
    ================================ -->
    <transition name="fade">
    <div v-if="showModal"
         class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
         @click.self="closeModal">


        <div class="bg-white p-6 rounded-xl w-96">


            <h2 class="text-lg font-semibold mb-3 text-blue-700">
                Confirm Action
            </h2>


            <p class="mb-5 text-gray-600">
                <span v-if="modalType === 'remove'">
                    Remove this user's role?
                </span>
                <span v-else>
                    Delete this user permanently?
                </span>
            </p>


            <div class="flex justify-end gap-2">


                <button @click="closeModal"
                        class="px-4 py-2 border rounded">
                    Cancel
                </button>


                <button @click="confirmAction"
                        :class="modalType === 'delete'
                            ? 'bg-red-600 text-white px-4 py-2 rounded'
                            : 'bg-blue-600 text-white px-4 py-2 rounded'">
                    Confirm
                </button>


            </div>


        </div>
    </div>
    </transition>


</div>
</template>


<style>
.fade-enter-active, .fade-leave-active {
    transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>

