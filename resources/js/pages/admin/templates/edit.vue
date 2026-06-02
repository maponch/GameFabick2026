<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" />
      <h1 class="text-h4">Édition du template</h1>
      <v-chip v-if="template" :color="statusConfig[template.status]?.color" label class="ml-2">
        {{ statusConfig[template.status]?.label ?? template.status }}
      </v-chip>
    </div>

    <div v-if="loading" class="d-flex justify-center pa-8">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <template v-else-if="template">
      <v-expansion-panels v-model="openPanels" multiple>
        <v-expansion-panel value="infos">
          <v-expansion-panel-title>Informations générales</v-expansion-panel-title>
          <v-expansion-panel-text>
            <TemplateForm ref="formRef" v-model="form" :types="types" :types-loading="typesLoading" :formats="formats"
              :formats-loading="formatsLoading" :server-errors="errors" />
            <div class="d-flex justify-end mt-2">
              <v-btn color="primary" :loading="savingInfos" @click="saveInfos">
                Enregistrer les informations
              </v-btn>
            </div>
          </v-expansion-panel-text>
        </v-expansion-panel>

        <v-expansion-panel value="cards">
          <v-expansion-panel-title>
            Cartes
            <template #actions>
              <v-chip size="small" class="mr-2">{{ objects.length }}</v-chip>
            </template>
          </v-expansion-panel-title>
          <v-expansion-panel-text>
            <div class="d-flex justify-end mb-3">
              <v-btn color="primary" prepend-icon="mdi-plus" @click="openCreateObject">
                Ajouter une carte
              </v-btn>
            </div>

            <p v-if="objects.length === 0" class="text-medium-emphasis text-body-2">
              Aucune carte pour l'instant. Ajoutez-en au moins une avant de publier le jeu.
            </p>

            <v-list v-else>
              <v-list-item v-for="obj in objects" :key="obj.id" class="mb-1">
                <template #prepend>
                  <div class="color-dot" :style="{ backgroundColor: obj.default_color }" />
                </template>

                <v-list-item-title>{{ obj.name }}</v-list-item-title>
                <v-list-item-subtitle>
                  Quantité : {{ obj.quantity }}
                  <span v-if="obj.description"> — {{ obj.description }}</span>
                </v-list-item-subtitle>

                <template #append>
                  <v-btn icon="mdi-pencil" size="small" variant="text" @click="openEditObject(obj)" />
                  <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="askDeleteObject(obj)" />
                </template>
              </v-list-item>
            </v-list>
          </v-expansion-panel-text>
        </v-expansion-panel>

        <v-expansion-panel value="schema">
          <v-expansion-panel-title>
            Champs personnalisés
            <template #actions>
              <v-chip size="small" class="mr-2">{{ cardSchema.length }}</v-chip>
            </template>
          </v-expansion-panel-title>
          <v-expansion-panel-text>
            <p class="text-medium-emphasis text-body-2 mb-3">
              Définissez des champs additionnels qui apparaîtront sur chaque carte
              (en plus du nom, de la description et de la couleur). Par exemple : un camp,
              une catégorie, une difficulté.
            </p>

            <div class="d-flex justify-end mb-3">
              <v-btn size="small" prepend-icon="mdi-plus" @click="addField">Ajouter un champ</v-btn>
            </div>

            <p v-if="cardSchema.length === 0" class="text-medium-emphasis text-body-2">
              Aucun champ personnalisé.
            </p>

            <v-card v-for="(field, i) in cardSchema" :key="field._uid" variant="tonal" class="pa-3 mb-3">
              <v-row dense>
                <v-col cols="12" sm="5">
                  <v-text-field v-model="field.label" label="Libellé *" variant="outlined" density="compact"
                    hide-details @update:model-value="onLabelChange(field)" />
                </v-col>
                <v-col cols="12" sm="4">
                  <v-select v-model="field.type" :items="fieldTypes" label="Type" variant="outlined" density="compact"
                    hide-details />
                </v-col>
                <v-col cols="12" sm="2" class="d-flex align-center">
                  <v-switch v-model="field.required" label="Requis" color="primary" density="compact" hide-details />
                </v-col>
                <v-col cols="12" sm="1" class="d-flex align-center justify-end">
                  <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="removeField(i)" />
                </v-col>
              </v-row>

              <v-combobox v-if="field.type === 'select'" v-model="field.options" label="Options (Entrée pour ajouter) *"
                variant="outlined" density="compact" multiple chips closable-chips class="mt-2" hide-details />
            </v-card>

            <v-alert v-if="schemaChanged" type="warning" variant="tonal" density="compact" class="mt-3 mb-2">
              Modifications non enregistrées. Cliquez sur « Enregistrer les champs » pour les sauvegarder.
            </v-alert>

            <div class="d-flex align-center justify-end mt-2 ga-2">
              <v-btn v-if="schemaChanged" variant="text" @click="resetSchema">
                Annuler les modifications
              </v-btn>
              <v-btn color="primary" :loading="savingSchema" :disabled="!schemaChanged" @click="saveSchema">
                Enregistrer les champs
              </v-btn>
            </div>
          </v-expansion-panel-text>
        </v-expansion-panel>
      </v-expansion-panels>
    </template>

    <ObjectModal v-model="objectModal" :template-id="templateId" :object="objectToEdit" :card-schema="savedSchema"
      :template-formats="selectedFormatSlugs" :existing-objects="objects" @saved="onObjectSaved" />

    <v-dialog v-model="deleteObjectDialog" max-width="440">
      <v-card>
        <v-card-title>Supprimer la carte</v-card-title>
        <v-card-text>
          Confirmer la suppression de <strong>{{ objectToDelete?.name }}</strong> ?
        </v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteObjectDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="deletingObject" @click="confirmDeleteObject">Supprimer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { api } from '../../../api'
