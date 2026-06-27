<template>
  <v-expansion-panels v-model="openPanels" class="mt-6">
    <v-expansion-panel value="comments">
      <v-expansion-panel-title>
        Commentaires
        <template #actions>
          <v-chip size="small" class="mr-2">{{ comments.length }}</v-chip>
        </template>
      </v-expansion-panel-title>

      <v-expansion-panel-text>
        <v-card v-if="!loading" class="pa-3 mb-4" variant="tonal">
          <v-textarea v-model="draft" :label="myComment ? 'Modifier mon commentaire' : 'Ajouter un commentaire'"
            variant="outlined" rows="3" maxlength="1000" counter hide-details="auto" class="mb-2"
            :error-messages="errors.content" />
          <div class="d-flex justify-end ga-2">
            <v-btn v-if="myComment" variant="text" color="error" size="small" @click="askDelete">
              Supprimer
            </v-btn>
            <v-btn color="primary" :loading="saving" :disabled="!draft.trim()" @click="submit">
              {{ myComment ? 'Mettre à jour' : 'Publier' }}
            </v-btn>
          </div>
        </v-card>

        <div v-if="loading" class="d-flex justify-center pa-4">
          <v-progress-circular indeterminate size="24" color="primary" />
        </div>

        <p v-else-if="otherComments.length === 0" class="text-medium-emphasis text-body-2 text-center pa-4">
          Aucun commentaire pour le moment.
        </p>

        <div v-else>
          <v-card v-for="c in otherComments" :key="c.id" variant="outlined" class="pa-3 mb-2">
            <div class="d-flex align-center justify-space-between mb-1">
              <strong>{{ c.user.username }}</strong>
              <div class="d-flex align-center ga-2">
                <span class="text-caption text-medium-emphasis">{{ formatDate(c.created_at) }}</span>
                <v-btn v-if="c.user_id !== currentUserId" icon="mdi-flag-outline" variant="text" size="x-small"
                  color="error" @click="openReport(c.id)" />
                <v-btn v-if="isAdmin && c.user_id !== currentUserId" icon="mdi-delete" variant="text" size="x-small"
                  color="error" @click="askAdminDelete(c)" />
              </div>
            </div>
            <p class="text-body-2 mb-0" style="white-space: pre-wrap">{{ c.content }}</p>
          </v-card>
        </div>
      </v-expansion-panel-text>
      <ReportModal v-if="reportTargetId" v-model="reportOpen" reportable-type="comment" :reportable-id="reportTargetId"
        @reported="emit('success', 'Signalement envoyé. Merci, un administrateur examinera votre demande.')" />
        <v-dialog v-model="adminDeleteDialog" max-width="440">
        <v-card>
          <v-card-title>Supprimer le commentaire</v-card-title>
          <v-card-text>
            Supprimer le commentaire de <strong>{{ commentToDelete?.user?.username }}</strong> ?
            Cette action est définitive.
          </v-card-text>
          <v-card-actions>
            <v-spacer />
            <v-btn variant="text" @click="adminDeleteDialog = false">Annuler</v-btn>
            <v-btn color="error" :loading="adminDeleting" @click="confirmAdminDelete">Supprimer</v-btn>
          </v-card-actions>
        </v-card>
      </v-dialog>
    </v-expansion-panel>

    <v-dialog v-model="deleteDialog" max-width="440">
      <v-card>
        <v-card-title>Supprimer le commentaire</v-card-title>
        <v-card-text>Cette action est irréversible.</v-card-text>
        <v-card-actions>
          <v-spacer />
          <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="deleting" @click="confirmDelete">Supprimer</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-expansion-panels>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { api } from '../../api'
import ReportModal from './ReportModal.vue'

const props = defineProps({
  templateId: { type: [Number, String], default: null },
  projectId: { type: [Number, String], default: null },
  currentUserId: { type: Number, required: true },
  isAdmin: { type: Boolean, default: false },
})

const emit = defineEmits(['error', 'success'])

const openPanels = ref(['comments'])
const comments = ref([])
const loading = ref(true)
const saving = ref(false)
const deleting = ref(false)
const errors = ref({})
const draft = ref('')
const deleteDialog = ref(false)

const reportOpen = ref(false)
const reportTargetId = ref(null)

const adminDeleteDialog = ref(false)
const commentToDelete = ref(null)
const adminDeleting = ref(false)

const myComment = computed(() => comments.value.find(c => c.user_id === props.currentUserId))
const otherComments = computed(() => comments.value)

watch(myComment, (val) => {
  draft.value = val?.content ?? ''
}, { immediate: true })

const listUrl = computed(() => {
  if (props.templateId) return `/templates/${props.templateId}/comments`
  if (props.projectId) return `/projects/${props.projectId}/comments`
  return null
})

async function loadComments() {
  if (!listUrl.value) return
  loading.value = true
  try {
    const { data } = await api.get(listUrl.value)
    comments.value = data
  } catch {
    emit('error', 'Erreur lors du chargement des commentaires.')
  } finally {
    loading.value = false
  }
}

async function submit() {
  if (!draft.value.trim()) return
  errors.value = {}
  saving.value = true
  try {
    const payload = { content: draft.value.trim() }
    if (props.templateId) payload.template_id = props.templateId
    if (props.projectId) payload.project_id = props.projectId

    await api.post('/comments', payload)
    emit('success', myComment.value ? 'Commentaire mis à jour.' : 'Commentaire publié.')
    await loadComments()
  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      emit('error', 'Erreur lors de l\'enregistrement.')
    }
  } finally {
    saving.value = false
  }
}
function openReport(commentId) {
  reportTargetId.value = commentId
  reportOpen.value = true
}

function askDelete() {
  deleteDialog.value = true
}
async function confirmDelete() {
  if (!myComment.value) return
  deleting.value = true
  try {
    await api.delete(`/comments/${myComment.value.id}`)
    emit('success', 'Commentaire supprimé.')
    deleteDialog.value = false
    draft.value = ''
    await loadComments()
  } catch {
    emit('error', 'Erreur lors de la suppression.')
  } finally {
    deleting.value = false
  }
}
function askAdminDelete(comment) {
  commentToDelete.value = comment
  adminDeleteDialog.value = true
}
async function confirmAdminDelete() {
  if (!commentToDelete.value) return
  adminDeleting.value = true
  try {
    await api.delete(`/comments/${commentToDelete.value.id}`)
    emit('success', 'Commentaire supprimé.')
    adminDeleteDialog.value = false
    commentToDelete.value = null
    await loadComments()
  } catch {
    emit('error', 'Erreur lors de la suppression.')
  } finally {
    adminDeleting.value = false
  }
}

function formatDate(date) {
  if (!date) return ''
  const d = new Date(date)
  return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(loadComments)
</script>