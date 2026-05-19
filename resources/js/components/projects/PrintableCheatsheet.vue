<template>
  <div ref="printArea" class="printable-container">

    <div class="printable-header">
      <h1>{{ project.title }}</h1>
      <p>Correspondance des cartes — {{ project.template?.name }}</p>
    </div>

    <table class="cheatsheet">
      <thead>
        <tr>
          <th>Carte du jeu</th>
          <th>Rôle</th>
          <th>Quantité</th>
          <th>Description</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="object in project.objects" :key="object.id">
          <td>
            <span v-for="mapping in object.existing_deck_mapping" :key="mapping" class="card-chip">
              {{ mapping }}
            </span>
          </td>
          <td><strong>{{ object.name }}</strong></td>
          <td>×{{ object.quantity }}</td>
          <td>{{ object.description }}</td>
        </tr>
      </tbody>
    </table>

  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({ project: { type: Object, required: true } })

const printArea = ref(null)
defineExpose({ printArea })
</script>

<style scoped>
.printable-container {
  background: white;
  color: black;
  padding: 20mm;
  width: 210mm;
  max-height: 290mm;
  overflow: hidden;
  box-sizing: border-box;
  font-family: Arial, sans-serif;
}

.printable-header {
  text-align: center;
  margin-bottom: 15mm;
  border-bottom: 2px solid #333;
  padding-bottom: 5mm;
}

.cheatsheet {
  width: 100%;
  border-collapse: collapse;
}

.cheatsheet th,
.cheatsheet td {
  border: 1px solid #ddd;
  padding: 6mm 4mm;
  text-align: left;
  font-size: 10pt;
}

.cheatsheet th {
  background: #f5f5f5;
  font-weight: bold;
}

.cheatsheet td:first-child {
  vertical-align: middle;
  /* ← centre verticalement dans la cellule */
}
.card-chip {
  display: inline-flex;
  align-items: center;
  background: #1976D2;
  color: white;
  padding: 4pt 8pt;
  /* ← augmenté pour respirer */
  border-radius: 4pt;
  margin-right: 2pt;
  margin-bottom: 2pt;
  font-size: 9pt;
  font-weight: bold;
  min-height: 18pt;
  /* ← hauteur minimale lisible */
}
</style>