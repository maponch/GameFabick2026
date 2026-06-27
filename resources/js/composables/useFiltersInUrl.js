import { ref, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useFiltersInUrl(defaults) {
  const route = useRoute()
  const router = useRouter()
  const state = ref({ ...defaults })

  onMounted(() => {
    const next = { ...defaults }
    for (const key of Object.keys(defaults)) {
      const raw = route.query[key]
      if (raw === undefined || raw === '') continue
      const def = defaults[key]
      if (typeof def === 'number') next[key] = Number(raw)
      else if (typeof def === 'boolean') next[key] = raw === 'true'
      else next[key] = raw
    }
    state.value = next
  })

  watch(state, (val) => {
    const query = { ...route.query }
    for (const key of Object.keys(defaults)) {
      const v = val[key]
      const def = defaults[key]
      if (v === null || v === undefined || v === '' || v === def) {
        delete query[key]
      } else {
        query[key] = String(v)
      }
    }
    router.replace({ query }).catch(() => { })
  }, { deep: true })

  return state
}