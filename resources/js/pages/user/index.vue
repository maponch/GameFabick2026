<template>
  <v-container>

    <h1 class="text-h4 mb-6">Mon profil 👤</h1>

    <v-card color="light-blue" class="pa-5">

      <p v-if="user.role === 'admin'">
        <strong>Rôle :</strong> Admin
      </p>
      <p><strong>Pseudo :</strong> {{ user.username }}</p>
      <p><strong>Email :</strong> {{ user.email }}</p>
      <v-avatar size="120" class="mt-4">
        <v-img :src="user.photo_profile_url" />
      </v-avatar> 

      

    </v-card>

  </v-container>
</template>

<script setup>
const user = window.user
console.log(user)
</script>