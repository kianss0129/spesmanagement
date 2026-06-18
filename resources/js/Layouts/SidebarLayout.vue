<template>
  <div class="d-flex min-vh-100 bg-dark text-white">
    <!-- Sidebar -->
    <div class="d-flex flex-column p-3 text-white bg-dark shadow" style="width: 250px;">
      <a href="/" class="d-flex align-items-center mb-3 text-white text-decoration-none">
        <i class="bi bi-bootstrap-fill fs-3 me-2"></i>
        <span class="fs-4">Sidebar</span>
      </a>
      <hr>
      <ul class="nav nav-pills flex-column mb-auto">
        <li>
          <Link class="nav-link text-white" href="/dashboard">
            <i class="bi bi-house-door me-2"></i> Dashboard
          </Link>
        </li>
        <!-- Patients link removed — SPES system does not use Patient role -->
        <li>
          <Link class="nav-link text-white" href="/logout" method="post" as="button">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
          </Link>
        </li>
      </ul>
    </div>

    <!-- Main Content -->
    <div class="flex-grow-1 p-4 bg-light text-dark">
      <slot />
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>
