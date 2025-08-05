<template>
  <VCard
    class="ai-paperwork-transfer-strip"
    color="orange-lighten-4"
    variant="tonal"
  >
    <VCardText class="py-3">
      <div class="d-flex align-center justify-space-between">
        <div class="d-flex align-center">
          <VIcon
            icon="tabler-transfer"
            color="orange-darken-2"
            class="me-3"
            size="24"
          />
          <span class="text-orange-darken-2 font-weight-medium">
            Vuoi trasferire la pratica ad un altro backofficer?
          </span>
        </div>
        
        <VBtn
          color="orange"
          variant="elevated"
          size="small"
          :loading="isTransferring"
          @click="handleTransfer"
        >
          <VIcon
            icon="tabler-send"
            start
            size="16"
          />
          Trasferisci
        </VBtn>
      </div>
    </VCardText>
  </VCard>

  <!-- Modal di conferma trasferimento -->
  <AIPaperworkTransferModal
    v-model:is-visible="isModalVisible"
    :ai-paperwork-id="aiPaperworkId"
    :current-brand-id="currentBrandId"
    :current-brand-name="currentBrandName"
    :is-loading-current-brand="isLoadingCurrentBrand"
    @confirm="handleTransferConfirm"
  />

  <!-- Modal di risultato trasferimento -->
  <AIPaperworkTransferModalResult
    v-model="showResultModal"
    :success="transferResult.success"
    :original-brand-id="transferResult.originalBrandId"
    :destination-brand-id="transferResult.destinationBrandId"
    :original-brand-name="currentBrandName"
    :destination-brand-name="destinationBrandName"
    :ai-paperwork-id="aiPaperworkId"
    :error-message="transferResult.errorMessage"
    @close="onResultModalClose"
  />
</template>

<script setup>
import AIPaperworkTransferModal from '@/components/AIPaperworkTransferModal.vue'
import AIPaperworkTransferModalResult from '@/components/AIPaperworkTransferModalResult.vue'

const router = useRouter()

const props = defineProps({
  aiPaperworkId: {
    type: [String, Number],
    required: true,
  },
  currentBrandId: {
    type: [String, Number],
    required: false,
    default: null,
  },
  redirectUrl: {
    type: String,
    required: false,
    default: '/workflow/aipaperworks',
  },
})

const emit = defineEmits(['onTransferConfirmed', 'onTransferSuccess', 'onTransferCompleted'])

// Controlla se l'utente è admin
const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser.roles.some(role => role.name === 'gestione' || role.name === 'amministrazione')

// Controllo visibilità modals
const isModalVisible = ref(false)
const showResultModal = ref(false)
const isTransferring = ref(false)

// Gestione nomi brand
const currentBrandName = ref('')
const destinationBrandName = ref('')
const isLoadingCurrentBrand = ref(false)

// Risultato del trasferimento
const transferResult = ref({
  success: false,
  originalBrandId: null,
  destinationBrandId: null,
  errorMessage: '',
})

// Funzione per ottenere il nome del brand corrente
const fetchCurrentBrandName = async () => {
  if (!props.currentBrandId) return
  
  isLoadingCurrentBrand.value = true
  try {
    const response = await $api(`/brands/${props.currentBrandId}`)
    currentBrandName.value = response.name
  } catch (error) {
    console.error('Errore nel caricamento del brand corrente:', error)
    currentBrandName.value = `Brand ID: ${props.currentBrandId}`
  } finally {
    isLoadingCurrentBrand.value = false
  }
}

// Funzione per ottenere il nome del brand di destinazione
const fetchDestinationBrandName = async (brandId) => {
  try {
    const response = await $api(`/brands/${brandId}`)
    destinationBrandName.value = response.name
    return response.name
  } catch (error) {
    console.error('Errore nel caricamento del brand di destinazione:', error)
    const fallback = `Brand ID: ${brandId}`
    destinationBrandName.value = fallback
    return fallback
  }
}

const handleTransfer = () => {
  fetchCurrentBrandName() // Carica il nome del brand corrente quando si apre il modal
  isModalVisible.value = true
}

const handleTransferConfirm = async (transferData) => {
  isTransferring.value = true
  isModalVisible.value = false
  
  try {
    const response = await $api(`/ai-paperworks/${props.aiPaperworkId}/transfer`, {
      method: 'POST',
      body: {
        brand_id: transferData.brandId,
      },
    })

    // Ottieni il nome del brand di destinazione
    await fetchDestinationBrandName(response.new_brand_id)

    // Successo
    transferResult.value = {
      success: true,
      originalBrandId: response.previous_brand_id || props.currentBrandId,
      destinationBrandId: response.new_brand_id,
      errorMessage: '',
    }

    showResultModal.value = true
    emit('onTransferSuccess', {
      aiPaperworkId: props.aiPaperworkId,
      previousBrandId: response.previous_brand_id,
      newBrandId: response.new_brand_id,
    })

  } catch (error) {
    console.error('Transfer failed:', error)
    
    // Anche in caso di errore, ottieni il nome del brand di destinazione per il modal
    await fetchDestinationBrandName(transferData.brandId)
    
    // Errore
    transferResult.value = {
      success: false,
      originalBrandId: props.currentBrandId,
      destinationBrandId: transferData.brandId,
      errorMessage: error.data?.error || error.message || 'Si è verificato un errore durante il trasferimento',
    }

    showResultModal.value = true
  } finally {
    isTransferring.value = false
  }

  emit('onTransferConfirmed', transferData)
}

const onResultModalClose = () => {
  showResultModal.value = false
  
  // Se il trasferimento è riuscito
  if (transferResult.value.success) {
    if (isAdmin) {
      // Se sei admin, emetti evento per far ricaricare i dati della pagina
      emit('onTransferCompleted', { shouldReload: true })
    } else {
      // Se non sei admin, vai alla lista delle AI paperworks perché non puoi più vedere questo brand
      router.push(props.redirectUrl)
    }
  }
  
  // Reset del risultato
  transferResult.value = {
    success: false,
    originalBrandId: null,
    destinationBrandId: null,
    errorMessage: '',
  }
}
</script>

<style scoped>
.ai-paperwork-transfer-strip {
  border-left: 4px solid rgb(var(--v-theme-orange));
}
</style>