import { useRouter, useRoute } from 'vue-router'
import TemplateForm from '../../../components/admin/templates/TemplateForm.vue'
import ObjectModal from '../../../components/admin/templates/ObjectModal.vue'

const router = useRouter()
const route = useRoute()
const templateId = route.params.id

const formRef = ref(null)
const template = ref(null)
const objects = ref([])
const types = ref([])
const typesLoading = ref(true)
const loading = ref(true)
const savingInfos = ref(false)
const errors = ref({})
const openPanels = ref(['infos'])
const snackbar = ref({ show: false, message: '', color: 'success' })

const formats = ref([])
const formatsLoading = ref(true)

const objectModal = ref(false)
const objectToEdit = ref(null)
const deleteObjectDialog = ref(false)
const objectToDelete = ref(null)
const deletingObject = ref(false)

const cardSchema = ref([])
const savingSchema = ref(false)
const schemaChanged = ref(false)
const schemaSnapshot = ref('[]')

let schemaUid = 0

const statusConfig = {
  draft: { label: 'Brouillon', color: 'grey' },
  published: { label: 'Publié', color: 'success' },
  archived: { label: 'Archivé', color: 'warning' },
}

const form = ref({
  name: '',
  description: '',
  rules: '',
  type_id: null,
  format_ids: [],
  min_players: 1,
  max_players: 4,
  duration_min: 15,
  duration_max: 30,
})

const fieldTypes = [
  { title: 'Texte court', value: 'text' },
  { title: 'Texte long', value: 'textarea' },
  { title: 'Nombre', value: 'number' },
  { title: 'Liste déroulante', value: 'select' },
  { title: 'Oui/Non', value: 'boolean' },
]
const savedSchema = computed(() => {
  try {
    return JSON.parse(schemaSnapshot.value)
  } catch {
    return []
  }
})
const selectedFormatSlugs = computed(() => {
  return formats.value
    .filter(f => form.value.format_ids.includes(f.id))
    .map(f => f.slug)
})

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

async function loadTypes() {
  typesLoading.value = true
  try {
    const { data } = await api.get('/admin/types')
    types.value = data
  } catch (e) {
    showError('Erreur lors du chargement des types.')
  } finally {
    typesLoading.value = false
  }
}
async function loadFormats() {
  formatsLoading.value = true
  try {
    const { data } = await api.get('/admin/formats')
    formats.value = data
  } catch (e) {
    showError('Erreur lors du chargement des formats.')
  } finally {
    formatsLoading.value = false
  }
}

async function loadTemplate() {
  loading.value = true
  try {
    const { data } = await api.get(`/admin/templates/${templateId}`)
    template.value = data
    objects.value = data.objects ?? []
    cardSchema.value = (data.card_schema ?? []).map(f => ({
      _uid: ++schemaUid,
      label: f.label ?? '',
      key: f.key ?? '',
      type: f.type ?? 'text',
      required: !!f.required,
      options: f.options ?? [],
    }))
    schemaSnapshot.value = JSON.stringify(cleanSchema())
    schemaChanged.value = false
    form.value = {
      name: data.name,
      description: data.description ?? '',
      rules: data.rules ?? '',
      type_id: data.type_id,
      format_ids: data.formats?.map(f => f.id) ?? [],
      min_players: data.min_players,
      max_players: data.max_players,
      duration_min: data.duration_min,
      duration_max: data.duration_max,
    }
  } catch (e) {
    if (e.response?.status === 404) {
      showError('Template introuvable.')
      router.push('/admin/templates')
    } else {
      showError('Erreur lors du chargement.')
    }
  } finally {
    loading.value = false
  }
}
function openCreateObject() {
  objectToEdit.value = null
  objectModal.value = true
}

