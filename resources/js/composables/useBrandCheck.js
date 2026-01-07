import { $api } from '@/utils/api'
import { computed, watch } from 'vue'

// Cache per il risultato del controllo brand (per utente)
let brandCheckCache = null
let brandCheckPromise = null
let cachedUserId = null

/**
 * Composable per controllare se l'utente ha il brand "ALFACOM SOLAR" abilitato
 * Gli admin vedono sempre i menu items
 */
export function useBrandCheck() {
  const userData = useCookie('userData')
  
  // Controlla se l'utente è admin
  const isAdmin = computed(() => {
    if (!userData.value?.roles) return false
    return userData.value.roles.some(
      role => role.name === 'gestione'
    )
  })

  // Resetta la cache se cambia l'utente
  watch(() => userData.value?.id, (newUserId) => {
    if (newUserId !== cachedUserId) {
      brandCheckCache = null
      brandCheckPromise = null
      cachedUserId = newUserId
    }
  }, { immediate: true })

  // Funzione per controllare se l'utente ha il brand ALFACOM SOLAR abilitato
  const checkAlfacomSolarBrand = async () => {
    // Se è admin, restituisce sempre true
    if (isAdmin.value) {
      return true
    }

    // Verifica che l'utente sia loggato
    if (!userData.value?.id) {
      return false
    }

    // Se abbiamo già il risultato in cache per questo utente, restituiscilo
    if (brandCheckCache !== null && cachedUserId === userData.value.id) {
      return brandCheckCache
    }

    // Se c'è già una richiesta in corso, aspetta il suo risultato
    if (brandCheckPromise) {
      return brandCheckPromise
    }

    // Aggiorna l'ID utente nella cache
    cachedUserId = userData.value.id

    // Crea una nuova richiesta
    brandCheckPromise = (async () => {
      try {
        const response = await $api('/brands', {
          query: {
            enabled: 1,
            itemsPerPage: 1000,
          },
        })

        // Cerca il brand "ALFACOM SOLAR" nella risposta
        // La risposta ha la struttura: { brands: [...], totalPages: ..., totalBrands: ..., page: ... }
        const brands = response?.brands || []
        const hasAlfacomSolar = brands.some(
          brand => brand.name === 'ALFACOM SOLAR'
        )
        console.log('hasAlfacomSolar', hasAlfacomSolar)
        console.log('brands', brands)
        console.log('response', response)

        brandCheckCache = hasAlfacomSolar
        return hasAlfacomSolar
      } catch (error) {
        console.error('Errore nel controllo del brand:', error)
        // In caso di errore, restituisci false per sicurezza
        brandCheckCache = false
        return false
      } finally {
        // Reset della promise dopo il completamento
        brandCheckPromise = null
      }
    })()

    return brandCheckPromise
  }

  // Funzione per resettare la cache (utile dopo login/logout)
  const resetCache = () => {
    brandCheckCache = null
    brandCheckPromise = null
  }

  return {
    isAdmin,
    checkAlfacomSolarBrand,
    resetCache,
  }
}

