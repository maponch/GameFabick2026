<template>
  <v-footer color="indigo-lighten-1" class="pa-0">
    <v-container max-width="1200" class="py-6">
      <v-row>
        <v-col cols="12" md="4">
          <div class="text-h6 font-weight-bold mb-2">GameFabrick 🎲</div>
          <p class="text-body-2 opacity-80">
            Générez des jeux de société personnalisés à partir de templates
            ou créez les vôtres. Imprimez vos cartes ou utilisez un jeu de
            cartes classique avec un pense-bête.
          </p>
        </v-col>

        <v-col cols="6" md="3">
          <div class="text-subtitle-2 font-weight-bold mb-2">Navigation</div>
          <ul class="footer-links">
            <li>
              <RouterLink to="/community">Galerie</RouterLink>
            </li>
            <li>
              <RouterLink to="/games">Jeux</RouterLink>
            </li>
            <li>
              <RouterLink to="/projects">Mes projets</RouterLink>
            </li>
          </ul>
        </v-col>

        <v-col cols="6" md="3">
          <div class="text-subtitle-2 font-weight-bold mb-2">Informations</div>
          <ul class="footer-links">
            <li>
              <RouterLink to="/cgu">CGU</RouterLink>
            </li>
          </ul>
        </v-col>

        <v-col cols="12" md="2">
          <div class="text-subtitle-2 font-weight-bold mb-2">Suivez-nous</div>
          <div class="d-flex ga-1">
            <v-btn v-for="s in socials" :key="s.icon" :icon="s.icon" :href="s.url" target="_blank" rel="noopener"
              density="comfortable" variant="text" size="small" />
          </div>
        </v-col>
      </v-row>

      <v-divider class="my-4 opacity-30" />

      <div class="d-flex flex-wrap justify-space-between align-center ga-2 text-body-2 opacity-80">
        <div>© {{ year }} — GameFabrick</div>
        <div class="text-caption">TFE — Projet de fin d'études</div>
      </div>
    </v-container>
  </v-footer>
</template>

<script setup>
import { computed } from 'vue'

const year = computed(() => new Date().getFullYear())

const socials = [
  { icon: 'mdi-facebook', url: '#' },
  { icon: 'mdi-twitter', url: '#' },
  { icon: 'mdi-linkedin', url: '#' },
  { icon: 'mdi-instagram', url: '#' },
]
</script>

<style scoped>
.footer-links {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-links li {
  margin-bottom: 0.5rem;
}

.footer-links a {
  color: inherit;
  text-decoration: none;
  opacity: 0.85;
  transition: opacity 0.15s;
}

.footer-links a:hover {
  opacity: 1;
  text-decoration: underline;
}
</style>