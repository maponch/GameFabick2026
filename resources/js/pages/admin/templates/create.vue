<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="goBack" />
      <h1 class="text-h4">Nouveau template</h1>
    </div>

    <v-form ref="formRef" @submit.prevent="submit">
      <v-card class="pa-4 mb-4">
        <v-card-title class="px-0">Informations générales</v-card-title>

        <v-text-field v-model="form.name" label="Nom du jeu *" variant="outlined" :rules="[rules.required]"
          :error-messages="errors.name" class="mb-2" />

        <v-textarea v-model="form.description" label="Description" variant="outlined" rows="2"
          :error-messages="errors.description" class="mb-2" />

        <v-textarea v-model="form.rules" label="Règles" variant="outlined" rows="4" :error-messages="errors.rules"
          class="mb-2" />

        <v-select v-model="form.type_id" :items="types" item-title="name" item-value="id" label="Type de jeu *"
          variant="outlined" :loading="typesLoading" :rules="[rules.required]" :error-messages="errors.type_id"
          class="mb-2" />

        <v-row>
          <v-col cols="6">
            <v-text-field v-model.number="form.min_players" type="number" label="Joueurs min *" variant="outlined"
              :rules="[rules.required, rules.positive]" :error-messages="errors.min_players" />
          </v-col>
          <v-col cols="6">
            <v-text-field v-model.number="form.max_players" type="number" label="Joueurs max *" variant="outlined"
              :rules="[rules.required, rules.positive, rules.maxGteMin]" :error-messages="errors.max_players" />
          </v-col>
        </v-row>

        <v-row>
          <v-col cols="6">
            <v-text-field v-model.number="form.duration_min" type="number" label="Durée min (min) *" variant="outlined"
              :rules="[rules.required, rules.positive]" :error-messages="errors.duration_min" />
          </v-col>
          <v-col cols="6">
            <v-text-field v-model.number="form.duration_max" type="number" label="Durée max (min) *" variant="outlined"
              :rules="[rules.required, rules.positive, rules.durMaxGteMin]" :error-messages="errors.duration_max" />
          </v-col>
        </v-row>

        <v-switch v-model="form.supports_existing_deck" label="Supporte le mode jeu de cartes existant (pense-bête)"
          color="primary" />

        <v-select v-model="form.status" :items="statusItems" label="Statut *" variant="outlined"
          :error-messages="errors.status" />
      </v-card>

      <v-card class="pa-4 mb-4">
        <div class="d-flex align-center justify-space-between mb-2">
          <v-card-title class="px-0">Champs de carte personnalisés</v-card-title>
          <v-btn size="small" prepend-icon="mdi-plus" @click="addField">Ajouter un champ</v-btn>
        </div>

        <p v-if="form.card_schema.length === 0" class="text-medium-emphasis text-body-2">
          Aucun champ personnalisé. Les cartes auront les champs de base (nom, description, image).
        </p>

        <v-card v-for="(field, i) in form.card_schema" :key="field._uid" variant="tonal" class="pa-3 mb-3">
          <v-row dense>
            <v-col cols="12" sm="4">
              <v-text-field v-model="field.label" label="Libellé *" variant="outlined" density="compact"
                :error-messages="fieldErrors(i, 'label')" @update:model-value="onLabelChange(field)" />
            </v-col>
            <v-col cols="12" sm="3">
              <v-select v-model="field.type" :items="fieldTypes" label="Type" variant="outlined" density="compact" />
            </v-col>
            <v-col cols="12" sm="3" class="d-flex align-center">
              <v-switch v-model="field.required" label="Requis" color="primary" density="compact" hide-details />
            </v-col>
            <v-col cols="12" sm="2" class="d-flex align-center justify-end">
              <v-btn icon="mdi-delete" size="small" variant="text" color="error" @click="removeField(i)" />
            </v-col>
          </v-row>

          <div class="text-caption text-medium-emphasis mb-2">
            Clé technique : <code>{{ field.key || '(généré depuis le libellé)' }}</code>
          </div>

          <v-combobox v-if="field.type === 'select'" v-model="field.options" label="Options (Entrée pour ajouter) *"
            variant="outlined" density="compact" multiple chips closable-chips
            :error-messages="fieldErrors(i, 'options')" />
        </v-card>
      </v-card>

      <div class="d-flex ga-2">
        <v-btn variant="text" @click="goBack">Annuler</v-btn>
        <v-spacer />
        <v-btn color="primary" type="submit" :loading="saving">Créer le template</v-btn>
      </div>
    </v-form>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { api } from '../../../api'
