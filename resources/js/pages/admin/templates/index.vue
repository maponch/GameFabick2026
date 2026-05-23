<template>
  <v-container>
    <div class="d-flex align-center justify-space-between mb-6">
      <h1 class="text-h4">Templates de jeu</h1>
      <v-btn color="primary" prepend-icon="mdi-plus" @click="goCreate">
        Nouveau template
      </v-btn>
    </div>

    <v-text-field v-model="search" label="Rechercher un template" prepend-inner-icon="mdi-magnify" variant="outlined"
      density="comfortable" clearable class="mb-4" />

    <v-data-table :headers="headers" :items="filteredTemplates" :loading="loading" no-data-text="Aucun template."
      loading-text="Chargement…">
      <template #item.status="{ item }">
        <v-chip :color="statusConfig[item.status]?.color" size="small" label>
          {{ statusConfig[item.status]?.label ?? item.status }}
        </v-chip>
      </template>

      <template #item.updated_at="{ item }">
        {{ formatDate(item.updated_at) }}
      </template>

      <template #item.actions="{ item }">
        <v-btn icon="mdi-pencil" size="small" variant="text" @click="goEdit(item.id)" />

        <v-menu>
          <template #activator="{ props }">
            <v-btn icon="mdi-dots-vertical" size="small" variant="text" v-bind="props" />
          </template>
          <v-list>
            <v-list-item v-if="item.status !== 'published'" title="Publier" prepend-icon="mdi-check-circle"
              @click="changeStatus(item, 'published')" />
            <v-list-item v-if="item.status !== 'draft'" title="Repasser en brouillon" prepend-icon="mdi-pencil-circle"
              @click="changeStatus(item, 'draft')" />
            <v-list-item v-if="item.status !== 'archived'" title="Archiver" prepend-icon="mdi-archive"
              @click="changeStatus(item, 'archived')" />
            <v-divider />
            <v-list-item title="Supprimer" prepend-icon="mdi-delete" base-color="error" @click="askDelete(item)" />
          </v-list>
        </v-menu>
      </template>
    </v-data-table>

    <v-dialog v-model="deleteDialog" max-width="480">
      <v-card>
        <v-card-title>Supprimer le template</v-card-title>
        <v-card-text>
          Confirmer la suppression de
          <strong>{{ templateToDelete?.name }}</strong> ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="actionLoading" @click="confirmDelete">Supprimer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { api } from '../../../api'
import { useRouter } from 'vue-router'

const router = useRouter()

const templates = ref([])
const loading = ref(true)
const search = ref('')
const snackbar = ref({ show: false, message: '', color: 'success' })

const deleteDialog = ref(false)
const templateToDelete = ref(null)
const actionLoading = ref(false)

const headers = [
  { title: 'Nom', key: 'name', sortable: true },
  { title: 'Type', key: 'type', sortable: true },
  { title: 'Statut', key: 'status', sortable: true },
  { title: 'Objets', key: 'objects_count', sortable: true },
  { title: 'Modifié le', key: 'updated_at', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

const statusConfig = {
  draft: { label: 'Brouillon', color: 'grey' },
  published: { label: 'Publié', color: 'success' },
  archived: { label: 'Archivé', color: 'warning' },
}

const filteredTemplates = computed(() => {
  if (!search.value) return templates.value
  const q = search.value.toLowerCase()
  return templates.value.filter(t => t.name.toLowerCase().includes(q))
})

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

function formatDate(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('fr-BE', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

async function loadTemplates() {
  loading.value = true
  try {
    const { data } = await api.get('/admin/templates')
    templates.value = data
  } catch (e) {
    showError('Erreur lors du chargement des templates.')
  } finally {
    loading.value = false
  }
}

function goCreate() { router.push('/admin/templates/create') }
function goEdit(id) { router.push(`/admin/templates/${id}/edit`) }

function askDelete(template) {
  templateToDelete.value = template
  deleteDialog.value = true
}

async function confirmDelete() {
  if (!templateToDelete.value) return
  actionLoading.value = true
  try {
    await api.delete(`/admin/templates/${templateToDelete.value.id}`)
    showSuccess('Template supprimé.')
    deleteDialog.value = false
    await loadTemplates()
  } catch (e) {
    if (e.response?.status === 409) {
      showError(e.response.data.message)
    } else {
      showError('Erreur lors de la suppression.')
    }
  } finally {
    actionLoading.value = false
  }
}

async function changeStatus(template, status) {
  try {
    await api.patch(`/admin/templates/${template.id}/status`, { status })
    showSuccess('Statut mis à jour.')
    await loadTemplates()
  } catch (e) {
    showError('Erreur lors du changement de statut.')
  }
}

onMounted(loadTemplates)
</script>