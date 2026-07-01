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
            <span v-for="mapping in object.existing_deck_mapping" :key="mapping" class="card-chip"
              :class="cardColor(mapping)">
              {{ cardShortLabel(mapping) }}
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
import { cardShortLabel, cardColor } from '../../constants/classicDeck'

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
}
.card-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 28px;
  padding: 2mm 2mm;
  margin: 0.5mm;
  border-radius: 3px;
  border: 1.5px solid #333;
  background: white;
  font-size: 9pt;
  font-weight: 600;
  line-height: 1.4;
  vertical-align: middle;
}

.card-chip.red {
  color: #c62828;
  border-color: #c62828;
}

.card-chip.black {
  color: #000;
  border-color: #000;
}
</style>