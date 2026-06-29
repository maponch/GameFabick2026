<template>
  <!-- HERO -->
  <v-container fluid class="pa-0">
    <v-img :src="isDesktop ? bannerDesktop : bannerMobile" :height="isDesktop ? '80vh' : '60vh'" cover
      class="hero-img d-flex align-center" content-class="hero-image">
      <div class="hero-overlay d-flex align-center justify-center" :class="{ active: showOverlay }">
        <v-container class="text-center text-white hero-content">
          <h1 class="text-h3 text-md-h2 font-weight-bold mb-4">
            <template v-if="user">
              Bon retour, {{ user.username }} 🎲
            </template>
            <template v-else>
              Crée ton jeu de société 🎲
            </template>
          </h1>
          <p class="text-body-1 text-md-h6 mb-6 mx-auto hero-text">
            <template v-if="user">
              Reprends tes projets, explore la galerie ou lance un nouveau jeu.
            </template>
            <template v-else>
              Imagine, construis et partage tes propres jeux.
            </template>
          </p>

          <div class="d-flex justify-center ga-3 flex-wrap">
            <template v-if="user">
              <v-btn color="primary" size="large" prepend-icon="mdi-folder-multiple" to="/projects">
                Mes projets
              </v-btn>
              <v-btn color="white" variant="outlined" size="large" prepend-icon="mdi-cards-playing" to="/games">
                Bibliothèque
              </v-btn>
            </template>
            <template v-else>
              <v-btn color="primary" size="large" to="/register">
                Créer un compte
              </v-btn>
              <v-btn color="white" variant="outlined" size="large" to="/login">
                Se connecter
              </v-btn>
            </template>
          </div>
        </v-container>
      </div>
    </v-img>
  </v-container>
  <!-- INTRO -->
  <v-container class="section-spacing py-md-8">
    <v-row justify="center">
      <v-col cols="12" md="9" lg="8" class="text-center">
        <div class="kicker mb-2">À propos</div>
        <h2 class="text-h4 text-md-h3 font-weight-bold mb-4">
          Tes jeux, tes règles
        </h2>
        <p class="text-body-1 text-md-h6 text-medium-emphasis lh-md">
          GameFabrick t'aide à transformer une idée de jeu en un objet jouable.
          Inspire-toi des modèles existants ou pars d'une page blanche, ajoute tes cartes,
          définis tes règles, et imprime ton jeu en quelques clics. Pas besoin
          d'être designer ni développeur — juste l'envie de jouer.
        </p>
      </v-col>
    </v-row>
  </v-container>

  <!-- FEATURES -->
  <v-container class="section-spacing">
    <!-- FEATURES  -->
    <div class="kicker text-center mb-2">Fonctionnalités</div>
    <h2 class="text-h4 text-md-h3 text-center font-weight-bold mb-10">
      Ce que tu peux faire
    </h2>
    <v-row>
      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center h-100" variant="tonal">
          <v-icon size="48" color="primary">mdi-pencil-ruler</v-icon>
          <h3 class="text-h6 mt-3 mb-2">Créer</h3>
          <p class="text-body-2">
            Conçois tes règles, tes cartes et leurs propriétés. Pars d'un modèle existant
            ou démarre de zéro.
          </p>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center h-100" variant="tonal">
          <v-icon size="48" color="primary">mdi-view-gallery</v-icon>
          <h3 class="text-h6 mt-3 mb-2">Explorer</h3>
          <p class="text-body-2">
            Parcours la galerie des jeux publiés par la communauté. Note, commente,
            duplique pour adapter à ton style.
          </p>
        </v-card>
      </v-col>
      <v-col cols="12" md="4">
        <v-card class="pa-5 text-center h-100" variant="tonal">
          <v-icon size="48" color="primary">mdi-printer</v-icon>
          <h3 class="text-h6 mt-3 mb-2">Imprimer</h3>
          <p class="text-body-2">
            Génère un PDF imprimable de tes cartes, ou un pense-bête à utiliser
            avec un jeu de cartes classique.
          </p>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <!-- MOCKUP CARTE -->
  <v-container class="section-spacing">
    <v-row align="center">
      <v-col cols="12" md="6">
        <div class="kicker mb-2">Personnalisation</div>
        <h2 class="text-h4 text-md-h3 font-weight-bold mb-4">
          Des cartes uniques, à ton image
        </h2>
        <p class="text-body-1 mb-4">
          Chaque carte peut avoir ses propres champs : nom, couleur, propriétés,
          numéros. Compose la structure qui correspond à ton jeu.
        </p>
        <p class="text-body-1">
          Imprime, partage, joue.
        </p>
      </v-col>
      <v-col cols="12" md="6" class="d-flex justify-center">
        <div class="mockup-row">
          <div class="mockup-card"
            style="background: linear-gradient(135deg, #6366f1, #8b5cf6); transform: rotate(-6deg);">
            <div class="mockup-card-title">Loup-Garou</div>
            <div class="mockup-card-body">Élimine tous les villageois pendant la nuit.</div>
            <div class="mockup-card-footer">Camp : Loup</div>
          </div>
          <div class="mockup-card"
            style="background: linear-gradient(135deg, #10b981, #14b8a6); transform: rotate(3deg);">
            <div class="mockup-card-title">Voyante</div>
            <div class="mockup-card-body">Découvre l'identité d'un joueur chaque nuit.</div>
            <div class="mockup-card-footer">Camp : Village</div>
          </div>
          <div class="mockup-card"
            style="background: linear-gradient(135deg, #f59e0b, #f97316); transform: rotate(-2deg);">
            <div class="mockup-card-title">Chasseur</div>
            <div class="mockup-card-body">Élimine un joueur en mourant.</div>
            <div class="mockup-card-footer">Camp : Village</div>
          </div>
        </div>
      </v-col>
    </v-row>
  </v-container>

  <!-- COMMENT ÇA MARCHE -->
  <v-container class="section-spacing">
    <div class="kicker text-center mb-2">En 3 étapes</div>
    <h2 class="text-h4 text-md-h3 text-center font-weight-bold mb-10">
      Comment ça marche
    </h2>
    <v-row>
      <v-col cols="12" md="4">
        <div class="step-block text-center">
          <div class="step-number">1</div>
          <h3 class="text-h6 mb-2">Choisis ta base</h3>
          <p class="text-body-2">
            Pars d'un modèle existant comme Loup-Garou, ou crée ton jeu de zéro.
          </p>
        </div>
      </v-col>
      <v-col cols="12" md="4">
        <div class="step-block text-center">
          <div class="step-number">2</div>
          <h3 class="text-h6 mb-2">Personnalise</h3>
          <p class="text-body-2">
            Ajoute tes cartes, leurs propriétés, leurs règles. Modifie tant que tu veux.
          </p>
        </div>
      </v-col>
      <v-col cols="12" md="4">
        <div class="step-block text-center">
          <div class="step-number">3</div>
          <h3 class="text-h6 mb-2">Joue</h3>
          <p class="text-body-2">
            Télécharge ton PDF, lance la partie, et partage avec la communauté.
          </p>
        </div>
      </v-col>
    </v-row>
  </v-container>

  <!-- GALERIE COMMUNITY -->
  <v-container v-if="featured.length > 0" class="section-spacing">
    <div class="d-flex align-center justify-space-between mb-6 flex-wrap ga-3">
      <!-- GALERIE : remplacer le h2 actuel -->
      <div>
        <div class="kicker mb-1">Communauté</div>
        <h2 class="text-h4 text-md-h3 font-weight-bold">Ils ont créé...</h2>
      </div>
      <v-btn variant="text" to="/community" append-icon="mdi-arrow-right">
        Voir tous les jeux
      </v-btn>
    </div>
    <v-row>
      <v-col v-for="p in featured" :key="p.id" cols="12" sm="6" md="4">
        <v-card variant="outlined" class="h-100 cursor-pointer" @click="$router.push(`/community/${p.id}`)">
          <v-card-text>
            <h3 class="text-h6 mb-1">{{ p.name }}</h3>
            <p class="text-caption text-medium-emphasis mb-2">
              par {{ p.author?.username ?? 'Anonyme' }}
            </p>
            <p class="text-body-2 mb-2 line-clamp-3">{{ p.description }}</p>
            <div class="d-flex align-center ga-2 mt-3">
              <v-chip size="x-small" prepend-icon="mdi-account-group">
                {{ p.min_players }}-{{ p.max_players }}
              </v-chip>
              <v-chip size="x-small" prepend-icon="mdi-clock-outline">
                {{ p.duration_min }}-{{ p.duration_max }} min
              </v-chip>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </v-container>

  <!-- CTA FINAL -->
  <v-container class="text-center section-spacing">
    <h2 class="text-h4 text-md-h3 font-weight-bold mb-4">
      <template v-if="user">Prêt à reprendre ?</template>
      <template v-else>Prêt à créer ton jeu ?</template>
    </h2>
    <v-btn v-if="user" color="primary" size="large" prepend-icon="mdi-plus" to="/projects/new">
      Créer un nouveau projet
    </v-btn>
    <v-btn v-else color="primary" size="large" to="/register">
      Rejoindre maintenant
    </v-btn>
  </v-container>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useResponsive } from '@/composables/useResponsive'
