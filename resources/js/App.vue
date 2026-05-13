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
        </v-banner-text>
        <template #actions>
          <!-- ✅ Bouton annulation visible uniquement si auto-suppression -->
          <v-btn v-if="user.deletion_initiator === 'self'" color="white" variant="tonal" prepend-icon="mdi-restore"
            :loading="cancelLoading" @click="cancelDeletion">
            Annuler la suppression
          </v-btn>
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
import { api } from './api'

const route = useRoute()
const user = ref(null)

const showDeletionBanner = ref(true)
const showEmailBanner = ref(true)
const showBanner = computed(() =>
  !!user.value && !user.value.email_verified_at && showEmailBanner.value
)
const cancelLoading = ref(false)

async function cancelDeletion() {
  cancelLoading.value = true
  try {
    await api.post('/user/cancel-deletion')
    user.value = await getUser() // refresh
  } catch (e) {
    console.error(e)
  } finally {
    cancelLoading.value = false
  }
}

watch(
  () => route.path,
  async () => {
    user.value = await getUser()
  },
  { immediate: true }
)
</script>