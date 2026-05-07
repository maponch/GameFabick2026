<template>
  <v-container>

    <h1 class="text-h4 mb-6">Dashboard Admin 🛡️</h1>

    <!-- STATS -->
    <v-row class="mb-6">
      <v-col cols="12" sm="3">
        <v-card class="pa-4 text-center" color="primary" variant="tonal">
          <div class="text-h3 font-weight-bold">{{ stats.total_users ?? '—' }}</div>
          <div class="text-body-2 mt-1">Utilisateurs total</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="pa-4 text-center" color="success" variant="tonal">
          <div class="text-h3 font-weight-bold">{{ stats.new_this_week ?? '—' }}</div>
          <div class="text-body-2 mt-1">Nouveaux cette semaine</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="pa-4 text-center" color="warning" variant="tonal">
          <div class="text-h3 font-weight-bold">{{ stats.admins ?? '—' }}</div>
          <div class="text-body-2 mt-1">Administrateurs</div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="3">
        <v-card class="pa-4 text-center" color="error" variant="tonal">
          <div class="text-h3 font-weight-bold">{{ stats.suspended ?? '—' }}</div>
          <div class="text-body-2 mt-1">Suspendus actifs</div>
        </v-card>
      </v-col>
    </v-row>

    <!-- LISTE UTILISATEURS -->
    <v-card>
      <v-card-title class="pa-4 d-flex align-center justify-space-between">
        <span>Utilisateurs</span>
        <v-text-field v-model="search" prepend-inner-icon="mdi-magnify" placeholder="Rechercher..." variant="outlined"
          density="compact" hide-details style="max-width: 250px" />
      </v-card-title>

      <v-divider />

      <v-data-table :headers="headers" :items="filteredUsers" :loading="loading" items-per-page="10">
        <!-- Avatar + nom -->
        <template #item.username="{ item }">
          <div class="d-flex align-center ga-3 py-2 cursor-pointer" @click="router.push(`/admin/users/${item.id}`)">
            <v-avatar size="36">
              <v-img :src="item.photo_profile_url" />
            </v-avatar>
            <div>
              <div class="text-primary">{{ item.username }}</div>
              <div v-if="item.is_permanently_banned" class="text-caption text-error">
                Banni définitivement
              </div>
            </div>
          </div>
        </template>

        <!-- Rôle -->
        <template #item.role="{ item }">
          <v-chip :color="item.role === 'admin' ? 'primary' : 'default'" size="small"
            :prepend-icon="item.role === 'admin' ? 'mdi-shield-crown' : 'mdi-account'">
            {{ item.role === 'admin' ? 'Admin' : 'Utilisateur' }}
          </v-chip>
        </template>

        <!-- Statut -->
        <template #item.status="{ item }">
          <v-chip v-if="item.is_permanently_banned" color="error" size="small" prepend-icon="mdi-cancel">
            Banni
          </v-chip>
          <v-chip v-else-if="item.is_suspended" color="warning" size="small" prepend-icon="mdi-account-lock">
            Suspendu ({{ item.suspension_count }}/3)
          </v-chip>
          <v-chip v-else color="success" size="small" prepend-icon="mdi-account-check">
            Actif
          </v-chip>
        </template>

        <!-- Date inscription -->
        <template #item.created_at="{ item }">
          {{ new Date(item.created_at).toLocaleDateString('fr-FR') }}
        </template>

        <!-- Actions -->
        <template #item.actions="{ item }">
          <div class="d-flex ga-1">

            <!-- Promouvoir / rétrograder -->
            <v-tooltip :text="item.role === 'admin' ? 'Retirer admin' : 'Promouvoir admin'">
              <template #activator="{ props }">
                <v-btn v-bind="props" :icon="item.role === 'admin' ? 'mdi-shield-off' : 'mdi-shield-crown'" size="small"
                  variant="text" :color="item.role === 'admin' ? 'warning' : 'primary'"
                  :disabled="item.id === currentUserId" @click="toggleRole(item)" />
              </template>
            </v-tooltip>

            <!-- Suspendre / lever -->
            <v-tooltip :text="item.is_suspended ? 'Lever la suspension' : 'Suspendre'">
              <template #activator="{ props }">
                <v-btn v-bind="props" :icon="item.is_suspended ? 'mdi-account-check' : 'mdi-account-lock'" size="small"
                  variant="text" :color="item.is_suspended ? 'success' : 'warning'"
                  :disabled="item.id === currentUserId || item.is_permanently_banned"
                  @click="item.is_suspended ? unsuspend(item) : openSuspendDialog(item)" />
              </template>
            </v-tooltip>

          </div>
        </template>

      </v-data-table>
    </v-card>

    <!-- DIALOG SUSPENSION -->
    <v-dialog v-model="suspendDialog" max-width="450">
      <v-card class="pa-4">
        <v-card-title>Suspendre {{ userToSuspend?.username }}</v-card-title>
        <v-card-text>

          <v-textarea v-model="suspendForm.reason" label="Raison de la suspension" variant="outlined" rows="3"
            :error-messages="suspendErrors.reason" class="mb-3" />

          <v-select v-model="suspendForm.duration" label="Durée de la suspension" variant="outlined"
            :items="durationOptions" item-title="label" item-value="value" :error-messages="suspendErrors.duration" />

          <v-alert v-if="suspendForm.duration === 'permanent'" type="error" variant="tonal" density="compact"
            class="mt-3">
            Cette action est irréversible — l'utilisateur sera banni définitivement.
          </v-alert>

          <v-alert v-if="userToSuspend?.suspension_count >= 2 && suspendForm.duration !== 'permanent'" type="warning"
            variant="tonal" density="compact" class="mt-3">
            Attention : c'est la {{ userToSuspend.suspension_count + 1 }}e suspension de cet utilisateur.
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

    <!-- DIALOG SUPPRESSION -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="pa-4">
        <v-card-title>Confirmer la suppression</v-card-title>
        <v-card-text>
          Voulez-vous vraiment supprimer <strong>{{ userToDelete?.username }}</strong> ?
          Cette action est irréversible. Les données personnelles seront effacées
          mais l'historique de modération sera conservé de façon anonyme.
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
import { ref, computed, onMounted } from 'vue'
import { api } from '../../api'
import { getUser } from '../../router'
import { useRouter } from 'vue-router'

