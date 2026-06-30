<template>
  <v-container>

    <v-btn variant="text" prepend-icon="mdi-arrow-left" class="mb-4" @click="$router.back()">
      Retour
    </v-btn>

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else-if="user">

      <v-alert v-if="user.is_pending_deletion" type="warning" variant="tonal" class="mb-4"
        prepend-icon="mdi-delete-clock">
        Ce compte est en attente de suppression définitive le <strong>{{ user.scheduled_deletion_at }}</strong>.
        Seul l'annulation est possible jusqu'à cette date.
      </v-alert>

      <v-row>

        <!-- INFOS UTILISATEUR -->
        <v-col cols="12" md="4">
          <v-card class="pa-6 text-center">

            <v-avatar size="100" class="mb-4">
              <v-img :src="user.photo_profile_url" />
            </v-avatar>

            <div class="text-h6">{{ user.username }}</div>
            <div class="text-body-2 text-medium-emphasis mb-3">{{ user.email }}</div>

            <div class="d-flex justify-center ga-2 mb-4 flex-wrap">
              <v-chip :color="user.role === 'admin' ? 'primary' : 'default'" size="small"
                :prepend-icon="user.role === 'admin' ? 'mdi-shield-crown' : 'mdi-account'">
                {{ user.role === 'admin' ? 'Admin' : 'Utilisateur' }}
              </v-chip>

              <v-chip v-if="user.is_pending_deletion" color="deep-orange" size="small" prepend-icon="mdi-delete-clock">
                Suppression en cours
              </v-chip>
              <v-chip v-else-if="user.is_permanently_banned" color="error" size="small" prepend-icon="mdi-cancel">
                Banni
              </v-chip>
              <v-chip v-else-if="user.is_suspended" color="warning" size="small" prepend-icon="mdi-account-lock">
                Suspendu
              </v-chip>
              <v-chip v-else color="success" size="small" prepend-icon="mdi-account-check">
                Actif
              </v-chip>
            </div>

            <v-divider class="mb-4" />

            <div class="text-left">
              <div class="d-flex justify-space-between mb-2">
                <span class="text-caption text-medium-emphasis">Inscrit le</span>
                <span class="text-body-2">{{ formatDate(user.created_at) }}</span>
              </div>
              <div class="d-flex justify-space-between mb-2">
                <span class="text-caption text-medium-emphasis">Email vérifié</span>
                <v-icon :color="user.email_verified_at ? 'success' : 'error'" size="small">
                  {{ user.email_verified_at ? 'mdi-check-circle' : 'mdi-close-circle' }}
                </v-icon>
              </div>
              <div class="d-flex justify-space-between">
                <span class="text-caption text-medium-emphasis">Suspensions</span>
                <span class="text-body-2">{{ user.suspension_count }} / 3</span>
              </div>
              <div v-if="user.is_pending_deletion" class="d-flex justify-space-between mt-2">
                <span class="text-caption text-medium-emphasis">Suppression définitive</span>
                <span class="text-body-2 text-deep-orange">{{ user.scheduled_deletion_at }}</span>
              </div>
            </div>

          </v-card>

          <!-- ACTIONS -->
          <v-card class="pa-4 mt-4">
            <v-card-title class="text-body-1 font-weight-bold mb-3">Actions</v-card-title>

            <!-- Promouvoir / rétrograder -->
            <v-btn block class="mb-2" :color="user.role === 'admin' ? 'warning' : 'primary'" variant="tonal"
              :prepend-icon="user.role === 'admin' ? 'mdi-shield-off' : 'mdi-shield-crown'"
              :disabled="user.id === currentUserId || user.is_pending_deletion" @click="toggleRole">
              {{ user.role === 'admin' ? 'Retirer admin' : 'Promouvoir admin' }}
            </v-btn>

            <!-- Suspendre / lever -->
            <v-btn v-if="!user.is_permanently_banned" block class="mb-2"
              :color="user.is_suspended ? 'success' : 'warning'" variant="tonal"
              :prepend-icon="user.is_suspended ? 'mdi-account-check' : 'mdi-account-lock'"
              :disabled="user.id === currentUserId || user.is_pending_deletion"
              @click="user.is_suspended ? unsuspend() : suspendDialog = true">
              {{ user.is_suspended ? 'Lever la suspension' : 'Suspendre' }}
            </v-btn>
            
            <!-- 2FA -->
            <v-divider class="my-3" />
            <div class="text-caption text-medium-emphasis mb-2">DOUBLE AUTHENTIFICATION</div>

            <div v-if="user.two_factor_enabled">
              <v-chip color="success" size="small" prepend-icon="mdi-shield-check" class="mb-2">
                Activée ({{ user.two_factor_method === 'totp' ? 'App' : 'Email' }})
              </v-chip>

              <v-btn block color="warning" variant="tonal" prepend-icon="mdi-key-variant" class="mb-2"
                :disabled="user.is_pending_deletion" @click="regenerateDialog = true">
                Régénérer les codes
              </v-btn>

              <v-btn block class="mb-2" color="error" variant="tonal" prepend-icon="mdi-shield-off"
                :disabled="user.is_pending_deletion" @click="disable2faAdminDialog = true">
                Désactiver le 2FA
              </v-btn>
              <v-btn v-if="!user.is_pending_deletion" block color="error" variant="tonal" prepend-icon="mdi-delete"
                :disabled="user.id === currentUserId" @click="deleteDialog = true">
                Supprimer le compte
              </v-btn>

              <v-btn v-else block color="success" variant="tonal" prepend-icon="mdi-restore"
                @click="cancelDeletionDialog = true">
                Annuler la suppression
              </v-btn>
            </div>
            <div v-else>
              <v-chip color="default" size="small">Non activée</v-chip>
            </div>
            <!-- DIALOG RÉGÉNÉRATION CODES -->
            <v-dialog v-model="regenerateDialog" max-width="500">
              <v-card class="pa-4">
                <v-card-title>Régénérer les codes de secours</v-card-title>

                <v-card-text>
                  <v-alert type="info" variant="tonal" density="compact">
                    Les nouveaux codes seront envoyés directement par email à l'utilisateur.
                    Les anciens codes seront immédiatement invalidés.
                  </v-alert>
                </v-card-text>

                <v-card-actions class="justify-end">
                  <v-btn variant="text" @click="regenerateDialog = false">Annuler</v-btn>
                  <v-btn color="warning" :loading="actionLoading" @click="regenerateCodes">
                    Régénérer et envoyer
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>

            <!-- Supprimer -->          
            <v-dialog v-model="cancelDeletionDialog" max-width="400">
              <v-card class="pa-4">
                <v-card-title>Annuler la suppression</v-card-title>
                <v-card-text>
                  Le compte sera restauré et l'utilisateur pourra à nouveau se connecter normalement.
                </v-card-text>
                <v-card-actions class="justify-end">
                  <v-btn variant="text" @click="cancelDeletionDialog = false">Annuler</v-btn>
                  <v-btn color="success" :loading="actionLoading" @click="cancelDeletion">
                    Confirmer
                  </v-btn>
                </v-card-actions>
              </v-card>
            </v-dialog>
          </v-card>
        </v-col>

        <!-- HISTORIQUE SUSPENSIONS -->
        <v-col cols="12" md="8">
          <v-card>
            <v-card-title class="pa-4">Historique des suspensions</v-card-title>
            <v-divider />

            <v-list v-if="user.suspensions.length > 0">
              <v-list-item v-for="suspension in user.suspensions" :key="suspension.id" class="py-3">
                <template #prepend>
                  <v-icon :color="suspension.is_active ? 'warning' : 'default'">
                    {{ suspension.is_active ? 'mdi-account-lock' : 'mdi-account-lock-open' }}
                  </v-icon>
                </template>

                <v-list-item-title>{{ suspension.reason }}</v-list-item-title>
                <v-list-item-subtitle>
                  Par {{ suspension.admin }} · {{ suspension.created_at }}
                </v-list-item-subtitle>
                <v-list-item-subtitle v-if="suspension.expires_at">
                  Expire le {{ suspension.expires_at }}
                </v-list-item-subtitle>
                <v-list-item-subtitle v-else>
                  Permanent
                </v-list-item-subtitle>

                <template #append>
                  <v-chip :color="suspension.is_active ? 'warning' : 'default'" size="x-small">
                    {{ suspension.is_active ? 'Active' : 'Levée' }}
                  </v-chip>
                </template>
              </v-list-item>
            </v-list>

            <div v-else class="pa-6 text-center text-medium-emphasis">
              Aucune suspension enregistrée.
            </div>
          </v-card>
        </v-col>

      </v-row>

    </div>

    <!-- DIALOG SUSPENSION -->
    <v-dialog v-model="suspendDialog" max-width="450">
      <v-card class="pa-4">
        <v-card-title>Suspendre {{ user?.username }}</v-card-title>
        <v-card-text>
          <v-textarea v-model="suspendForm.reason" label="Raison de la suspension" variant="outlined" rows="3"
            :error-messages="suspendErrors.reason" class="mb-3" />
          <v-select v-model="suspendForm.duration" label="Durée" variant="outlined" :items="durationOptions"
            item-title="label" item-value="value" :error-messages="suspendErrors.duration" />
          <v-alert v-if="suspendForm.duration === 'permanent'" type="error" variant="tonal" density="compact"
            class="mt-3">
            Cette action est irréversible.
          </v-alert>
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="suspendDialog = false">Annuler</v-btn>
          <v-btn :color="suspendForm.duration === 'permanent' ? 'error' : 'warning'" :loading="actionLoading"
            @click="suspendUser">
            Suspendre
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="disable2faAdminDialog" max-width="450">
      <v-card class="pa-4">
        <v-card-title>Désactiver le 2FA</v-card-title>
        <v-card-text>
          <v-alert type="warning" variant="tonal" density="compact" class="mb-3">
            L'utilisateur sera notifié par email de cette action.
          </v-alert>

          <v-textarea v-model="disable2faReason" label="Raison (visible par l'utilisateur)" variant="outlined" rows="3"
            :error-messages="disable2faErrors.reason"
            placeholder="Ex: Demande utilisateur suite à perte des codes de secours, identité vérifiée par email du 07/05/2026" />
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="disable2faAdminDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="actionLoading" @click="disable2faAdmin">
            Désactiver et notifier
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- DIALOG SUPPRESSION -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="pa-4">
        <v-card-title>Supprimer {{ user?.username }}</v-card-title>
        <v-card-text>
          Le compte sera archivé 30 jours avant destruction définitive.
          Un email de confirmation sera envoyé à l'utilisateur.
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="actionLoading" @click="deleteUser">
            Supprimer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" location="bottom right">
      {{ snackbar.message }}
    </v-snackbar>

  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api } from '../../api'