import { useRouter } from 'vue-router'

const router = useRouter()

const formRef = ref(null)
const types = ref([])
const typesLoading = ref(true)
const saving = ref(false)
const errors = ref({})
const snackbar = ref({ show: false, message: '', color: 'success' })

let uidCounter = 0

const statusItems = [
  { title: 'Brouillon', value: 'draft' },
  { title: 'Publié', value: 'published' },
  { title: 'Archivé', value: 'archived' },
]

const fieldTypes = [
  { title: 'Texte court', value: 'text' },
  { title: 'Texte long', value: 'textarea' },
  { title: 'Nombre', value: 'number' },
  { title: 'Liste déroulante', value: 'select' },
  { title: 'Oui/Non', value: 'boolean' },
]

const rules = {
  required: v => (v !== null && v !== undefined && v !== '') || 'Champ requis',
  positive: v => (v > 0) || 'Doit être supérieur à 0',
  maxGteMin: v => (v >= form.value.min_players) || 'Doit être ≥ joueurs min',
  durMaxGteMin: v => (v >= form.value.duration_min) || 'Doit être ≥ durée min',
}

const form = ref({
  name: '',
  description: '',
  rules: '',
  type_id: null,
  min_players: 1,
  max_players: 4,
  duration_min: 15,
  duration_max: 30,
  supports_existing_deck: false,
  status: 'draft',
  card_schema: [],
})

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

function fieldErrors(index, prop) {
  return errors.value[`card_schema.${index}.${prop}`] ?? []
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
}

function addField() {
  form.value.card_schema.push({
    _uid: ++uidCounter,
    label: '',
    key: '',
    type: 'text',
    required: false,
    options: [],
  })
}

function removeField(i) {
  form.value.card_schema.splice(i, 1)
}

function validateSchema() {
  const keys = []
  for (const f of form.value.card_schema) {
    if (!f.label?.trim()) return 'Chaque champ de carte doit avoir un libellé.'
    if (!f.key) return `Le champ "${f.label}" produit une clé technique vide. Utilisez des lettres.`
    if (keys.includes(f.key)) return `La clé "${f.key}" est dupliquée entre deux champs.`
    keys.push(f.key)
    if (f.type === 'select' && (!f.options || f.options.length === 0)) {
      return `Le champ "${f.label}" (liste déroulante) doit avoir au moins une option.`
    }
  }
  return null
}

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

function cleanSchema() {
  return form.value.card_schema.map(f => {
    const out = { key: f.key, label: f.label.trim(), type: f.type, required: !!f.required }
    if (f.type === 'select') out.options = f.options ?? []
    return out
  })
}

async function submit() {
  errors.value = {}

  const { valid } = await formRef.value.validate()
  if (!valid) {
    showError('Veuillez corriger les champs en rouge.')
    return
  }

  const schemaError = validateSchema()
  if (schemaError) {
    showError(schemaError)
    return
  }

  saving.value = true
  try {
    const payload = { ...form.value, card_schema: cleanSchema() }
    const { data } = await api.post('/admin/templates', payload)
    showSuccess('Template créé avec succès.')
    router.push('/admin/templates')
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
      showError('Le serveur a rejeté certaines valeurs.')
    } else if (e.response?.status === 403) {
      showError('Action non autorisée.')
    } else {
      showError('Erreur lors de la création.')
    }
  } finally {
    saving.value = false
  }
}

function goBack() { router.push('/admin/templates') }

onMounted(loadTypes)
</script>