<template>
  <v-container max-width="900" class="py-8">
    <v-btn v-if="canGoBack" variant="text" prepend-icon="mdi-arrow-left" class="mb-4" @click="$router.back()">
      Retour
    </v-btn>

    <h1 class="text-h4 mb-2">Conditions Générales d'Utilisation</h1>
    <p class="text-caption text-medium-emphasis mb-6">
      Version en vigueur : <strong>{{ version }}</strong>
    </p>

    <v-card class="pa-6">
      <div class="cgu-content">
        <h2 class="text-h6 mt-2 mb-3">1. Objet des CGU</h2>
        <p>
          Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») ont pour objet d'encadrer
          l'accès et l'utilisation du service GameFabrick, une plateforme permettant de générer, stocker
          et gérer des jeux, scénarios et contenus associés.
        </p>
        <p>Toute utilisation de GameFabrick implique l'acceptation sans réserve des présentes CGU.</p>

        <h2 class="text-h6 mt-6 mb-3">2. Description du service</h2>
        <p>GameFabrick offre notamment les fonctionnalités suivantes :</p>
        <ul>
          <li>Génération automatisée de concepts, scénarios ou assets de jeux.</li>
          <li>Gestion, sauvegarde et modification des projets créés.</li>
          <li>Exportation éventuelle des contenus selon les formats disponibles.</li>
          <li>Accès à des ressources, templates ou outils intégrés.</li>
        </ul>
        <p>Le service est accessible en ligne, sous réserve de disponibilité et d'évolution technique.</p>

        <h2 class="text-h6 mt-6 mb-3">3. Accès au service</h2>
        <p>L'accès à GameFabrick peut nécessiter :</p>
        <ul>
          <li>la création d'un compte utilisateur ;</li>
          <li>une connexion internet ;</li>
          <li>un appareil compatible.</li>
        </ul>
        <p>L'utilisateur garantit l'exactitude des informations fournies et garde confidentiels ses identifiants.</p>

        <h2 class="text-h6 mt-6 mb-3">4. Utilisation du service</h2>
        <p>L'utilisateur s'engage à :</p>
        <ul>
          <li>utiliser le service conformément à la loi (pas de contenus illégaux, discriminatoires, diffamatoires…) ;
          </li>
          <li>ne pas utiliser GameFabrick pour générer ou diffuser des contenus violant des droits d'auteur ;</li>
          <li>ne pas perturber le bon fonctionnement de la plateforme (scripts automatisés, attaques, extraction de
            données…).</li>
        </ul>
        <p>GameFabrick se réserve le droit de suspendre ou supprimer un compte en cas de non-respect des CGU.</p>

        <h2 class="text-h6 mt-6 mb-3">5. Propriété intellectuelle</h2>
        <h3 class="text-subtitle-1 font-weight-bold mt-3">5.1 Propriété de GameFabrick</h3>
        <p>
          Tous les éléments techniques, graphiques, textuels et logiciels de GameFabrick sont protégés
          par le droit d'auteur et restent la propriété exclusive de la plateforme.
          Toute reproduction non autorisée est interdite.
        </p>
        <h3 class="text-subtitle-1 font-weight-bold mt-3">5.2 Propriété des contenus générés</h3>
        <p>Sauf indication contraire :</p>
        <ul>
          <li>L'utilisateur est propriétaire des contenus qu'il génère via GameFabrick, dans la limite des licences
            accordées par les technologies utilisées.</li>
          <li>L'utilisateur est responsable de vérifier que l'usage qu'il fait des contenus générés ne viole pas des
            droits tiers (par ex. marques, œuvres protégées).</li>
          <li>GameFabrick ne revendique aucun droit sur les créations de l'utilisateur.</li>
        </ul>

        <h2 class="text-h6 mt-6 mb-3">6. Responsabilité</h2>
        <h3 class="text-subtitle-1 font-weight-bold mt-3">6.1 Responsabilité de l'utilisateur</h3>
        <p>L'utilisateur est seul responsable :</p>
        <ul>
          <li>des contenus qu'il crée, diffuse ou exporte ;</li>
          <li>de la conformité légale de ses créations ;</li>
          <li>de l'usage commercial éventuel de ses productions.</li>
        </ul>
        <h3 class="text-subtitle-1 font-weight-bold mt-3">6.2 Responsabilité de GameFabrick</h3>
        <p>GameFabrick ne peut être tenue responsable :</p>
        <ul>
          <li>des dommages directs ou indirects liés à l'utilisation du service ;</li>
          <li>de la perte de données ;</li>
          <li>des interruptions ou dysfonctionnements temporaires ;</li>
          <li>des erreurs éventuelles dans les contenus générés.</li>
        </ul>
        <p>Ce service est fourni « tel quel » sans garantie de résultat.</p>

        <h2 class="text-h6 mt-6 mb-3">7. Données personnelles</h2>
        <p>
          GameFabrick traite certaines données personnelles dans le cadre du fonctionnement du service
          (création de compte, utilisation de l'application…).
          Le détail du traitement des données est présenté dans la Politique de Confidentialité RGPD, disponible
          séparément.
        </p>

        <h2 class="text-h6 mt-6 mb-3">8. Suspension ou suppression du compte</h2>
        <p>GameFabrick peut suspendre ou supprimer un compte notamment en cas de :</p>
        <ul>
          <li>violation des CGU ;</li>
          <li>usage frauduleux du service ;</li>
          <li>activité illégale ou dommageable.</li>
        </ul>
        <p>L'utilisateur peut à tout moment supprimer son compte depuis l'espace prévu.</p>

        <h2 class="text-h6 mt-6 mb-3">9. Modifications des CGU</h2>
        <p>
          GameFabrick se réserve le droit de modifier les CGU à tout moment.
          Les utilisateurs seront informés en cas de changements majeurs.
        </p>

        <h2 class="text-h6 mt-6 mb-3">10. Droit applicable</h2>
        <p>
          Les présentes CGU sont soumises au droit belge.
          En cas de litige, les tribunaux compétents seront ceux du ressort du siège de GameFabrick.
        </p>

        <h2 class="text-h6 mt-6 mb-3">11. Contact</h2>
        <p>Pour toute question relative aux CGU : <strong>support@gamefabrick.app</strong></p>
      </div>
    </v-card>
  </v-container>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const canGoBack = computed(() => window.history.length > 1)
const version = import.meta.env.VITE_CGU_VERSION ?? '2026-06-27'
</script>

<style scoped>
.cgu-content p {
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.cgu-content ul {
  margin-bottom: 0.75rem;
  padding-left: 1.25rem;
}

.cgu-content li {
  margin-bottom: 0.25rem;
}
</style>