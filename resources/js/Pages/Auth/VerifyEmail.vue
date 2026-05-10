<script setup>
import { computed } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    status: String,
})

const form = useForm({})

const submit = () => {
    form.post(route('verification.send'))
}

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent'
)

const userEmail = computed(() => {
    return usePage().props.auth.user.email
})
</script>

<template>
    <Head title="Email Verification - SPES System" />

    <div class="min-h-screen flex items-center justify-center bg-gray-200 px-4">
        <div class="bg-white p-10 rounded-2xl shadow-md w-full max-w-lg text-center">

            <!-- Email Icon -->
            <div class="flex justify-center mb-6">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-7 w-7 text-green-600" 
                         fill="none" 
                         viewBox="0 0 24 24" 
                         stroke="currentColor">
                        <path stroke-linecap="round" 
                              stroke-linejoin="round" 
                              stroke-width="2" 
                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8m-18 0a2 2 0 012-2h14a2 2 0 012 2m-18 0v8a2 2 0 002 2h14a2 2 0 002-2V8"/>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-2xl font-semibold text-gray-800 mb-2">
                Please verify your email
            </h2>

            <!-- Email Info -->
            <p class="text-gray-600 mb-6">
                We've sent a verification link to<br>
                <span class="font-medium text-gray-800">
                    {{ userEmail }}
                </span>
            </p>

            <p class="text-sm text-gray-500 mb-6">
                Just click the link in that email to complete your signup.
                If you don’t see it, you may need to check your spam folder.
            </p>

            <!-- Success Message -->
            <div v-if="verificationLinkSent"
                 class="mb-4 text-green-600 text-sm">
                A new verification link has been sent to your email.
            </div>

            <!-- Resend Button -->
            <form @submit.prevent="submit">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-gray-800 text-white py-3 rounded-lg hover:bg-gray-900 transition disabled:opacity-50"
                >
                    Resend Verification Email
                </button>
            </form>
        </div>
    </div>
</template>
