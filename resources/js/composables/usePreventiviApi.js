import { ref } from 'vue';

// Cache per i dati caricati
const cache = {
  productCategories: null,
  tipologieTetto: null,
  vociEconomiche: null,
  coefficientiProduzione: null,
  prodottiFotovoltaico: null,
  modalitaPagamento: null,
};

// Funzione helper per fare chiamate API
async function fetchApi(endpoint, params = {}) {
  try {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `${endpoint}?${queryString}` : endpoint;
    const response = await $api(url);
    return response;
  } catch (error) {
    console.error(`Errore nel caricamento da ${endpoint}:`, error);
    throw error;
  }
}

// Funzione helper per estrarre i dati dalla risposta API
function extractDataFromResponse(response) {
  // Se è già un array, restituiscilo direttamente
  if (Array.isArray(response)) {
    return response;
  }
  
  // Prova vari formati comuni di risposta API
  if (response?.data && Array.isArray(response.data)) {
    return response.data;
  }
  
  if (response?.items && Array.isArray(response.items)) {
    return response.items;
  }
  
  if (response?.results && Array.isArray(response.results)) {
    return response.results;
  }
  
  // Se non è un array e non ha proprietà note, restituisci array vuoto
  return [];
}

export function usePreventiviApi() {
  const loading = ref(false);
  const error = ref(null);

  // Carica le categorie prodotti
  const loadProductCategories = async (query = '', itemsPerPage = 10) => {
    if (cache.productCategories && !query) {
      return cache.productCategories;
    }
    
    loading.value = true;
    error.value = null;
    try {
      const response = await fetchApi('/api/product-categories', {
        q: query,
        itemsPerPage: itemsPerPage.toString(),
      });
      // Estrai i dati dalla risposta
      const data = extractDataFromResponse(response);
      if (!query) {
        cache.productCategories = data;
      }
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Carica le tipologie tetto
  const loadTipologieTetto = async (query = '', itemsPerPage = 10) => {
    if (cache.tipologieTetto && !query) {
      return cache.tipologieTetto;
    }
    
    loading.value = true;
    error.value = null;
    try {
      const response = await fetchApi('/api/tipologie-tetto', {
        q: query,
        itemsPerPage: itemsPerPage.toString(),
      });
      const data = extractDataFromResponse(response);
      if (!query) {
        cache.tipologieTetto = data;
      }
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Carica le voci economiche
  const loadVociEconomiche = async (query = '', tipoVoce = '', itemsPerPage = 10) => {
    loading.value = true;
    error.value = null;
    try {
      const params = {
        q: query,
        itemsPerPage: itemsPerPage.toString(),
      };
      
      // Aggiungi tipo_voce solo se specificato
      if (tipoVoce) {
        params.tipo_voce = tipoVoce;
      }
      
      const response = await fetchApi('/api/voci-economiche', params);
      const data = extractDataFromResponse(response);
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Carica i coefficienti produzione
  const loadCoefficientiProduzione = async (query = '') => {
    if (cache.coefficientiProduzione && !query) {
      return cache.coefficientiProduzione;
    }
    
    loading.value = true;
    error.value = null;
    try {
      const response = await fetchApi('/api/coefficienti-produzione', {
        q: query,
      });
      const data = extractDataFromResponse(response);
      if (!query) {
        cache.coefficientiProduzione = data;
      }
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Carica i prodotti fotovoltaico
  const loadProdottiFotovoltaico = async (query = '', itemsPerPage = 10) => {
    if (cache.prodottiFotovoltaico && !query) {
      return cache.prodottiFotovoltaico;
    }
    
    loading.value = true;
    error.value = null;
    try {
      const response = await fetchApi('/api/prodotti-fotovoltaico', {
        q: query,
        itemsPerPage: itemsPerPage.toString(),
      });
      const data = extractDataFromResponse(response);
      if (!query) {
        cache.prodottiFotovoltaico = data;
      }
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Carica le modalità pagamento
  const loadModalitaPagamento = async () => {
    if (cache.modalitaPagamento) {
      return cache.modalitaPagamento;
    }
    
    loading.value = true;
    error.value = null;
    try {
      const response = await fetchApi('/api/modalita-pagamento');
      const data = extractDataFromResponse(response);
      cache.modalitaPagamento = data;
      return data;
    } catch (err) {
      error.value = err;
      return [];
    } finally {
      loading.value = false;
    }
  };

  // Funzione per resettare la cache
  const clearCache = () => {
    cache.productCategories = null;
    cache.tipologieTetto = null;
    cache.vociEconomiche = null;
    cache.coefficientiProduzione = null;
    cache.prodottiFotovoltaico = null;
    cache.modalitaPagamento = null;
  };

  return {
    loading,
    error,
    loadProductCategories,
    loadTipologieTetto,
    loadVociEconomiche,
    loadCoefficientiProduzione,
    loadProdottiFotovoltaico,
    loadModalitaPagamento,
    clearCache,
  };
}

