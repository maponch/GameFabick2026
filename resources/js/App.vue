<template>
  <v-app>
    <AppHeader />

    <v-main>
      <v-banner v-if="showEmailBannerVisible" color="warning" icon="mdi-email-alert" lines="one">
        <v-banner-text>
          Votre email n'est pas vérifié — certaines fonctionnalités sont limitées.
        </v-banner-text>
        <template #actions>
          <v-btn variant="text" to="/verify-email">Vérifier maintenant</v-btn>
          <v-btn icon="mdi-close" variant="text" @click="showEmailBanner = false" />
        </template>
      </v-banner>

      <v-banner v-if="showCguBanner" color="error" icon="mdi-shield-alert" lines="two">
        <v-banner-text>
          Les Conditions Générales d'Utilisation ont été mises à jour.
          Vous devez les accepter pour continuer à utiliser GameFabrick.
        </v-banner-text>
        <template #actions>
          <v-btn variant="text" to="/cgu" target="_blank">Lire les CGU</v-btn>
          <v-btn variant="flat" color="white" :loading="acceptingCgu" @click="acceptCgu">
            Adhérer
          </v-btn>
        </template>
      </v-banner>

      <router-view />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/layout/AppHeader.vue'
import AppFooter from './components/layout/AppFooter.vue'
import { getUser, clearUser } from './router'
import { api } from './api'

const route = useRoute()
const user = ref(null)
const showEmailBanner = ref(true)
const acceptingCgu = ref(false)

const showEmailBannerVisible = computed(() =>
  !!user.value && !user.value.email_verified_at && showEmailBanner.value
)

const showCguBanner = computed(() =>
  !!user.value && user.value.cgu_needs_acceptance === true
)

async function acceptCgu() {
  acceptingCgu.value = true
  try {
    const { data } = await api.post('/account/accept-cgu')
    user.value = data.user
    clearUser()
  } catch (e) {
    console.error('Erreur acceptation CGU:', e)
  } finally {
    acceptingCgu.value = false
  }
}

watch(
  () => route.path,
  async () => { user.value = await getUser() },
  { immediate: true }
)
</script>