import { getUser } from '../../router'

const router = useRouter()
const route = useRoute()

const user = ref(null)
const loading = ref(true)
const actionLoading = ref(false)
const currentUserId = ref(null)
const snackbar = ref({ show: false, message: '', color: 'success' })

const deleteDialog = ref(false)
const suspendDialog = ref(false)
const suspendForm = ref({ reason: '', duration: '1_day' })
const suspendErrors = ref({})

const disable2faAdminDialog = ref(false)
const regenerateDialog = ref(false)
const disable2faReason = ref('')
const disable2faErrors = ref({})

const cancelDeletionDialog = ref(false)

const durationOptions = [
  { label: '1 jour', value: '1_day' },
  { label: '7 jours', value: '7_days' },
  { label: '1 mois', value: '1_month' },
  { label: 'Définitif', value: 'permanent' },
]

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR')
}

async function loadUser() {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/users/${route.params.id}`)
    user.value = data
  } catch {
    showError('Erreur lors du chargement.')
    router.push('/admin')
  } finally {
    loading.value = false
  }
}

async function toggleRole() {
  const newRole = user.value.role === 'admin' ? 'user' : 'admin'
  try {
    await api.patch(`/admin/users/${user.value.id}/role`, { role: newRole })
    user.value.role = newRole
    showSuccess('Rôle mis à jour.')
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  }
}

async function suspendUser() {
  suspendErrors.value = {}
  if (!suspendForm.value.reason.trim()) {
    suspendErrors.value.reason = ['La raison est requise.']
    return
  }
  actionLoading.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/suspend`, suspendForm.value)
    user.value.is_suspended = true
    user.value.suspension_count++
    if (suspendForm.value.duration === 'permanent') user.value.is_permanently_banned = true
    suspendDialog.value = false
    showSuccess('Utilisateur suspendu.')
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  } finally {
    actionLoading.value = false
  }
}

