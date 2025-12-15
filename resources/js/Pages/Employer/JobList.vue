<script setup>
defineProps({
  jobs: Array
});
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">My Job Listings</h1>

    <a href="/employer/jobs/create" class="btn-primary mb-3">+ New Job</a>

    <table class="table-auto w-full bg-white shadow">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2">Title</th>
          <th class="p-2">Location</th>
          <th class="p-2">Slots</th>
          <th class="p-2">Closing</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="job in jobs" :key="job.id">
          <td class="p-2">{{ job.title }}</td>
          <td class="p-2">{{ job.location }}</td>
          <td class="p-2">{{ job.slots }}</td>
          <td class="p-2">{{ job.closing_date }}</td>
          <td class="p-2 space-x-2">
            <a :href="`/employer/jobs/${job.id}/edit`" class="btn-secondary">Edit</a>

            <form :action="`/employer/jobs/${job.id}`" method="POST" @submit.prevent="$event.target.submit()">
              <input type="hidden" name="_method" value="DELETE" />
              <button class="btn-danger">Delete</button>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
