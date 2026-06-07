<template>
  <v-container>

    <v-btn variant="text" prepend-icon="mdi-arrow-left" to="/games" class="mb-4">
      Retour à la bibliothèque
    </v-btn>

    <div v-if="loading" class="d-flex justify-center mt-10">
      <v-progress-circular indeterminate color="primary" />
    </div>

    <div v-else-if="project">

      <div class="d-flex align-center justify-space-between mb-2 flex-wrap ga-2">
        <h1 class="text-h4">{{ project.name }}</h1>
        <v-chip :color="project.mode === 'printable' ? 'primary' : 'success'" prepend-icon="mdi-cards">
          {{ project.mode === 'printable' ? 'Mode impression' : 'Jeu de cartes classique' }}
        </v-chip>
      </div>

      <p class="text-body-2 text-medium-emphasis mb-6">
        Basé sur <strong>{{ project.template?.name }}</strong>
      </p>

      <v-tabs v-model="tab" class="mb-6">
        <v-tab value="cards">
          <v-icon class="me-2">mdi-cards</v-icon>
          {{ project.mode === 'printable' ? 'Cartes à imprimer' : 'Pense-bête' }}
        </v-tab>
        <v-tab value="rules">
          <v-icon class="me-2">mdi-book-open</v-icon>
          Règles du jeu
        </v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <!-- ONGLET CARTES -->
        <v-window-item value="cards">

          <!-- Mode impression -->
          <div v-if="project.mode === 'printable'">
            <v-row>
              <v-col v-for="object in project.objects" :key="object.id" cols="6" sm="4" md="3">
                <v-card v-for="n in object.quantity" :key="`${object.id}-${n}`" class="mb-3 d-flex flex-column"
                  :color="object.default_color" height="220">
                  <v-card-title class="text-center text-white pb-1">
                    {{ object.custom_text || object.name }}
                  </v-card-title>

                  <v-card-text
                    class="text-center text-white flex-grow-1 d-flex flex-column align-center justify-center">
                    <p v-if="object.description" class="text-caption mb-2">
                      {{ object.description }}
                    </p>

                    <div v-if="objectCustomFields(object).length > 0" class="custom-fields w-100">
                      <div v-for="field in objectCustomFields(object)" :key="field.key" class="custom-field">
                        <span class="custom-field-label">{{ field.label }} :</span>
                        <span class="custom-field-value">{{ formatFieldValue(field, object.custom_data?.[field.key])
                          }}</span>
                      </div>
                    </div>
                  </v-card-text>
                </v-card>
              </v-col>
            </v-row>
          </div>

          <!-- Mode jeu existant -->
          <div v-else>
            <v-card class="pa-6">
              <h2 class="text-h6 mb-4">Correspondance des cartes</h2>
              <p class="text-body-2 text-medium-emphasis mb-4">
                Distribuez ces cartes de votre jeu classique en fonction de leur rôle :
              </p>

              <v-table>
                <thead>
                  <tr>
                    <th>Carte du jeu</th>
                    <th>Rôle</th>
                    <th>Quantité</th>
                    <th>Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="object in project.objects" :key="object.id">
                    <td>
                      <v-chip v-for="mapping in object.existing_deck_mapping" :key="mapping"
                        :color="cardColor(mapping) === 'red' ? 'red' : 'white'"
                        variant="outlined" size="default" class="me-1">
                        {{ cardShortLabel(mapping) }}
                      </v-chip>
                    </td>
                    <td><strong>{{ object.name }}</strong></td>
                    <td>×{{ object.quantity }}</td>
                    <td class="text-caption">{{ object.description }}</td>
                  </tr>
                </tbody>
              </v-table>
            </v-card>
          </div>

        </v-window-item>

        <!-- ONGLET RÈGLES -->
        <v-window-item value="rules">
          <v-card class="pa-6">
            <div class="text-body-1" style="white-space: pre-line">{{ project.template?.rules }}</div>
          </v-card>
        </v-window-item>

      </v-window>

      <!-- Actions -->
      <v-card class="pa-4 mt-6">
        <h3 class="text-h6 mb-3">Actions</h3>
        <div class="d-flex flex-wrap ga-2">
          <v-btn color="primary" variant="tonal" prepend-icon="mdi-printer" :loading="generatingPdf"
            @click="generatePdf">
            Télécharger le PDF
          </v-btn>
          <v-btn color="error" variant="tonal" prepend-icon="mdi-delete" @click="deleteDialog = true">
            Supprimer
          </v-btn>
        </div>
      </v-card>

      <div style="position: fixed; left: -9999px; top: 0;">
        <PrintableCards v-if="project.mode === 'printable'" ref="printableCardsRef" :project="project" />
        <PrintableCheatsheet v-if="project.mode === 'existing_deck'" ref="printableCheatsheetRef" :project="project" />
      </div>

    </div>

    <!-- Dialog suppression -->
    <v-dialog v-model="deleteDialog" max-width="400">
      <v-card class="pa-4">
        <v-card-title>Supprimer le projet</v-card-title>
        <v-card-text>
          Voulez-vous vraiment supprimer <strong>{{ project?.name }}</strong> ?
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions class="justify-end">
          <v-btn variant="text" @click="deleteDialog = false">Annuler</v-btn>
          <v-btn color="error" :loading="deleting" @click="deleteProject">
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
import html2pdf from 'html2pdf.js'
import PrintableCards from '../../components/projects/PrintableCards.vue'
import PrintableCheatsheet from '../../components/projects/PrintableCheatsheet.vue'
import { cardShortLabel, cardColor } from '../../constants/classicDeck' 

