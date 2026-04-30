<template>
  <v-dialog :model-value="modelValue" max-width="400" @update:model-value="$emit('update:modelValue', $event)">
    <v-card class="pa-4">
      <v-card-title>Changer la photo</v-card-title>
      <v-card-text>
        <div class="d-flex flex-column align-center mb-4">
          <v-avatar size="100" class="mb-3">
            <v-img :src="photoPreview ?? currentUrl" />
          </v-avatar>
          <v-btn variant="outlined" size="small" prepend-icon="mdi-upload" @click="$refs.photoInput.click()">
            Choisir une image
          </v-btn>
          <input ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp" style="display:none"
            @change="handlePhoto" />
        </div>
        <v-alert v-if="photoError" type="error" variant="tonal" density="compact">
          {{ photoError }}
        </v-alert>
      </v-card-text>
      <v-card-actions class="justify-end">
        <v-btn variant="text" @click="$emit('update:modelValue', false)">Annuler</v-btn>
        <v-btn color="primary" :loading="saving" :disabled="!photoFile" @click="save">
          Enregistrer
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { api } from '../../api'

const props = defineProps({
  modelValue: Boolean,
  currentUrl: String,
})

const emit = defineEmits(['update:modelValue', 'updated'])

const photoPreview = ref(null)
const photoFile = ref(null)
const photoError = ref(null)
const saving = ref(false)

// Réinitialise à chaque ouverture
watch(() => props.modelValue, (val) => {
  if (val) {
    photoPreview.value = null
    photoFile.value = null
    photoError.value = null
  }
})

function handlePhoto(e) {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 2 * 1024 * 1024) { photoError.value = 'La photo ne doit pas dépasser 2MB.'; return }
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { photoError.value = 'Format accepté : JPG, PNG, WEBP.'; return }
  photoError.value = null
  photoFile.value = file
  photoPreview.value = URL.createObjectURL(file)
}

async function save() {
  saving.value = true
  try {
    const formData = new FormData()
    formData.append('profile_photo', photoFile.value)
    const { data } = await api.post('/user/photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    emit('updated', data.user)
    emit('update:modelValue', false)
  } catch {
    photoError.value = 'Erreur lors de la mise à jour.'
  } finally {
    saving.value = false
  }
}
</script>