function openEditObject(obj) {
  objectToEdit.value = obj
  objectModal.value = true
}

async function onObjectSaved() {
  showSuccess('Carte enregistrée.')
  await loadObjects()
}

async function loadObjects() {
  try {
    const { data } = await api.get(`/admin/templates/${templateId}/objects`)
    objects.value = data
  } catch (e) {
    showError('Erreur lors du chargement des cartes.')
  }
}

function askDeleteObject(obj) {
  objectToDelete.value = obj
  deleteObjectDialog.value = true
}

async function confirmDeleteObject() {
  if (!objectToDelete.value) return
  deletingObject.value = true
  try {
    await api.delete(`/admin/templates/${templateId}/objects/${objectToDelete.value.id}`)
    showSuccess('Carte supprimée.')
    deleteObjectDialog.value = false
    await loadObjects()
  } catch (e) {
    showError('Erreur lors de la suppression.')
  } finally {
    deletingObject.value = false
  }
}
function resetSchema() {
  const restored = JSON.parse(schemaSnapshot.value)
  cardSchema.value = restored.map(f => ({
    _uid: ++schemaUid,
    label: f.label,
    key: f.key,
    type: f.type,
    required: !!f.required,
    options: f.options ?? [],
  }))
  schemaChanged.value = false
}
function slugifyKey(label) {
  const s = (label || '')
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '')
  return /^[0-9]/.test(s) ? `f_${s}` : s
}

function onLabelChange(field) {
  field.key = slugifyKey(field.label)
  schemaChanged.value = true
}

function addField() {
  cardSchema.value.push({
    _uid: ++schemaUid,
    label: '',
    key: '',
    type: 'text',
    required: false,
    options: [],
  })
  schemaChanged.value = true
}

function removeField(i) {
  cardSchema.value.splice(i, 1)
  schemaChanged.value = true
}

function validateSchema() {
  const keys = []
  for (const f of cardSchema.value) {
    if (!f.label?.trim()) return 'Chaque champ doit avoir un libellé.'
    if (!f.key) return `Le champ "${f.label}" produit une clé vide. Utilisez des lettres.`
    if (keys.includes(f.key)) return `La clé "${f.key}" est dupliquée.`
    keys.push(f.key)
    if (f.type === 'select' && (!f.options || f.options.length === 0)) {
      return `Le champ "${f.label}" (liste déroulante) doit avoir au moins une option.`
    }
  }
  return null
}

function cleanSchema() {
  return cardSchema.value.map(f => {
    const out = { key: f.key, label: f.label.trim(), type: f.type, required: !!f.required }
    if (f.type === 'select') out.options = f.options ?? []
    return out
  })
}

async function saveSchema() {
  const err = validateSchema()
  if (err) {
    showError(err)
    return
  }
  savingSchema.value = true
  try {
    await api.patch(`/admin/templates/${templateId}`, { card_schema: cleanSchema() })
    showSuccess('Champs personnalisés enregistrés.')
    schemaSnapshot.value = JSON.stringify(cleanSchema())
    schemaChanged.value = false
  } catch (e) {
    if (e.response?.status === 422) {
      showError('Le serveur a rejeté le schéma.')
    } else {
      showError('Erreur lors de l\'enregistrement.')
    }
  } finally {
    savingSchema.value = false
  }
}

async function saveInfos() {
  errors.value = {}
  const valid = await formRef.value.validate()
  if (!valid) {
    showError('Veuillez corriger les champs en rouge.')
    return
  }

  savingInfos.value = true
  try {
    await api.patch(`/admin/templates/${templateId}`, form.value)
    showSuccess('Informations enregistrées.')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      showError('Le serveur a rejeté certaines valeurs.')
    } else {
      showError('Erreur lors de l\'enregistrement.')
    }
  } finally {
    savingInfos.value = false
  }
}

function goBack() { router.push('/admin/templates') }

onMounted(() => {
  loadTypes()
  loadFormats()
  loadTemplate()
})
</script>
<style scoped>
.color-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  border: 1px solid rgba(0, 0, 0, 0.2);
  margin-right: 12px;
}
</style>