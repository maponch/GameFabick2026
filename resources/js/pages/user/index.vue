<template>
  <v-container>

    <h1 class="text-h4 mb-6">Mon profil 👤</h1>

    <v-card class="pa-5">

      <p v-if="user.role === 'admin'">
        <strong>Rôle :</strong> Admin
      </p>
      <p><strong>Pseudo :</strong> {{ user.username }}</p>
      <p><strong>Email :</strong> {{ user.email }}</p>

      

    </v-card>

  </v-container>
</template>

<script setup>
const user = window.user
</script>