import { api } from '@/api'
import { getUser } from '@/router'
import bannerMobile from '@/assets/images/banner-mobile.jpg'
import bannerDesktop from '@/assets/images/banner-desktop.jpg'

const { isDesktop } = useResponsive()
const showOverlay = ref(false)
const user = ref(null)
const featured = ref([])

async function loadFeatured() {
  try {
    const { data } = await api.get('/community')
    featured.value = (data ?? [])
      .slice()
      .sort((a, b) => (b.average_rating ?? 0) - (a.average_rating ?? 0))
      .slice(0, 3)
  } catch {
    featured.value = []
  }
}

const handleScroll = () => {
  showOverlay.value = window.scrollY > 25
}

onMounted(async () => {
  user.value = await getUser()
  window.addEventListener('scroll', handleScroll)
  loadFeatured()
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})
</script>

<style scoped>
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.75));
  opacity: 0;
  transition: opacity 0.25s ease;
}

.hero-overlay.active {
  opacity: 1;
}

.hero-overlay.active .hero-content {
  transform: translateX(-50%) translateY(0);
  transition: all 0.4s ease;
}

.hero-image img {
  object-position: bottom;
}

.hero-content {
  position: absolute;
  bottom: 60px;
  left: 50%;
  transform: translateX(-50%);
  text-align: center;
  color: white;
  max-width: 700px;
  padding: 0 20px;
}

