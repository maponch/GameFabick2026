<template>
  <v-container>

    <!-- chargement -->
    <div v-if="loading">
      Chargement...
    </div>

    <!-- contenu -->
    <div v-else>

      <h1 class="text-h4 mb-6">Mon profil 👤</h1>

      <v-card color="light-blue" class="pa-5">

        <p v-if="user?.role === 'admin'">
          <strong>Rôle :</strong> Admin
        </p>

        <p><strong>Pseudo :</strong> {{ user?.username }}</p>
        <p><strong>Email :</strong> {{ user?.email }}</p>

        <v-avatar size="120" class="mt-4">
          <v-img :src="user?.photo_profile_url" />
        </v-avatar>

      </v-card>

    </div>

  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../api'

const user = ref(null)
const loading = ref(true) // ✅ déclaré + initialisé à true

onMounted(async () => {
  try {
    await api.get('/sanctum/csrf-cookie') // ✅ initialise la session
    const { data } = await api.get('/user')
    user.value = data
    console.log('User data:', data)
  } catch (e) {
    window.location.href = '/login'
  } finally {
    loading.value = false
  }
})
</script>