<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="$router.push('/admin')" />
      <h1 class="text-h4">Signalements</h1>
    </div>

    <p class="text-medium-emphasis mb-4">
      Signalements émis par les utilisateurs. Examiner chaque cas et décider de l'action à mener directement sur la
      cible.
    </p>

    <v-card class="pa-4 mb-4">
      <div class="d-flex ga-3 flex-wrap">
        <v-select v-model="filters.status" :items="statusOptions" label="Statut" variant="outlined" density="compact"
          hide-details clearable style="max-width: 220px;" @update:model-value="loadReports" />
        <v-select v-model="filters.reportable_type" :items="typeOptions" label="Type de cible" variant="outlined"
          density="compact" hide-details clearable style="max-width: 220px;" @update:model-value="loadReports" />
      </div>
    </v-card>

    <v-data-table :headers="headers" :items="reports" :loading="loading" :no-data-text="emptyMessage"
      loading-text="Chargement…" :items-per-page="25" :items-per-page-options="[10, 25, 50, 100]">
      <template #item.status="{ item }">
        <v-chip size="small" :color="statusColor(item.status)" label>
          {{ statusLabel(item.status) }}
        </v-chip>
      </template>

      <template #item.reportable_type="{ item }">
        <v-chip size="small" variant="tonal">{{ typeLabel(item.reportable_type) }}</v-chip>
      </template>

      <template #item.target="{ item }">
        <component :is="targetLink(item) ? 'RouterLink' : 'span'" :to="targetLink(item)" class="text-decoration-none">
          {{ targetName(item) }}
        </component>
      </template>

      <template #item.reporter="{ item }">
        <span class="text-body-2">{{ item.reporter?.username ?? '—' }}</span>
      </template>

      <template #item.reason_code="{ item }">
        <span class="text-body-2">{{ reasonLabel(item.reason_code) }}</span>
      </template>

      <template #item.created_at="{ item }">
        <span class="text-body-2">{{ formatDate(item.created_at) }}</span>
      </template>

      <template #item.actions="{ item }">
        <v-btn icon="mdi-eye" size="small" variant="text" @click="openDetail(item)" />
      </template>
    </v-data-table>

    <v-dialog v-model="modal" max-width="640" persistent>
      <v-card v-if="selected">
        <v-card-title>Signalement #{{ selected.id }}</v-card-title>
        <v-card-text>
          <div v-if="selected.reportable_type === 'App\\Models\\Comment' && selected.reportable" class="mb-3">
            <div class="text-caption text-medium-emphasis">Contenu du commentaire</div>
            <v-card variant="tonal" class="pa-3 mt-1">
              <p class="text-body-2 mb-2" style="white-space: pre-wrap">{{ selected.reportable.content }}</p>
              <div class="text-caption text-medium-emphasis">
                par {{ selected.reportable.user?.username ?? '—' }}
              </div>
            </v-card>
          </div>
          <div class="mb-3">
            <div class="text-caption text-medium-emphasis">Cible</div>
            <div class="d-flex align-center ga-2">
              <v-chip size="small" variant="tonal">{{ typeLabel(selected.reportable_type) }}</v-chip>
              <component :is="targetLink(selected) ? 'RouterLink' : 'span'" :to="targetLink(selected)"
                class="text-decoration-none font-weight-medium">
                {{ targetName(selected) }}
              </component>
            </div>
          </div>

          <div class="mb-3">
            <div class="text-caption text-medium-emphasis">Signalé par</div>
            <div>{{ selected.reporter?.username ?? '—' }} ({{ selected.reporter?.email ?? '—' }})</div>
          </div>

          <div class="mb-3">
            <div class="text-caption text-medium-emphasis">Motif</div>
            <div>{{ reasonLabel(selected.reason_code) }}</div>
          </div>

          <div v-if="selected.reason_text" class="mb-3">
            <div class="text-caption text-medium-emphasis">Précisions du signalant</div>
            <p class="text-body-2 mb-0" style="white-space: pre-wrap">{{ selected.reason_text }}</p>
          </div>

          <v-divider class="my-3" />

          <div v-if="relatedPending.length > 0" class="mb-3">
            <div class="text-caption text-medium-emphasis">
              Autres signalements en attente sur la même cible ({{ relatedPending.length }})
            </div>
            <v-list density="compact">
              <v-list-item v-for="r in relatedPending" :key="r.id"
                :title="`${reasonLabel(r.reason_code)} — ${r.reporter?.username ?? '—'}`"
                :subtitle="formatDate(r.created_at)" />
            </v-list>
          </div>

          <template v-if="selected.status === 'pending'">
            <v-textarea v-model="adminNote" label="Note interne (facultatif)" variant="outlined" rows="2"
              maxlength="2000" counter />
          </template>

          <v-alert v-else type="info" variant="tonal" density="compact" class="mt-3">
            Signalement déjà traité par
            <strong>{{ selected.reviewer?.username ?? '—' }}</strong>
            le {{ formatDate(selected.reviewed_at) }}.
            <div v-if="selected.admin_note" class="mt-2 text-body-2" style="white-space: pre-wrap">
              {{ selected.admin_note }}
            </div>
          </v-alert>
        </v-card-text>

        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" :disabled="submitting" @click="modal = false">Fermer</v-btn>
          <template v-if="selected.status === 'pending'">
            <v-btn color="grey" variant="text" :loading="submitting && action === 'dismissed'" :disabled="submitting"
              @click="resolve('dismissed')">
              Rejeter
            </v-btn>
            <v-btn v-if="actionConfig" :color="actionConfig.color" variant="flat"
              :loading="submitting && action === 'reviewed'" :disabled="submitting" @click="resolve('reviewed')">
              {{ actionConfig.label }}
            </v-btn>
          </template>
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

