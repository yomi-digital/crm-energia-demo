<script setup>
import DropZoneContracts from '@/components/DropZoneContracts.vue'
import PopupAICreationWizardNotification from '@/components/dialogs/PopupAICreationWizardNotification.vue'
import { onMounted, ref, watch } from 'vue'

const dialog = ref(false)
const isUploading = ref(false)
const dropZoneRef = ref()
const brands = ref([])
const selectedBrand = ref(null)
const agents = ref([])
const selectedAgentId = ref(null)
const searchBrand = ref()
const isLoadingBrands = ref(false)
const isLoadingAgents = ref(false)
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// Controlla se l'utente è backoffice
const loggedInUser = useCookie('userData').value
const isBackoffice = loggedInUser?.roles?.some(role => role.name === 'backoffice')

// Controlla se l'utente può vedere il pulsante AI (admin, backoffice, struttura, agente)
const canSeeAI = loggedInUser?.roles?.some(role => 
  ['gestione', 'amministrazione', 'backoffice', 'struttura', 'agente'].includes(role.name)
)

// Controlla se l'utente può selezionare l'agente (gestione, struttura, backoffice)
const canSelectAgent = loggedInUser?.roles?.some(role => 
  ['gestione', 'struttura', 'backoffice'].includes(role.name)
)

// Controlla se l'utente è agente (per mostrare il popup di notifica)
const isAgente = loggedInUser?.roles?.some(role => role.name === 'agente')

// Popup di notifica per agenti
const showNotification = ref(false)

// Fetch brands with search functionality
const fetchBrands = async (query = '') => {
  isLoadingBrands.value = true
  try {
    const searchParam = query ? `&q=${encodeURIComponent(query)}` : ''
    const response = await $api(`/brands/personal?itemsPerPage=999999&enabled=1${searchParam}`)
    brands.value = response.brands
  } catch (error) {
    console.error('Failed to load brands:', error)
    alert.value = {
      show: true,
      type: 'error',
      message: 'Errore nel caricamento dei brand'
    }
  } finally {
    isLoadingBrands.value = false
  }
}

// Fetch agents filtrati per brand e ruoli
// - Se l'utente è backoffice: mostra solo gli AGENTI (ruolo "agente")
// - Altri ruoli (gestione, struttura, ecc.): vedono agenti + backoffice + strutture + gestione
const fetchAgents = async (brandId = null) => {
  isLoadingAgents.value = true
  agents.value = []
  try {
    let url = '/agents?itemsPerPage=99999999&select=1'

    // Solo se NON è un backoffice, includiamo anche altri ruoli
    if (!isBackoffice) {
      url += '&structures=1&gestione=1&backoffice=1'
    }

    if (brandId) {
      url += `&brand_id=${brandId}`
    }

    const response = await $api(url)
    agents.value = response.agents.map(agent => ({
      title: [agent.name, agent.last_name].filter(Boolean).join(' '),
      value: agent.id,
    }))
  } catch (error) {
    console.error('Failed to load agents:', error)
    agents.value = []
  } finally {
    isLoadingAgents.value = false
  }
}

// Watch for search input changes
watch(searchBrand, (query) => {
  if (query && query !== selectedBrand.value) {
    fetchBrands(query)
  } else if (!query) {
    // Se la ricerca è vuota, ricarica tutti i brands
    fetchBrands()
  }
})

onMounted(() => {
  fetchBrands()
})

// Function to close dialog and reset form
const closeDialog = () => {
  dialog.value = false
  alert.value.show = false
  selectedBrand.value = null
  selectedAgentId.value = null
  searchBrand.value = ''
  // Ricarica tutti i brands quando si chiude il dialog
  fetchBrands()
}

// Quando cambia il brand, resetta l'agente e ricarica gli agenti per quel brand (solo se può selezionare l'agente)
watch(selectedBrand, brandId => {
  if (canSelectAgent) {
    selectedAgentId.value = null
    if (brandId) {
      fetchAgents(brandId)
    } else {
      agents.value = []
    }
  }
})

