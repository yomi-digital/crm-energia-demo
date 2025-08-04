<script setup>
import DropZoneContracts from '@/components/DropZoneContracts.vue'
import PopupAICreationWizardNotification from '@/components/dialogs/PopupAICreationWizardNotification.vue'
import { onMounted, ref } from 'vue'

const dialog = ref(false)
const isUploading = ref(false)
const dropZoneRef = ref()
const brands = ref([])
const selectedBrand = ref(null)
const isLoadingBrands = ref(false)
const alert = ref({
  show: false,
  type: 'success',
  message: ''
})

// Controlla se l'utente è backoffice
const loggedInUser = useCookie('userData').value
const isBackoffice = loggedInUser.roles.some(role => role.name === 'backoffice')

// Controlla se l'utente è agente (per mostrare il popup di notifica)
const isAgente = loggedInUser.roles.some(role => role.name === 'agente')

// Popup di notifica per agenti
const showNotification = ref(false)

// Fetch brands on component mount
const fetchBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands/personal?itemsPerPage=999999&enabled=1')
    brands.value = response.brands || []
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

onMounted(() => {
  fetchBrands()
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
  
  isUploading.value = true
  alert.value.show = false
  
  const formData = new FormData()
  formData.append('contract', files[0])
  formData.append('brand_id', selectedBrand.value)
  
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
      dialog.value = false
      alert.value.show = false
      selectedBrand.value = null // Reset brand selection
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
      v-if="!isBackoffice"
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

          <VSelect
            v-model="selectedBrand"
            :items="brands"
            item-title="name"
            item-value="id"
            label="Seleziona Brand *"
            placeholder="Scegli il brand per questa pratica"
            :loading="isLoadingBrands"
            :disabled="isUploading"
            :error="!selectedBrand && alert.show && alert.type === 'warning'"
            class="mb-4"
            outlined
            required
          />

          <DropZoneContracts
            @dropped="uploadContract"
            @error="(msg) => alert = { show: true, type: 'warning', message: msg }"
            :disabled="isUploading"
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
            @click="dialog = false"
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