const router = useRouter() 

const stats = ref({})
const users = ref([])
const loading = ref(true)
const search = ref('')
const actionLoading = ref(false)
const currentUserId = ref(null)
const snackbar = ref({ show: false, message: '', color: 'success' })

// Suppression
const deleteDialog = ref(false)
const userToDelete = ref(null)

// Suspension
const durationOptions = [
  { label: '1 jour', value: '1_day' },
  { label: '7 jours', value: '7_days' },
  { label: '1 mois', value: '1_month' },
  { label: 'Définitif', value: 'permanent' },
]
const suspendDialog = ref(false)
const userToSuspend = ref(null)
const suspendForm = ref({ reason: '', duration: '1_day' })
const suspendErrors = ref({})

const headers = [
  { title: 'Utilisateur', key: 'username', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Rôle', key: 'role', sortable: true },
  { title: 'Statut', key: 'status', sortable: false },
  { title: 'Inscrit le', key: 'created_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const filteredUsers = computed(() => {
  if (!search.value) return users.value
  const q = search.value.toLowerCase()
  return users.value.filter(u =>
    u.username.toLowerCase().includes(q) ||
    u.email.toLowerCase().includes(q)
  )
})

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

async function loadData() {
  loading.value = true
  try {
    const [statsRes, usersRes] = await Promise.all([
      api.get('/admin/stats'),
      api.get('/admin/users'),
    ])
    stats.value = statsRes.data
    users.value = usersRes.data
  } catch {
    showError('Erreur lors du chargement des données.')
  } finally {
    loading.value = false
  }
}

async function toggleRole(user) {
  const newRole = user.role === 'admin' ? 'user' : 'admin'
  try {
    const { data } = await api.patch(`/admin/users/${user.id}/role`, { role: newRole })
    const index = users.value.findIndex(u => u.id === user.id)
    if (index !== -1) users.value[index] = { ...users.value[index], role: data.user.role }
    showSuccess(`Rôle mis à jour pour ${user.username}.`)
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur lors de la mise à jour.')
  }
}

function openSuspendDialog(user) {
  userToSuspend.value = user
  suspendForm.value = { reason: '', expires_at: '', permanent: false }
  suspendErrors.value = {}
  suspendDialog.value = true
}

async function suspendUser() {
  suspendErrors.value = {}

  if (!suspendForm.value.reason.trim()) {
    suspendErrors.value.reason = ['La raison est requise.']
    return
  }
  if (!suspendForm.value.duration) {
    suspendErrors.value.duration = ['Choisissez une durée.']
    return
  }

  actionLoading.value = true
  try {
    await api.post(`/admin/users/${userToSuspend.value.id}/suspend`, suspendForm.value)
    const index = users.value.findIndex(u => u.id === userToSuspend.value.id)
    if (index !== -1) {
      users.value[index] = {
        ...users.value[index],
        is_suspended: true,
        suspension_count: users.value[index].suspension_count + 1,
        is_permanently_banned: suspendForm.value.duration === 'permanent',
      }
    }
    stats.value.suspended++
    suspendDialog.value = false
    showSuccess(`${userToSuspend.value.username} suspendu.`)
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur lors de la suspension.')
  } finally {
    actionLoading.value = false
  }
}

async function unsuspend(user) {
  try {
    await api.patch(`/admin/users/${user.id}/unsuspend`)
    const index = users.value.findIndex(u => u.id === user.id)
    if (index !== -1) users.value[index] = { ...users.value[index], is_suspended: false }
    stats.value.suspended--
    showSuccess(`Suspension de ${user.username} levée.`)
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur lors de la levée de suspension.')
  }
}

function confirmDelete(user) {
  userToDelete.value = user
  deleteDialog.value = true
}

async function deleteUser() {
  actionLoading.value = true
  try {
    await api.delete(`/admin/users/${userToDelete.value.id}`)
    users.value = users.value.filter(u => u.id !== userToDelete.value.id)
    stats.value.total_users--
    deleteDialog.value = false
    showSuccess(`${userToDelete.value.username} supprimé.`)
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur lors de la suppression.')
  } finally {
    actionLoading.value = false
  }
}

onMounted(async () => {
  const user = await getUser()
  currentUserId.value = user?.id
  await loadData()
})
</script>