<template>
  <v-app>
    <AppHeader />

    <v-main>
      <v-banner v-if="showBanner" color="warning" icon="mdi-email-alert" lines="one" >
        <v-banner-text>
          Votre email n'est pas vérifié — certaines fonctionnalités sont limitées.
        </v-banner-text>
        <template #actions>
          <v-btn variant="text" to="/verify-email">
            Vérifier maintenant
          </v-btn>
          <v-btn icon="mdi-close" variant="text" @click="showEmailBanner = false" />
        </template>
      </v-banner>
      <v-banner v-if="user?.scheduled_deletion_at && showDeletionBanner" color="deep-orange" icon="mdi-delete-clock"
        lines="one">
        <v-banner-text>
          Votre compte sera définitivement supprimé le
          <strong>{{ new Date(user.scheduled_deletion_at).toLocaleDateString('fr-FR') }}</strong>.
          Contactez un admin pour annuler.
        </v-banner-text>
        <template #actions>
          <v-btn icon="mdi-close" variant="text" @click="showDeletionBanner = false" />
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
import { getUser } from './router'

const route = useRoute()
const user = ref(null)

const showDeletionBanner = ref(true)
const showEmailBanner = ref(true)
const showBanner = computed(() =>
  !!user.value && !user.value.email_verified_at && showEmailBanner.value
)

watch(
  () => route.path,
  async () => {
    user.value = await getUser()
  },
  { immediate: true }
)
</script>