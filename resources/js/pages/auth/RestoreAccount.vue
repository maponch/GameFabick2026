<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Restaurer mon compte 
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            Connectez-vous pour annuler la suppression de votre compte
          </v-card-subtitle>

          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
            @click:close="errorMessage = null">
            {{ errorMessage }}
          </v-alert>

          <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">
            {{ successMessage }}
          </v-alert>

          <v-card-text>

            <v-text-field v-model="form.email" label="Email" type="email" prepend-inner-icon="mdi-email"
              variant="outlined" :error-messages="errors.email" :disabled="loading" autocomplete="email" class="mb-2"
              @keyup.enter="restore" />

            <v-text-field v-model="form.password" label="Mot de passe" :type="showPassword ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword" variant="outlined" :error-messages="errors.password"
              :disabled="loading" autocomplete="current-password" class="mb-4" @keyup.enter="restore" />

            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="restore">
              Restaurer mon compte
            </v-btn>

          </v-card-text>

          <v-card-actions class="justify-center">
            <v-btn variant="text" to="/login" size="small">
              Retour à la connexion
            </v-btn>
          </v-card-actions>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { api } from '../../api'
import { clearUser } from '../../router'

const router = useRouter()
const route = useRoute()

const form = ref({
  email: route.query.email ?? '',
  password: '',
})

const errors = ref({})
const errorMessage = ref(null)
const successMessage = ref(null)
const loading = ref(false)
const showPassword = ref(false)

async function restore() {
  errors.value = {}
  errorMessage.value = null

  if (!form.value.email.trim()) {
    errors.value.email = ['L\'email est requis.']
    return
  }
  if (!form.value.password) {
    errors.value.password = ['Le mot de passe est requis.']
    return
  }

  loading.value = true
  try {
    await api.get('/sanctum/csrf-cookie')
    await api.post('/user/restore', {
      email: form.value.email.trim(),
      password: form.value.password,
    })

    clearUser() 
    successMessage.value = 'Compte restauré ! Redirection...'
    setTimeout(() => router.push('/dashboard'), 1500)

  } catch (e) {
    if (e.response?.status === 422) {
      errors.value = e.response.data.errors ?? {}
    } else if (e.response?.status === 404) {
      errorMessage.value = 'Aucun compte supprimé trouvé pour cet email.'
    } else if (e.response?.status === 403) {
      errorMessage.value = 'Délai de restauration dépassé. Le compte a été détruit définitivement.'
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
    form.value.password = ''
  }
}
</script>