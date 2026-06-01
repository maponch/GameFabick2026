export const RANKS = [
  { id: '2', label: '2' },
  { id: '3', label: '3' },
  { id: '4', label: '4' },
  { id: '5', label: '5' },
  { id: '6', label: '6' },
  { id: '7', label: '7' },
  { id: '8', label: '8' },
  { id: '9', label: '9' },
  { id: '10', label: '10' },
  { id: 'J', label: 'Valet' },
  { id: 'Q', label: 'Dame' },
  { id: 'K', label: 'Roi' },
  { id: 'A', label: 'As' },
]

export const SUITS = [
  { id: 'spades', label: 'Pique', symbol: '♠', color: 'black' },
  { id: 'hearts', label: 'Cœur', symbol: '♥', color: 'red' },
  { id: 'diamonds', label: 'Carreau', symbol: '♦', color: 'red' },
  { id: 'clubs', label: 'Trèfle', symbol: '♣', color: 'black' },
]

export const JOKERS = [
  { id: 'joker-red', label: 'Joker rouge', color: 'red' },
  { id: 'joker-black', label: 'Joker noir', color: 'black' },
]

// Identifiant d'une carte normale : "K-hearts"
export function cardId(rank, suit) {
  return `${rank}-${suit}`
}

// Libellé affiché : "Roi de Cœur"
export function cardLabel(id) {
  const joker = JOKERS.find(j => j.id === id)
  if (joker) return joker.label

  const [rank, suit] = id.split('-')
  const r = RANKS.find(x => x.id === rank)
  const s = SUITS.find(x => x.id === suit)
  if (!r || !s) return id
  return `${r.label} de ${s.label}`
}

// Liste plate des 54 identifiants
export function allCards() {
  const cards = []
  for (const r of RANKS) {
    for (const s of SUITS) {
      cards.push(cardId(r.id, s.id))
    }
  }
  cards.push(...JOKERS.map(j => j.id))
  return cards
}

// Raccourci "toute une valeur" : les 4 cartes d'un rank → ["K-spades","K-hearts",...]
export function cardsByRank(rankId) {
  return SUITS.map(s => cardId(rankId, s.id))
}

// Raccourci "toute une enseigne" : les 13 cartes d'un suit
export function cardsBySuit(suitId) {
  return RANKS.map(r => cardId(r.id, suitId))
}

// Détecte si tous les éléments d'un groupe sont présents dans une sélection
export function isRankFullySelected(rankId, selection) {
  return cardsByRank(rankId).every(id => selection.includes(id))
}

export function isSuitFullySelected(suitId, selection) {
  return cardsBySuit(suitId).every(id => selection.includes(id))
}
export function cardShortLabel(id) {
  const joker = JOKERS.find(j => j.id === id)
  if (joker) return joker.id === 'joker-red' ? 'Joker R' : 'Joker N'

  const [rank, suit] = id.split('-')
  const s = SUITS.find(x => x.id === suit)
  if (!s) return id
  return `${rank}${s.symbol}`
}

// Couleur d'une carte ('red' | 'black') pour la coloration
export function cardColor(id) {
  const joker = JOKERS.find(j => j.id === id)
  if (joker) return joker.color

  const [, suit] = id.split('-')
  const s = SUITS.find(x => x.id === suit)
  return s ? s.color : 'black'
}