@media (max-width: 768px) {
  .hero-content {
    bottom: 40px;
  }
}

.step-number {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  background: rgb(var(--v-theme-primary));
  color: white;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: 700;
  margin-bottom: 12px;
}

.mockup-row {
  display: flex;
  gap: 16px;
  perspective: 1000px;
  max-width: 100%;
  flex-wrap: wrap;
  justify-content: center;
}

.mockup-card {
  width: 130px;
  height: 200px;
  border-radius: 12px;
  padding: 14px;
  color: white;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  transition: transform 0.3s ease;
}

.mockup-card:hover {
  transform: translateY(-8px) rotate(0deg) !important;
}

.mockup-card-title {
  font-weight: 700;
  font-size: 1.1rem;
  line-height: 1.2;
}

.mockup-card-body {
  font-size: 0.8rem;
  opacity: 0.95;
  line-height: 1.3;
}

.mockup-card-footer {
  font-size: 0.7rem;
  font-weight: 600;
  opacity: 0.8;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.cursor-pointer {
  cursor: pointer;
}

.kicker {
  text-transform: uppercase;
  letter-spacing: 0.18em;
  font-size: 1rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
}



.lh-md {
  line-height: 1.7;
}
:deep(body),
:deep(html) {
  overflow-x: hidden;
}
.section-spacing {
  margin-top: 80px;
  margin-bottom: 80px;
}

@media (min-width: 960px) {
  .section-spacing {
    margin-top: 140px;
    margin-bottom: 140px;
  }
}
@media (max-width: 600px) {
  .mockup-card {
    width: 110px;
    height: 170px;
  }

  .mockup-card:nth-child(3) {
    display: none;
    /* on retire la 3e carte sur mobile pour éviter le scroll */
  }
}

@media (min-width: 960px) {
  .kicker {
    font-size: 1.15rem;
  }
}
</style>