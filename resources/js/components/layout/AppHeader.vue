<template>

  <v-app-bar color="primary" dark>
    <v-toolbar-title>GameFabrick 🎲</v-toolbar-title>

    <v-spacer />

    <!-- DESKTOP : SI NON CONNECTÉ -->
    <template v-if="!user && mdAndUp">
      <v-btn variant="text" to="/login">Connexion</v-btn>
      <v-btn variant="outlined" to="/register">Inscription</v-btn>
    </template>

    <!-- DESKTOP : SI CONNECTÉ -->
    <template v-if="user && mdAndUp">
      <v-btn variant="text" to="/dashboard">Dashboard</v-btn>
      <v-btn variant="text" :to="{ name: 'profile' }">Profil</v-btn>
      <v-btn v-if="user.role === 'admin'" variant="text" to="/admin">Admin</v-btn>
      <v-btn variant="text" :loading="logoutLoading" @click="logout">Logout</v-btn>
    </template>

    <!-- MOBILE : bouton hamburger -->
    <v-btn v-if="!mdAndUp" icon @click="drawer = !drawer">
      <v-icon>mdi-menu</v-icon>
    </v-btn>

  </v-app-bar>

  <!-- DRAWER MOBILE -->
  <v-navigation-drawer v-model="drawer" temporary location="right">

    <v-list-item title="GameFabrick 🎲" class="py-4" />

    <v-divider />

    <!-- Non connecté -->
    <template v-if="!user">
      <v-list-item prepend-icon="mdi-login" title="Connexion" to="/login" @click="drawer = false" />
      <v-list-item prepend-icon="mdi-account-plus" title="Inscription" to="/register" @click="drawer = false" />
    </template>

    <!-- Connecté -->
    <template v-else>
      <v-list-item prepend-icon="mdi-view-dashboard" title="Dashboard" to="/dashboard" @click="drawer = false" />
      <v-list-item prepend-icon="mdi-account" title="Profil" :to="{ name: 'profile' }" @click="drawer = false" />
      <v-list-item v-if="user.role === 'admin'" prepend-icon="mdi-shield-crown" title="Admin" to="/admin"
        @click="drawer = false" />

      <v-divider />

      <v-list-item prepend-icon="mdi-logout" title="Déconnexion" :disabled="logoutLoading" @click="logout" />
    </template>

  </v-navigation-drawer>

</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useDisplay } from 'vuetify'
import { api } from '../../api'
import { getUser, clearUser } from '../../router'

const { mdAndUp } = useDisplay()
const router = useRouter()
const route = useRoute()

const user = ref(null)
const logoutLoading = ref(false)
const drawer = ref(false)

// ✅ Se rafraîchit à chaque changement de route (login, register, etc.)
watch(
  () => route.path,
  async () => { user.value = await getUser() },
  { immediate: true } // déclenché aussi au premier rendu
)

async function logout() {
  logoutLoading.value = true
  drawer.value = false
  try {
    await api.post('/logout')
  } finally {
    clearUser()
    user.value = null
    logoutLoading.value = false
    router.push('/login')
  }
}
</script>