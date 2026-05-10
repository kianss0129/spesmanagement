<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue';
import SectionBorder from '@/Components/SectionBorder.vue';
import TwoFactorAuthenticationForm from '@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
    beneficiary: Object,
    ratings: Array,
    average: Number,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">

                <!-- ✅ Jetstream Account Info -->
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm :user="$page.props.auth.user" />
                    <SectionBorder />
                </div>

                <!-- 🔥 BENEFICIARY INFORMATION SECTION -->
                <div v-if="beneficiary" class="bg-white p-6 rounded shadow mb-10">
                    
                    <!-- Header -->
                    <div class="flex items-center space-x-4 mb-6">
                        <img
                            v-if="beneficiary.profile_photo_url"
                            :src="beneficiary.profile_photo_url"
                            class="w-20 h-20 rounded-full object-cover"
                        />
                        <div>
                            <h3 class="text-2xl font-bold">{{ beneficiary.name }}</h3>
                            <p class="text-sm text-gray-500">{{ beneficiary.email }}</p>
                            <p class="text-sm text-gray-500">{{ beneficiary.contact_number || 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Average Rating -->
                    <div class="mb-6">
                        <h3 class="font-semibold text-lg">
                            Average Rating: {{ average || 0 }} / 5
                        </h3>
                    </div>

                    <!-- Personal Info -->
                    <div class="border p-4 rounded mb-6">
                        <h4 class="font-semibold mb-2">Personal Information</h4>
                        <p><strong>Birthdate:</strong> {{ beneficiary.birthdate || 'N/A' }}</p>
                        <p><strong>Gender:</strong> {{ beneficiary.gender || 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ beneficiary.address || 'N/A' }}</p>
                    </div>

                    <!-- School -->
                    <div v-if="beneficiary.school" class="border p-4 rounded mb-6">
                        <h4 class="font-semibold mb-2">School Information</h4>
                        <p><strong>School:</strong> {{ beneficiary.school.name || 'N/A' }}</p>
                        <p><strong>Program:</strong> {{ beneficiary.school.program || 'N/A' }}</p>
                        <p><strong>Year Level:</strong> {{ beneficiary.school.year_level || 'N/A' }}</p>
                        <p><strong>Student ID:</strong> {{ beneficiary.school.student_id || 'N/A' }}</p>
                    </div>

                    <!-- PESO -->
                    <div v-if="beneficiary.pesoOffice" class="border p-4 rounded mb-6">
                        <h4 class="font-semibold mb-2">Assigned PESO Office</h4>
                        <p><strong>Office Name:</strong> {{ beneficiary.pesoOffice.name || 'N/A' }}</p>
                        <p><strong>Officer:</strong> {{ beneficiary.pesoOffice.officer_name || 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ beneficiary.pesoOffice.address || 'N/A' }}</p>
                        <p><strong>Contact:</strong> {{ beneficiary.pesoOffice.contact_number || 'N/A' }}</p>
                    </div>

                    <!-- Work History -->
                    <div v-if="beneficiary.workHistory?.length" class="border p-4 rounded mb-6">
                        <h4 class="font-semibold mb-2">SPES Work History</h4>

                        <table class="w-full border-collapse border text-sm">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border px-2 py-1 text-left">Employer</th>
                                    <th class="border px-2 py-1 text-left">Position</th>
                                    <th class="border px-2 py-1 text-left">Duration</th>
                                    <th class="border px-2 py-1 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="work in beneficiary.workHistory" :key="work.id">
                                    <td class="border px-2 py-1">{{ work.employer?.name || 'N/A' }}</td>
                                    <td class="border px-2 py-1">{{ work.position || 'N/A' }}</td>
                                    <td class="border px-2 py-1">{{ work.duration || 'N/A' }}</td>
                                    <td class="border px-2 py-1">{{ work.status || 'N/A' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Ratings -->
                    <div v-if="ratings?.length" class="border p-4 rounded">
                        <h4 class="font-semibold mb-2">Employer Ratings</h4>

                        <div v-for="rating in ratings" :key="rating.id" class="border p-3 rounded mb-2">
                            <p><strong>Employer:</strong> {{ rating.employer?.name || 'N/A' }}</p>
                            <p><strong>Overall:</strong> {{ rating.overall }}</p>
                            <p><strong>Comment:</strong> {{ rating.comment || 'N/A' }}</p>
                        </div>
                    </div>

                    <div v-else>
                        <p>No ratings available yet.</p>
                    </div>

                </div>

                <!-- Password -->
                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <SectionBorder />
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />
                </div>

                <!-- Sessions -->
                <SectionBorder />
                <LogoutOtherBrowserSessionsForm :sessions="sessions" class="mt-10 sm:mt-0" />

                <!-- Delete -->
                <template v-if="$page.props.jetstream.hasAccountDeletionFeatures">
                    <SectionBorder />
                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>

            </div>
        </div>
    </AppLayout>
</template>