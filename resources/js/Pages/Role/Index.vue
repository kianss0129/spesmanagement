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
<div
    class="min-h-screen bg-cover bg-center bg-no-repeat relative overflow-hidden"
    style="background-image: url('/images/spes-bg.jpg');"
>

    <!-- OVERLAY -->
    <div class="absolute inset-0 bg-black/70"></div>

    <!-- GLOW EFFECT -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute w-80 h-80 bg-blue-500/20 blur-3xl rounded-full -top-20 -left-20 animate-pulse"></div>
        <div class="absolute w-80 h-80 bg-cyan-400/20 blur-3xl rounded-full bottom-0 right-0 animate-pulse"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto p-6">

        <!-- TITLE -->
        <h1 class="text-4xl font-extrabold text-white mb-6">
            Role Management
        </h1>

        <!-- BACK -->
        <button
            @click="$inertia.visit(route('dashboard'))"
            class="mb-6 flex items-center gap-2 text-white/80 hover:text-white transition"
        >
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                />
            </svg>

            <span class="font-medium">
                Back
            </span>
        </button>

        <!-- FLASH -->
        <div
            v-if="success"
            class="bg-green-500/20 border border-green-400/30
                   text-green-100 p-4 rounded-2xl mb-4 backdrop-blur-xl"
        >
            {{ success }}
        </div>

        <div
            v-if="error"
            class="bg-red-500/20 border border-red-400/30
                   text-red-100 p-4 rounded-2xl mb-4 backdrop-blur-xl"
        >
            {{ error }}
        </div>

        <!-- TOAST -->
        <transition name="fade">
            <div
                v-if="toast.show"
                class="fixed top-5 right-5 z-50"
            >
                <div
                    :class="[
                        'px-5 py-3 rounded-2xl shadow-2xl text-white text-sm backdrop-blur-xl border',
                        toast.type === 'success'
                            ? 'bg-green-500/20 border-green-400/30'
                            : 'bg-red-500/20 border-red-400/30'
                    ]"
                >
                    {{ toast.message }}
                </div>
            </div>
        </transition>

        <!-- CREATE PESO -->
        <div
            class="mb-8 bg-white/10 backdrop-blur-xl border border-white/20
                   rounded-3xl shadow-2xl p-6"
        >

            <h2 class="text-xl font-bold text-white mb-5">
                Create PESO Official Account
            </h2>

            <form @submit.prevent="createPesoAccount" class="flex flex-wrap gap-4">

                <input
                    v-model="pesoForm.name"
                    type="text"
                    placeholder="Name"
                    class="bg-white/10 border border-white/20
                           text-white placeholder-gray-300
                           rounded-xl px-4 py-3 w-64 outline-none"
                />

                <input
                    v-model="pesoForm.email"
                    type="email"
                    placeholder="Email"
                    class="bg-white/10 border border-white/20
                           text-white placeholder-gray-300
                           rounded-xl px-4 py-3 w-64 outline-none"
                />

                <input
                    v-model="pesoForm.password"
                    type="password"
                    placeholder="Password"
                    class="bg-white/10 border border-white/20
                           text-white placeholder-gray-300
                           rounded-xl px-4 py-3 w-52 outline-none"
                />

                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-600
                           text-white px-6 py-3 rounded-xl
                           font-semibold transition"
                >
                    Create Account
                </button>

            </form>

        </div>

        <!-- ASSIGN ROLE -->
        <div
            class="mb-8 bg-white/10 backdrop-blur-xl border border-white/20
                   rounded-3xl shadow-2xl p-6"
        >

            <h2 class="text-xl font-bold text-white mb-5">
                Assign Role
            </h2>

            <form @submit.prevent="submit" class="flex flex-wrap gap-4">

                <select
                    v-model="form.user_id"
                    class="bg-white/10 border border-white/20
                           text-white rounded-xl px-4 py-3
                           w-64 outline-none"
                >
                    <option value="" class="text-black">
                        Select user
                    </option>

                    <option
                        v-for="u in users"
                        :key="u.id"
                        :value="u.id"
                        class="text-black"
                    >
                        {{ u.name }} ({{ u.email }})
                    </option>
                </select>

                <select
                    v-model="form.role"
                    class="bg-white/10 border border-white/20
                           text-white rounded-xl px-4 py-3
                           w-52 outline-none"
                >
                    <option value="" class="text-black">
                        Select role
                    </option>

                    <option
                        v-for="r in roles"
                        :key="r"
                        :value="r"
                        class="text-black"
                    >
                        {{ r }}
                    </option>
                </select>

                <button
                    class="bg-blue-500 hover:bg-blue-600
                           text-white px-6 py-3 rounded-xl
                           font-semibold transition"
                >
                    Assign Role
                </button>

            </form>

        </div>

        <!-- USERS TABLE -->
        <div
            class="bg-white/10 backdrop-blur-xl border border-white/20
                   rounded-3xl shadow-2xl overflow-hidden"
        >

            <div class="overflow-x-auto">

                <table class="w-full text-sm text-white">

                    <thead class="bg-white/10">
                        <tr>
                            <th class="p-4 text-left">Name</th>
                            <th class="p-4 text-left">Email</th>
                            <th class="p-4 text-left">Role</th>
                            <th class="p-4 text-left">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="border-t border-white/10 hover:bg-white/5 transition"
                        >

                            <td class="p-4">
                                {{ user.name }}
                            </td>

                            <td class="p-4 text-gray-300">
                                {{ user.email }}
                            </td>

                            <td class="p-4">

                                <span
                                    class="bg-blue-500/20 border border-blue-400/30
                                           text-blue-100 px-3 py-1 rounded-full text-xs"
                                >
                                    {{ user.role || 'No Role' }}
                                </span>

                            </td>

                            <td class="p-4 flex flex-wrap gap-2">

                                <button
                                    v-if="user.role"
                                    @click="openModal('remove', user.id)"
                                    class="border border-blue-400/30
                                           bg-blue-500/10 text-blue-100
                                           px-4 py-2 rounded-xl text-xs
                                           hover:bg-blue-500/20 transition"
                                >
                                    Remove Role
                                </button>

                                <button
                                    @click="openModal('delete', user.id)"
                                    class="bg-red-500 hover:bg-red-600
                                           text-white px-4 py-2 rounded-xl
                                           text-xs transition"
                                >
                                    Delete User
                                </button>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        <!-- MODAL -->
        <transition name="fade">

            <div
                v-if="showModal"
                class="fixed inset-0 bg-black/70 backdrop-blur-sm
                       flex items-center justify-center z-50"
                @click.self="closeModal"
            >

                <div
                    class="bg-white/10 backdrop-blur-2xl border border-white/20
                           rounded-3xl shadow-2xl p-8 w-[90%] max-w-md"
                >

                    <h2 class="text-2xl font-bold text-white mb-4">
                        Confirm Action
                    </h2>

                    <p class="text-gray-300 mb-6">

                        <span v-if="modalType === 'remove'">
                            Remove this user's role?
                        </span>

                        <span v-else>
                            Delete this user permanently?
                        </span>

                    </p>

                    <div class="flex justify-end gap-3">

                        <button
                            @click="closeModal"
                            class="px-5 py-2 rounded-xl
                                   border border-white/20
                                   text-white hover:bg-white/10 transition"
                        >
                            Cancel
                        </button>

                        <button
                            @click="confirmAction"
                            :class="modalType === 'delete'
                                ? 'bg-red-500 hover:bg-red-600'
                                : 'bg-blue-500 hover:bg-blue-600'"
                            class="text-white px-5 py-2 rounded-xl transition"
                        >
                            Confirm
                        </button>

                    </div>

                </div>

            </div>

        </transition>

    </div>

</div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
