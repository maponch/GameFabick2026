<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Créer un compte 🎲
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            Rejoignez GameFabrick
          </v-card-subtitle>

          <!-- Erreur globale -->
          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
            @click:close="errorMessage = null">
            {{ errorMessage }}
          </v-alert>

          <!-- Succès -->
          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </v-alert>

          <v-card-text>

            <!-- Username -->
            <v-text-field v-model="form.username" label="Pseudo" prepend-inner-icon="mdi-account" variant="outlined"
              :error-messages="errors.username" :disabled="loading" autocomplete="username" class="mb-2" />

            <!-- Email -->
            <v-text-field v-model="form.email" label="Email" type="email" prepend-inner-icon="mdi-email"
              variant="outlined" :error-messages="errors.email" :disabled="loading" autocomplete="email" class="mb-2" />
              <!-- Photo de profil -->
            <div class="d-flex flex-column align-center mb-4">
              <v-avatar size="100" class="mb-3">
                <v-img :src="photoPreview ?? '/images/defaut-profile.png'" />
              </v-avatar>

              <v-btn variant="outlined" size="small" prepend-icon="mdi-camera" :disabled="loading"
                @click="$refs.photoInput.click()">
                {{ photoPreview ? 'Changer la photo' : 'Ajouter une photo' }}
              </v-btn>

              <input ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp" style="display:none"
                @change="handlePhoto" />

              <v-btn v-if="photoPreview" variant="text" color="error" size="x-small" class="mt-1" @click="removePhoto">
                Supprimer
              </v-btn>

              <span v-if="errors.profile_photo" class="text-error text-caption mt-1">
                {{ errors.profile_photo[0] }}
              </span>
            </div>

            <!-- Mot de passe -->
            <v-text-field v-model="form.password" label="Mot de passe" :type="showPassword ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword" variant="outlined" :error-messages="errors.password"
              :disabled="loading" autocomplete="new-password" class="mb-1" />

            <!-- Indicateur de force du mot de passe -->
            <div v-if="form.password" class="mb-4">
              <div class="text-caption mb-1">
                Force : <strong :class="passwordStrength.color">{{ passwordStrength.label }}</strong>
              </div>
              <v-progress-linear :model-value="passwordStrength.score" :color="passwordStrength.color" height="4"
                rounded />
            </div>

            <!-- Confirmation mot de passe -->
            <v-text-field v-model="form.password_confirmation" label="Confirmer le mot de passe"
              :type="showPasswordConfirm ? 'text' : 'password'" prepend-inner-icon="mdi-lock-check"
              :append-inner-icon="showPasswordConfirm ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPasswordConfirm = !showPasswordConfirm" variant="outlined"
              :error-messages="errors.password_confirmation" :disabled="loading" autocomplete="new-password"
              class="mb-4" />
            <!-- CGU -->
            <v-checkbox v-model="form.cgu_accepted" :error-messages="errors.cgu_accepted" :disabled="loading"
              density="compact" hide-details="auto" class="mb-4">
              <template #label>
                <span class="text-body-2">
                  J'accepte les
                  <RouterLink to="/cgu" target="_blank" class="text-primary">
                    Conditions Générales d'Utilisation
                  </RouterLink>
                </span>
              </template>
            </v-checkbox>

            <!-- Bouton -->
            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading || !form.cgu_accepted"
              @click="register">
              S'inscrire
            </v-btn>

          </v-card-text>

          <v-card-actions class="justify-center">
            <span class="text-body-2">Déjà un compte ?</span>
            <v-btn variant="text" color="primary" to="/login" size="small">
              Se connecter
            </v-btn>
          </v-card-actions>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'
import { usePasswordStrength } from '../../composables/usePasswordStrength'
import { usePasswordValidation } from '../../composables/usePasswordValidation'

const router = useRouter()

const form = ref({
  username: '',
  email: '',
  password: '',
  password_confirmation: '',
  cgu_accepted: false,
})

const errors = ref({})
const errorMessage = ref(null)
const successMessage = ref(null)
const loading = ref(false)
const showPassword = ref(false)
const showPasswordConfirm = ref(false)
const photoPreview = ref(null)
const photoFile = ref(null)
  
const { passwordStrength } = usePasswordStrength(() => form.value.password)
const { validatePassword } = usePasswordValidation(() => form.value.password)

function handlePhoto(e) {
  const file = e.target.files[0]
  if (!file) return

  // Validation taille (2MB max) et type
  if (file.size > 2 * 1024 * 1024) {
    errors.value.profile_photo = ['La photo ne doit pas dépasser 2MB.']
    return
  }
  if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
    errors.value.profile_photo = ['Format accepté : JPG, PNG, WEBP.']
    return
  }

  errors.value.profile_photo = []
  photoFile.value = file
  photoPreview.value = URL.createObjectURL(file) // ✅ aperçu instantané
}

function removePhoto() {
  photoFile.value = null
  photoPreview.value = null
  photoInput.value.value = '' // reset l'input file
}

// Validation côté client
function validateForm() {
  const e = {}

  if (!form.value.username.trim())
    e.username = ['Le pseudo est requis.']
  else if (form.value.username.length < 3)
    e.username = ['Le pseudo doit faire au moins 3 caractères.']

  if (!form.value.email.trim())
    e.email = ['L\'email est requis.']
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    e.email = ['L\'email n\'est pas valide.']

  const passwordError = validatePassword()
  if (passwordError) e.password = [passwordError]

  if (form.value.password !== form.value.password_confirmation)
    e.password_confirmation = ['Les mots de passe ne correspondent pas.']
  if (!form.value.cgu_accepted)
    e.cgu_accepted = ['Vous devez accepter les CGU.']
  return e
}

async function register() {
  errorMessage.value = null
  successMessage.value = null

  errors.value = validateForm()
  if (Object.keys(errors.value).length > 0) return

  loading.value = true

  try {
    await api.get('/sanctum/csrf-cookie')

    const formData = new FormData()
    formData.append('username', form.value.username)
    formData.append('email', form.value.email)
    formData.append('password', form.value.password)
    formData.append('password_confirmation', form.value.password_confirmation)
    if (photoFile.value) {
      formData.append('profile_photo', photoFile.value)
    }
    formData.append('cgu_accepted', form.value.cgu_accepted ? '1' : '0')

    await api.post('/register', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    successMessage.value = 'Compte créé ! Vérifiez votre email.'
    setTimeout(() => router.push('/verify-email'), 1500)

  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
  }
}
</script>