const router = useRouter()
const route = useRoute()

const project = ref(null)
const loading = ref(true)
const tab = ref('cards')
const deleteDialog = ref(false)
const deleting = ref(false)
const generatingPdf = ref(false)
const snackbar = ref({ show: false, message: '', color: 'success' })

const printableCardsRef = ref(null)
const printableCheatsheetRef = ref(null)

async function loadProject() {
  try {
    const { data } = await api.get(`/projects/${route.params.id}`)
    project.value = data
  } catch {
    router.push('/games')
  } finally {
    loading.value = false
  }
}
function objectCustomFields(object) {
  const schema = project.value?.template?.card_schema ?? []
  return schema.filter(field => {
    const val = object.custom_data?.[field.key]
    return val !== null && val !== undefined && val !== ''
  })
}

function formatFieldValue(field, value) {
  if (field.type === 'boolean') return value ? 'Oui' : 'Non'
  return value
}

async function deleteProject() {
  deleting.value = true
  try {
    await api.delete(`/projects/${project.value.id}`)
    snackbar.value = { show: true, message: 'Projet supprimé.', color: 'success' }
    setTimeout(() => router.push('/games'), 1000)
  } catch {
    snackbar.value = { show: true, message: 'Erreur lors de la suppression.', color: 'error' }
  } finally {
    deleting.value = false
    deleteDialog.value = false
  }
}

async function generatePdf() {
  generatingPdf.value = true
  try {
    const element = project.value.mode === 'printable'
      ? printableCardsRef.value.printArea
      : printableCheatsheetRef.value.printArea

    const options = {
      margin: 0,
      filename: `${project.value.name.replace(/[^a-z0-9]/gi, '_')}.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: {
        mode: ['css'],        // ← enlever 'legacy' qui cause des pages blanches
        after: '.page'        // ← cible explicite
      }
    }

    await html2pdf().set(options).from(element).save()
    snackbar.value = { show: true, message: 'PDF généré.', color: 'success' }
  } catch (e) {
    console.error(e)
    snackbar.value = { show: true, message: 'Erreur lors de la génération.', color: 'error' }
  } finally {
    generatingPdf.value = false
  }
}

onMounted(loadProject)
</script>
<style scoped>
.custom-fields {
  border-top: 1px solid rgba(255, 255, 255, 0.3);
  padding-top: 6px;
  margin-top: 4px;
  font-size: 0.75rem;
  text-align: left;
}

.custom-field {
  margin-bottom: 2px;
  display: flex;
  justify-content: space-between;
  gap: 6px;
}

.custom-field-label {
  font-weight: 600;
  opacity: 0.85;
}

.custom-field-value {
  text-align: right;
}

.mapping-chip.red {
  color: #c62828 !important;
  border-color: #c62828 !important;
}

.mapping-chip.black {
  color: #000 !important;
  border-color: #000 !important;
}
</style>