const uploadContract = async (files) => {
  if (!files.length) return
  
  // Validazione brand obbligatorio
  if (!selectedBrand.value) {
    alert.value = {
      show: true,
      type: 'warning',
      message: 'Seleziona un brand prima di caricare il contratto'
    }
    return
  }

  // Validazione agente obbligatorio solo per gestione, struttura e backoffice
  if (canSelectAgent && !selectedAgentId.value) {
    alert.value = {
      show: true,
      type: 'warning',
      message: 'Seleziona un agente prima di caricare il contratto',
    }
    return
  }
  
  isUploading.value = true
  alert.value.show = false
  
  const formData = new FormData()
  formData.append('contract', files[0])
  formData.append('brand_id', selectedBrand.value)
  // Invia user_id solo se l'utente può selezionare l'agente e ha selezionato un agente, altrimenti il backend usa auth()->id()
  if (canSelectAgent && selectedAgentId.value) {
    formData.append('user_id', selectedAgentId.value)
  }
  
  try {
    const response = await $api('/upload-contract', {
      method: 'POST',
      body: formData,
    })

    if (!response.id) {
      throw new Error('Upload failed')
    }

    // Show success message
    alert.value = {
      show: true,
      type: 'success',
      message: 'File caricato con successo!'
    }
    
    // Mostra popup di notifica solo agli agenti
    if (isAgente) {
      showNotification.value = true
    }
    
    // Clear dropzone and reset form after a short delay
    dropZoneRef.value.clearDropzone()
    setTimeout(() => {
      closeDialog()
    }, 1500)
    
  } catch (error) {
    console.error('Upload failed:', error)
    alert.value = {
      show: true,
      type: 'error',
      message: 'Errore durante il caricamento del file'
    }
  } finally {
    isUploading.value = false
  }
}
</script>

<template>
  <div>
    <IconBtn 
      v-if="canSeeAI"
      @click="dialog = true" 
      id="ai-contract-upload-btn"
    >
      <span class="font-weight-bold">AI</span>
    </IconBtn>

    <VDialog
      v-model="dialog"
      width="600"
    >
      <VCard>
        <VCardTitle>Carica Contratto con AI</VCardTitle>
        <VCardText>
          <VAlert
            v-if="alert.show"
            :type="alert.type"
            :text="alert.message"
            class="mb-4"
          />

          <p class="text-body-1 mb-4">
            Carica il contratto completo di documenti di identità e bollette precedenti.<br>L'AI analizzera' il file e caricherà i dati nel sistema.
          </p>

          <SearchBrand
            v-model="selectedBrand"
            v-model:search="searchBrand"
            item-title="name"
            item-value="id"
            :items="brands"
            label="Seleziona Brand *"
            placeholder="Scegli il brand per questa pratica"
            :loading="isLoadingBrands"
            :disabled="isUploading"
            :error="!selectedBrand && alert.show && alert.type === 'warning'"
            class="mb-4"
            clearable
            required
          />

          <!-- Selettore Agente - visibile solo per gestione, struttura e backoffice -->
          <template v-if="canSelectAgent">
            <AppAutocomplete
              v-model="selectedAgentId"
              label="Agente *"
              :items="agents"
              item-title="title"
              item-value="value"
              placeholder="Seleziona un agente"
              :loading="isLoadingAgents"
              :disabled="isUploading || !selectedBrand"
              :error="!selectedAgentId && alert.show && alert.type === 'warning'"
              class="mb-1"
              clearable
              required
            />
            <p
              v-if="selectedBrand"
              class="text-caption text-medium-emphasis mb-4"
            >
              Sono mostrati solo gli agenti abilitati per il brand selezionato (flusso invertito rispetto a Crea pratica: qui si sceglie prima il brand).
            </p>
            <p
              v-else
              class="text-caption text-medium-emphasis mb-4"
            >
              Seleziona prima un brand per vedere gli agenti disponibili.
            </p>
          </template>

          <DropZoneContracts
            @dropped="uploadContract"
            @error="(msg) => alert = { show: true, type: 'warning', message: msg }"
            :disabled="isUploading || !selectedBrand || (canSelectAgent && !selectedAgentId)"
            accept=".pdf"
            single-file
            ref="dropZoneRef"
          />
          
          <VProgressLinear
            v-if="isUploading"
            indeterminate
            class="mt-4"
          />
        </VCardText>
        
        <VCardActions>
          <VSpacer />
          <VBtn
            color="primary"
            variant="text"
            @click="closeDialog"
          >
            Chiudi
          </VBtn>
        </VCardActions>
      </VCard>
    </VDialog>

    <!-- Popup di notifica per agenti -->
    <PopupAICreationWizardNotification
      v-model="showNotification"
    />
  </div>
</template> 
