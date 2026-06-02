<template>
  <div>
    <div class="d-flex align-center justify-space-between mb-4">
      <v-switch v-model="includeArchived" :label="`Afficher les ${entityLabelPlural} archivés`" color="primary"
        density="compact" hide-details @update:model-value="load" />
      <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreate">
        Nouveau {{ entityLabel }}
      </v-btn>
    </div>

    <v-data-table :headers="headers" :items="items" :loading="loading" :no-data-text="`Aucun ${entityLabel}.`"
      loading-text="Chargement…">
      <template #item.name="{ item }">
        <span :class="{ 'text-disabled': item.archived }">{{ item.name }}</span>
      </template>

      <template #item.archived="{ item }">
        <v-chip v-if="item.archived" color="warning" size="small" label>Archivé</v-chip>
        <v-chip v-else color="success" size="small" label>Actif</v-chip>
      </template>

      <template #item.usage_count="{ item }">
        {{ item.usage_count }} template(s)
      </template>

      <template #item.actions="{ item }">
        <template v-if="!item.archived">
          <v-btn icon="mdi-pencil" size="small" variant="text" @click="openEdit(item)" />
          <v-btn icon="mdi-archive" size="small" variant="text" color="warning" @click="askArchive(item)" />
        </template>
        <template v-else>
          <v-btn icon="mdi-restore" size="small" variant="text" color="success" @click="restore(item)" />
        </template>
      </template>
    </v-data-table>

    <ReferenceModal v-model="modalOpen" :entity="entityToEdit" :endpoint="endpoint" :entity-label="entityLabel"
      @saved="onSaved" />

    <v-dialog v-model="archiveDialog" max-width="440">
      <v-card>
        <v-card-title>Archiver le {{ entityLabel }}</v-card-title>
        <v-card-text>
          <p>Confirmer l'archivage de <strong>{{ itemToArchive?.name }}</strong> ?</p>
          <p v-if="itemToArchive?.usage_count > 0" class="text-warning text-body-2">
            Ce {{ entityLabel }} est utilisé par {{ itemToArchive.usage_count }} template(s).
            Ils garderont leur référence, mais ce {{ entityLabel }} ne sera plus proposé aux nouvelles créations.
          </p>
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="archiveDialog = false">Annuler</v-btn>
          <v-btn color="warning" :loading="archiving" @click="confirmArchive">Archiver</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../../api'
import ReferenceModal from './ReferenceModal.vue'

const props = defineProps({
  entityLabel: { type: String, required: true },
  entityLabelPlural: { type: String, required: true },
  endpoint: { type: String, required: true },
})

const emit = defineEmits(['snackbar'])

const items = ref([])
const loading = ref(true)
const includeArchived = ref(false)

const modalOpen = ref(false)
const entityToEdit = ref(null)

const archiveDialog = ref(false)
const itemToArchive = ref(null)
const archiving = ref(false)

const headers = [
  { title: 'Nom', key: 'name', sortable: true },
  { title: 'Statut', key: 'archived', sortable: true },
  { title: 'Utilisation', key: 'usage_count', sortable: true },
  { title: 'Actions', key: 'actions', sortable: false },
]

function showSuccess(message) { emit('snackbar', { message, color: 'success' }) }
function showError(message) { emit('snackbar', { message, color: 'error' }) }

async function load() {
  loading.value = true
  try {
    const params = includeArchived.value ? { include_archived: 1 } : {}
    const { data } = await api.get(props.endpoint, { params })
    items.value = data
  } catch (e) {
    showError('Erreur lors du chargement.')
  } finally {
    loading.value = false
  }
}

function openCreate() {
  entityToEdit.value = null
  modalOpen.value = true
}

function openEdit(item) {
  entityToEdit.value = item
  modalOpen.value = true
}

function onSaved() {
  showSuccess('Enregistré.')
  load()
}

function askArchive(item) {
  itemToArchive.value = item
  archiveDialog.value = true
}

async function confirmArchive() {
  if (!itemToArchive.value) return
  archiving.value = true
  try {
    await api.delete(`${props.endpoint}/${itemToArchive.value.id}`)
    showSuccess('Archivé.')
    archiveDialog.value = false
    load()
  } catch (e) {
    showError('Erreur lors de l\'archivage.')
  } finally {
    archiving.value = false
  }
}

async function restore(item) {
  try {
    await api.post(`${props.endpoint}/${item.id}/restore`)
    showSuccess('Restauré.')
    load()
  } catch (e) {
    showError('Erreur lors de la restauration.')
  }
}

onMounted(load)
</script>