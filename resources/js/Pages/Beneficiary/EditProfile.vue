<script setup>
import { usePage } from '@inertiajs/vue3'
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue'
import SectionBorder from '@/Components/SectionBorder.vue'
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue'
import LogoutOtherBrowserSessionsForm from '@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue'
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue'

const page = usePage()
const sessions = page.props.sessions
</script>

<template>
<div>

  <!-- PROFILE INFORMATION -->
  <div v-if="$page.props.jetstream.canUpdateProfileInformation">
    <UpdateProfileInformationForm :user="$page.props.auth.user" />
    <SectionBorder />
  </div>

  <!-- UPDATE PASSWORD -->
  <div v-if="$page.props.jetstream.canUpdatePassword">
    <UpdatePasswordForm />
    <SectionBorder />
  </div>

  <!-- BROWSER SESSIONS -->
  <LogoutOtherBrowserSessionsForm :sessions="sessions" />
  <SectionBorder />

  <!-- DELETE ACCOUNT -->
  <div v-if="$page.props.jetstream.hasAccountDeletionFeatures">
    <DeleteUserForm />
  </div>

</div>
</template>