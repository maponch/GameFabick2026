<template>
  <v-container>

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else>

      <h1 class="text-h4 mb-6">Mon profil 👤</h1>
      <v-alert v-if="user.scheduled_deletion_at" type="warning" variant="tonal" class="mb-4"
        prepend-icon="mdi-delete-clock">
        <strong>Votre compte est en cours de suppression.</strong>
        Il sera définitivement détruit le <strong>{{ formatDate(user.scheduled_deletion_at) }}</strong>.
        Contactez un administrateur si vous souhaitez annuler cette action.
      </v-alert>

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

        <v-divider class="mb-3" />

        <!-- 2FA -->
        <div class="d-flex align-center justify-space-between">
          <div>
            <div class="text-caption text-medium-emphasis">Double authentification</div>
            <div class="text-body-1">
              <v-chip v-if="user.two_factor_enabled" color="success" size="small" prepend-icon="mdi-shield-check">
                Activée ({{ user.two_factor_method === 'totp' ? 'App' : 'Email' }})
              </v-chip>
              <span v-else class="text-medium-emphasis">Désactivée</span>
            </div>
          </div>
          <v-btn size="small" variant="text" :icon="user.two_factor_enabled ? 'mdi-shield-off' : 'mdi-shield-plus'"
            @click="user.two_factor_enabled ? openDisable2FA() : modals.twoFactor = true" />
        </div>

        <!-- Rôle admin -->
        <template v-if="user.role === 'admin'">
          <v-divider class="my-3" />
          <v-chip color="primary" prepend-icon="mdi-shield-crown" size="small">Administrateur</v-chip>
        </template>
        <v-divider class="my-4" />

        <div class="text-caption font-weight-bold mb-2">
          MES DONNÉES (RGPD)
        </div>

        <p class="text-body-2 text-medium-emphasis mb-2">
          Téléchargez l'ensemble des données personnelles que GameFabrick détient sur vous,
          au format JSON.
        </p>

        <v-btn block variant="tonal" prepend-icon="mdi-download" :loading="exportingData" @click="exportData">
          Exporter mes données
        </v-btn>

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
    <ModalEnableTwoFactor v-model="modals.twoFactor" @enabled="onTwoFactorEnabled" @email-sent="showSuccess('Code envoyé par email.')" />
    <v-dialog v-model="disable2faDialog" max-width="400">
      <v-card class="pa-4">
        <v-card-title>Désactiver la 2FA</v-card-title>
        <v-card-text>
          <v-alert type="warning" variant="tonal" density="compact" class="mb-3">
            Désactiver le 2FA réduit la sécurité de votre compte.
          </v-alert>

          <p class="mb-3">Confirmez avec votre mot de passe :</p>

          <v-text-field v-model="disable2faPassword" label="Mot de passe"
            :type="showDisablePassword ? 'text' : 'password'"
            :append-inner-icon="showDisablePassword ? 'mdi-eye-off' : 'mdi-eye'"
            @click:append-inner="showDisablePassword = !showDisablePassword" variant="outlined"
            :error-messages="disable2faErrors.password" :disabled="disable2faSaving" autocomplete="current-password"
            @keyup.enter="disable2FA" />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="disable2faDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="disable2faSaving" @click="disable2FA">
            Désactiver
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
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
import ModalEnableTwoFactor from '../../components/user/ModalEnableTwoFactor.vue'
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
  twoFactor: false,
})

const disable2faDialog = ref(false)
const disable2faPassword = ref('')
const disable2faErrors = ref({})
const disable2faSaving = ref(false)
const showDisablePassword = ref(false)

const exportingData = ref(false)

async function onTwoFactorEnabled() {
  const { data } = await api.get('/user')
  user.value = data
  showSuccess('2FA activée avec succès.')
}
function openDisable2FA() {
  disable2faPassword.value = ''
  disable2faErrors.value = {}
  disable2faDialog.value = true
}
async function disable2FA() {
  disable2faErrors.value = {}
  if (!disable2faPassword.value) {
    disable2faErrors.value.password = ['Le mot de passe est requis.']
    return
  }

  disable2faSaving.value = true
  try {
    await api.post('/2fa/disable', { password: disable2faPassword.value })
    const { data } = await api.get('/user')
    user.value = data
    disable2faDialog.value = false
    showSuccess('2FA désactivée.')
  } catch (e) {
    if (e.response?.status === 422) disable2faErrors.value = e.response.data.errors ?? {}
    else if (e.response?.status === 403) showError(e.response.data.message)
    else showError('Erreur lors de la désactivation.')
  } finally {
    disable2faSaving.value = false
  }
}
function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR')
}

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
async function exportData() {
  exportingData.value = true
  try {
    const response = await api.get('/account/export', { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([response.data], { type: 'application/json' }))

    const disposition = response.headers['content-disposition'] ?? ''
    const match = disposition.match(/filename="(.+)"/)
    const filename = match?.[1] ?? `gamefabrick-export-${Date.now()}.json`

    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)

    showSuccess('Données exportées.')
  } catch {
    showError('Erreur lors de l\'export.')
  } finally {
    exportingData.value = false
  }
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