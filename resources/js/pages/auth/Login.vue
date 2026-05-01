<template>
  <v-container class="fill-height" fluid>
    <v-row align="center" justify="center">
      <v-col cols="12" sm="8" md="5" lg="4">

        <v-card class="pa-6" elevation="4" rounded="lg">

          <v-card-title class="text-h5 text-center mb-2">
            Connexion 🎲
          </v-card-title>
          <v-card-subtitle class="text-center mb-4">
            Bon retour sur GameFabrick
          </v-card-subtitle>

          <!-- Erreur globale -->
          <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4" closable
            @click:close="errorMessage = null">
            {{ errorMessage }}
          </v-alert>

          <v-card-text>

            <!-- Email -->
            <v-text-field v-model="form.email" label="Email" type="email" prepend-inner-icon="mdi-email"
              variant="outlined" :error-messages="errors.email" :disabled="loading" autocomplete="email" class="mb-2"               
              @update:model-value="val => form.email = val"
              @keyup.enter="login" />

            <!-- Mot de passe -->
            <v-text-field v-model="form.password" label="Mot de passe" :type="showPassword ? 'text' : 'password'"
              prepend-inner-icon="mdi-lock" :append-inner-icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'"
              @click:append-inner="showPassword = !showPassword" variant="outlined" :error-messages="errors.password"
              :disabled="loading" autocomplete="current-password" class="mb-6" @keyup.enter="login" />

            <!-- Bouton -->
            <v-btn block color="primary" size="large" :loading="loading" :disabled="loading" @click="login">
              Se connecter
            </v-btn>

          </v-card-text>

          <v-card-actions class="justify-center">
            <span class="text-body-2">Pas encore de compte ?</span>
            <v-btn variant="text" color="primary" to="/register" size="small">
              S'inscrire
            </v-btn>
            <v-btn variant="text" color="primary" to="/forgot-password" size="small">
              Mot de passe oublié ?
            </v-btn>
          </v-card-actions>

        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { api } from '../../api'

const router = useRouter()

const form = ref({
  email: '',
  password: '',
})

const errors = ref({})
const errorMessage = ref(null)
const loading = ref(false)
const showPassword = ref(false)

function validateForm() {
  const e = {}

  try{
    if (!form.value.email.trim())
    e.email = ['L\'email est requis.']
  else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email))
    e.email = ['L\'email n\'est pas valide.']
  } catch (e) {
    console.log('Erreurs Laravel:', e.response.data) // 👈 ce log précisément
  }

  if (!form.value.password)
    e.password = ['Le mot de passe est requis.']
  

  return e
}

async function login() {
  errorMessage.value = null
  errors.value = {}

  form.value.email = form.value.email.trim()

  console.log('Données envoyées:', {      
    email: form.value.email,
    password: form.value.password,
    emailLength: form.value.email.length,
    passwordLength: form.value.password.length,
  })

  // Validation côté client
  errors.value = validateForm()
  if (Object.keys(errors.value).length > 0) return

  loading.value = true

  try {
    await api.get('/sanctum/csrf-cookie')
    const { data } = await api.post('/login', form.value)

    // Redirige selon le rôle
    if (data.user?.role === 'admin') {
      router.push('/admin')
    } else {
      router.push('/dashboard')
    }

  } catch (e) {
    if (e.response?.status === 422) {
      // Erreurs de validation Laravel (identifiants invalides)
      console.log('Erreurs Laravel:', e.response.data)
      errors.value = e.response.data.errors ?? {}
    } else if (e.response?.status === 429) {
      // Trop de tentatives (throttle)
      errorMessage.value = 'Trop de tentatives. Veuillez patienter avant de réessayer.'
    } else {
      errorMessage.value = 'Une erreur est survenue. Veuillez réessayer.'
    }
  } finally {
    loading.value = false
    form.value.password = '' // ✅ vide le mot de passe après chaque tentative
  }
}
</script>