const reports = ref([])
const loading = ref(true)
const snackbar = ref({ show: false, message: '', color: 'success' })

const filters = ref({ status: 'pending', reportable_type: null })

const modal = ref(false)
const selected = ref(null)
const relatedPending = ref([])
const adminNote = ref('')
const submitting = ref(false)
const action = ref(null)

const statusOptions = [
  { title: 'En attente', value: 'pending' },
  { title: 'Traité', value: 'reviewed' },
  { title: 'Rejeté', value: 'dismissed' },
]

const typeOptions = [
  { title: 'Jeu (projet)', value: 'project' },
  { title: 'Commentaire', value: 'comment' },
  { title: 'Modèle (template)', value: 'template' },
]

const REASON_LABELS = {
  spam: 'Spam ou publicité',
  inappropriate: 'Contenu inapproprié',
  low_quality: 'Qualité insuffisante',
  copyright: 'Violation de droits d\'auteur',
  other: 'Autre',
}

const STATUS_LABELS = {
  pending: 'En attente',
  reviewed: 'Traité',
  dismissed: 'Rejeté',
}

const TYPE_LABELS = {
  'App\\Models\\Project': 'Jeu',
  'App\\Models\\Comment': 'Commentaire',
  'App\\Models\\GameTemplate': 'Modèle',
}

const headers = [
  { title: 'Statut', key: 'status', sortable: true, width: 110 },
  { title: 'Type', key: 'reportable_type', sortable: true, width: 130 },
  { title: 'Cible', key: 'target', sortable: false },
  { title: 'Signalé par', key: 'reporter', sortable: false },
  { title: 'Motif', key: 'reason_code', sortable: true, width: 200 },
  { title: 'Date', key: 'created_at', sortable: true, width: 150 },
  { title: 'Actions', key: 'actions', sortable: false, align: 'end', width: 80 },
]

const emptyMessage = computed(() =>
  reports.value.length === 0 ? 'Aucun signalement.' : 'Aucun résultat.'
)

function reasonLabel(code) { return REASON_LABELS[code] ?? code }
function statusLabel(s) { return STATUS_LABELS[s] ?? s }
function typeLabel(t) { return TYPE_LABELS[t] ?? t }

function statusColor(s) {
  return { pending: 'warning', reviewed: 'success', dismissed: 'grey' }[s] ?? 'grey'
}

const actionConfig = computed(() => {
  if (!selected.value?.reportable) return null
  const r = selected.value.reportable
  switch (selected.value.reportable_type) {
    case 'App\\Models\\Project':
      if (r.status !== 'published') {
        return { label: 'Jeu non publié — rejeter le signalement', color: 'grey', disabled: true }
      }
      return { label: 'Archiver ce jeu', color: 'warning', disabled: false }
    case 'App\\Models\\Comment':
      return { label: 'Supprimer ce commentaire', color: 'error', disabled: false }
    case 'App\\Models\\GameTemplate':
      if (r.status !== 'published') {
        return { label: 'Modèle non publié — rejeter le signalement', color: 'grey', disabled: true }
      }
      return { label: 'Archiver ce modèle', color: 'warning', disabled: false }
    default:
      return { label: 'Marquer traité', color: 'primary', disabled: false }
  }
})

function formatDate(date) {
  if (!date) return ''
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  })
}

function showSuccess(msg) { snackbar.value = { show: true, message: msg, color: 'success' } }
function showError(msg) { snackbar.value = { show: true, message: msg, color: 'error' } }

async function loadReports() {
  loading.value = true
  try {
    const params = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.reportable_type) params.reportable_type = filters.value.reportable_type
    params.per_page = 100

    const { data } = await api.get('/admin/reports', { params })
    reports.value = data.data ?? []
  } catch {
    showError('Erreur lors du chargement.')
  } finally {
    loading.value = false
  }
}

async function openDetail(item) {
  selected.value = item
  adminNote.value = ''
  relatedPending.value = []
  modal.value = true

  try {
    const { data } = await api.get(`/admin/reports/${item.id}`)
    selected.value = data.report
    relatedPending.value = data.related_pending ?? []
  } catch {
    showError('Erreur lors du chargement du détail.')
  }
}

async function resolve(status) {
  submitting.value = true
  action.value = status
  try {
    await api.patch(`/admin/reports/${selected.value.id}`, {
      status,
      admin_note: adminNote.value?.trim() || null,
    })
    if (status === 'dismissed') {
      showSuccess('Signalement rejeté.')
    } else {
      showSuccess(actionConfig.value?.label + ' — fait.')
    }
    modal.value = false
    await loadReports()
  } catch (e) {
    showError(e.response?.data?.message ?? 'Erreur lors de la mise à jour.')
  } finally {
    submitting.value = false
    action.value = null
  }
}
function targetLink(item) {
  const r = item.reportable
  if (!r) return null
  switch (item.reportable_type) {
    case 'App\\Models\\Project':
      return `/community/${r.id}`
    case 'App\\Models\\GameTemplate':
      return `/games/${r.slug ?? r.id}`
    case 'App\\Models\\Comment':
      if (r.project) return `/community/${r.project.id}`
      if (r.template) return `/games/${r.template.slug}`
      return null
    default:
      return null
  }
}
function targetName(item) {
  const r = item.reportable
  if (!r) return `#${item.reportable_id} (supprimé)`
  if (item.reportable_type === 'App\\Models\\Comment') {
    const parent = r.project?.name ?? r.template?.name ?? '?'
    return `Commentaire sur "${parent}"`
  }
  return r.name ?? `#${item.reportable_id}`
}

onMounted(loadReports)
</script>