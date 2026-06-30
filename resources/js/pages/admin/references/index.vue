<template>
  <v-container>
    <div class="d-flex align-center ga-3 mb-6">
      <v-btn icon="mdi-arrow-left" variant="text" @click="$router.back()" />
      <h1 class="text-h4">Référentiels</h1>
    </div>

    <v-tabs v-model="activeTab" class="mb-4">
      <v-tab value="types">Types de jeu</v-tab>
      <v-tab value="formats">Formats de jeu</v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <v-window-item value="types">
        <ReferenceList entity-label="type" entity-label-plural="types" endpoint="/admin/types" @snackbar="onSnackbar" />
      </v-window-item>

      <v-window-item value="formats">
        <ReferenceList entity-label="format" entity-label-plural="formats" endpoint="/admin/formats"
          @snackbar="onSnackbar" />
      </v-window-item>
    </v-window>

    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
    </v-snackbar>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import ReferenceList from '../../../components/admin/references/ReferenceList.vue'

const activeTab = ref('types')
const snackbar = ref({ show: false, message: '', color: 'success' })

function onSnackbar({ message, color }) {
  snackbar.value = { show: true, message, color }
}
</script>