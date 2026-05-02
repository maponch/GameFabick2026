<template>
  <v-app>
    <AppHeader />
    <v-banner v-if="showBanner" color="warning" icon="mdi-email-alert" lines="one" sticky>
      <v-banner-text>
        Votre email n'est pas vérifié — certaines fonctionnalités sont limitées.
      </v-banner-text>
      <template #actions>
        <v-btn variant="text" to="/verify-email">
          Vérifier maintenant
        </v-btn>
      </template>
    </v-banner>

    <v-main>
      <router-view />
    </v-main>

    <AppFooter />
  </v-app>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/layout/AppHeader.vue'
import AppFooter from './components/layout/AppFooter.vue'
import { getUser } from './router'

const route = useRoute()
const showBanner = ref(false)

// Vérifie à chaque changement de route
watch(
  () => route.path,
  async () => {
    const user = await getUser()
    showBanner.value = !!user && !user.email_verified_at
  },
  { immediate: true }
)
</script>