async function unsuspend() {
  try {
    await api.patch(`/admin/users/${user.value.id}/unsuspend`)
    user.value.is_suspended = false
    showSuccess('Suspension levée.')
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  }
}
async function disable2faAdmin() {
  disable2faErrors.value = {}
  if (!disable2faReason.value.trim()) {
    disable2faErrors.value.reason = ['La raison est requise.']
    return
  }

  actionLoading.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/disable-2fa`, {
      reason: disable2faReason.value
    })
    user.value.two_factor_enabled = false
    user.value.two_factor_method = null
    disable2faAdminDialog.value = false
    disable2faReason.value = ''
    showSuccess('2FA désactivée et utilisateur notifié.')
  } catch (e) {
    if (e.response?.status === 422) disable2faErrors.value = e.response.data.errors ?? {}
    else showError('Erreur.')
  } finally {
    actionLoading.value = false
  }
}
async function regenerateCodes() {
  actionLoading.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/regenerate-2fa-codes`)
    regenerateDialog.value = false
    showSuccess('Codes régénérés et envoyés par email.')
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  } finally {
    actionLoading.value = false
  }
}

async function deleteUser() {
  actionLoading.value = true
  try {
    await api.delete(`/admin/users/${user.value.id}`)
    deleteDialog.value = false
    showSuccess('Compte supprimé.')
    setTimeout(() => router.push('/admin'), 1500)
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  } finally {
    actionLoading.value = false
  }
}

async function cancelDeletion() {
  actionLoading.value = true
  try {
    await api.post(`/admin/users/${user.value.id}/cancel-deletion`)
    user.value.is_pending_deletion = false
    user.value.scheduled_deletion_at = null
    cancelDeletionDialog.value = false
    showSuccess('Suppression annulée.')
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur.')
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  const me = await getUser()
  currentUserId.value = me?.id
  await loadUser()
})
</script>