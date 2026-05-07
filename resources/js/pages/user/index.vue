<template>
  <v-container>

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else>

      <h1 class="text-h4 mb-6">Mon profil 👤</h1>

      <v-card class="pa-6" max-width="500">

        <!-- Photo -->
        <div class="d-flex flex-column align-center mb-6">
          <v-avatar size="120" class="mb-3">
            <v-img :src="user.photo_profile_url" />
          </v-avatar>
          <v-btn size="small" variant="outlined" prepend-icon="mdi-camera" @click="modals.photo = true">
            Changer la photo
          </v-btn>
        </div>

        <v-divider class="mb-4" />

        <!-- Username -->
        <div class="d-flex align-center justify-space-between mb-3">
          <div>
            <div class="text-caption text-medium-emphasis">Pseudo</div>
            <div class="text-body-1">{{ user.username }}</div>
          </div>
          <v-btn size="small" variant="text" icon="mdi-pencil" @click="modals.username = true" />
        </div>

        <v-divider class="mb-3" />

        <!-- Email -->
        <div class="d-flex align-center justify-space-between mb-3">
          <div>
            <div class="text-caption text-medium-emphasis">Email</div>
            <div class="text-body-1">{{ user.email }}</div>
          </div>
          <v-icon color="grey-lighten-1">mdi-lock</v-icon>
        </div>

        <v-divider class="mb-3" />

        <!-- Mot de passe -->
        <div class="d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-medium-emphasis">Mot de passe</div>
            <div class="text-body-1">••••••••</div>
          </div>
          <v-btn size="small" variant="text" icon="mdi-pencil" @click="modals.password = true" />
        </div>

        <!-- Rôle admin -->
        <template v-if="user.role === 'admin'">
          <v-divider class="my-3" />
          <v-chip color="primary" prepend-icon="mdi-shield-crown" size="small">Administrateur</v-chip>
        </template>

        <!-- Suppression de compte -->
        <v-divider class="my-4" />

        <div class="text-error text-caption font-weight-bold mb-2">
          ZONE DANGER
        </div>

        <v-btn block color="error" variant="tonal" prepend-icon="mdi-account-remove" @click="modals.delete = true">
          Supprimer mon compte
        </v-btn>

      </v-card>
    </div>

    <!-- Modals -->
    <ModalPhoto v-model="modals.photo" :current-url="user?.photo_profile_url" @updated="onUserUpdated"
      @error="showError" />
    <ModalUsername v-model="modals.username" :current-username="user?.username" @updated="onUserUpdated"
      @error="showError" />
    <ModalPassword v-model="modals.password" @updated="showSuccess('Mot de passe mis à jour.')" @error="showError" />

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" location="bottom right">
      {{ snackbar.message }}
    </v-snackbar>
    <ModalDeleteAccount v-model="modals.delete" @deleted="onAccountDeleted" @error="showError" />

  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../api'
import ModalPhoto from '../../components/user/ModalPhoto.vue'
import ModalUsername from '../../components/user/ModalUsername.vue'
import ModalPassword from '../../components/user/ModalPassword.vue'
import ModalDeleteAccount from '../../components/user/ModalDeleteAccount.vue'
import { clearUser } from '../../router'
import { useRouter } from 'vue-router'

const user = ref(null)
const loading = ref(true)
const snackbar = ref({ show: false, message: '', color: 'success' })

const router = useRouter()

const modals = ref({
  photo: false,
  username: false,
  password: false,
  delete: false,
})

function onAccountDeleted() {
  clearUser()
  router.push('/login')
}

function showSuccess(message) {
  snackbar.value = { show: true, message, color: 'success' }
}
function showError(message) {
  snackbar.value = { show: true, message, color: 'error' }
}

function onUserUpdated(updatedUser) {
  user.value = updatedUser
  showSuccess('Profil mis à jour.')
}

onMounted(async () => {
  try {
    await api.get('/sanctum/csrf-cookie')
    const { data } = await api.get('/user')
    user.value = data
  } catch {
    window.location.href = '/login'
  } finally {
    loading.value = false
  }
})
</script>