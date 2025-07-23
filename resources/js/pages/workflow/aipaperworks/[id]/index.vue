<script setup>
import { useRoute, useRouter } from 'vue-router'

definePage({
  meta: {
    action: 'view',
    subject: 'aipaperworks',
  },
})

const route = useRoute()
const router = useRouter()
const id = route.params.id

const isProcessing = ref(false)

const {
  data: aiPaperwork,
  execute: fetchAIPaperwork,
} = await useApi(`/ai-paperworks/${id}`)

const products = ref([])
const isLoadingProducts = ref(false)

const fetchProducts = async () => {
  isLoadingProducts.value = true
  try {
    const response = await $api('/products?itemsPerPage=999999&enabled=1')
    products.value = response.products
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

// Fetch products on component mount
await fetchProducts()

const brands = ref([])
const isLoadingBrands = ref(false)

const fetchBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands?itemsPerPage=999999&enabled=1')
    brands.value = response.brands || []
  } catch (error) {
    console.error('Failed to load brands:', error)
  } finally {
    isLoadingBrands.value = false
  }
}

// Fetch brands on component mount
await fetchBrands()

const extractedCustomer = ref({})
const extractedPaperwork = ref({})

// Watch for changes in the API response and update the refs
watch(() => aiPaperwork.value?.ai_extracted_customer, (newVal) => {
  if (newVal) {
    try {
      extractedCustomer.value = JSON.parse(newVal)
    } catch (e) {
      extractedCustomer.value = {}
    }
  }
}, { immediate: true })

watch(() => aiPaperwork.value?.ai_extracted_paperwork, (newVal) => {
  if (newVal) {
    try {
      const parsed = JSON.parse(newVal)
      extractedPaperwork.value = parsed
      // Imposta il brand_id dall'AI paperwork se esiste
      if (aiPaperwork.value?.brand_id) {
        extractedPaperwork.value.brand_id = aiPaperwork.value.brand_id
        console.log('Set brand_id to:', aiPaperwork.value.brand_id)
      }
    } catch (e) {
      extractedPaperwork.value = {}
    }
  }
}, { immediate: true })

// Watch separato per inizializzare il brand_id dal paperwork
watch(() => aiPaperwork.value?.brand_id, (brandId) => {
  if (brandId && extractedPaperwork.value) {
    extractedPaperwork.value.brand_id = brandId
    console.log('Watch brand_id updated to:', brandId)
  }
}, { immediate: true })

const extractedText = computed({
  get() {
    return aiPaperwork.value.extracted_text || ''
  },
  set(val) {
    if (aiPaperwork.value) {
      aiPaperwork.value.extracted_text = val
    }
  }
})

const processDocument = async () => {
  isProcessing.value = true
  try {
    await $api(`/ai-paperworks/${id}/process`, {
      method: 'POST',
    })
    fetchAIPaperwork()
  } catch (error) {
    console.error('Error processing document:', error)
  } finally {
    isProcessing.value = false
  }
}

const getStatusChipColor = (status) => {
  switch (status) {
    case 0:
      return 'warning'
    case 1:
      return 'info'
    case 2:
      return 'success'
    case 5:
      return 'success'
    case 8:
      return 'error'
    case 9:
      return 'error'
    default:
      return 'error'
  }
}

const getStatusText = (status) => {
  switch (status) {
    case 0:
      return 'In attesa'
    case 1:
      return 'In elaborazione'
    case 2:
      return 'Processato'
    case 5:
      return 'Confermato'
    case 8:
      return 'Annullato'
    case 9:
      return 'Errore'
    default:
      return 'Errore'
  }
}

const basename = computed(() => {
  if (!aiPaperwork.value?.filepath) return ''
  return aiPaperwork.value.filepath.split('/').pop()
})

const downloadFile = async () => {
  try {
    const response = await $api(`/ai-paperworks/${id}/download`, {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `pratica-${id}.pdf`)
    document.body.appendChild(link)
    link.click()
    link.remove()
  } catch (error) {
    console.error('Error downloading file:', error)
  }
}

const isSaving = ref(false)
const isUpdatingEmail = ref(false)

const saveModifications = async () => {
  isSaving.value = true
  try {
    await $api(`/ai-paperworks/${id}`, {
      method: 'PUT',
      body: {
        ai_extracted_customer: extractedCustomer.value,
        ai_extracted_paperwork: extractedPaperwork.value,
        brand_id: extractedPaperwork.value.brand_id
      }
    })
    // Simple success message
    alert('Modifiche salvate')
    window.location.reload()
  } catch (error) {
    console.error('Error saving modifications:', error)
    alert('Errore durante il salvataggio delle modifiche')
  } finally {
    isSaving.value = false
  }
}

const confirmPaperwork = async () => {
  if (!extractedPaperwork.value.product_id) {
    alert('Seleziona un prodotto prima di confermare')
    return
  }

  try {
    const response = await $api(`/ai-paperworks/${id}/confirm`, {
      method: 'POST',
      body: {
        product_id: extractedPaperwork.value.product_id,
        brand_id: extractedPaperwork.value.brand_id,
        status: 5
      }
    })
    // Update local status
    if (aiPaperwork.value) {
      aiPaperwork.value.status = 5
    }
    // Redirect to the new paperwork
    router.push(`/workflow/paperworks/${response.paperwork.id}`)
  } catch (error) {
    console.error('Error confirming paperwork:', error)
    alert('Errore durante la conferma della pratica')
  }
}

const cancelPaperwork = async () => {
  try {
    await $api(`/ai-paperworks/${id}/cancel`, {
      method: 'POST',
      body: {
        status: 8
      }
    })
    // Refresh the data
    await fetchAIPaperwork()
  } catch (error) {
    console.error('Error canceling paperwork:', error)
    alert('Errore durante l\'annullamento della pratica')
  }
}

const updateEmailAndRefresh = async () => {
  if (!extractedCustomer.value.email || !extractedCustomer.value.email.trim()) {
    alert('Inserisci un\'email valida')
    return
  }
  
  isUpdatingEmail.value = true
  
  try {
    const response = await $api(`/ai-paperworks/${id}/update-email`, {
      method: 'POST',
      body: {
        email: extractedCustomer.value.email
      }
    })
    
    // Mostra messaggio di successo
    const message = response.customer_found 
      ? `Email aggiornata! Cliente esistente trovato (ID: ${response.customer_id})`
      : 'Email aggiornata! Nessun cliente esistente trovato con questa email'
    
    alert(message)
    
    // Refresh della pagina per ricaricare tutti i dati
    window.location.reload()
    
  } catch (error) {
    console.error('Error updating email:', error)
    alert('Errore durante l\'aggiornamento dell\'email')
  } finally {
    isUpdatingEmail.value = false
  }
}
</script>

<template>
  <section>
    <VCard>
      <VCardTitle class="d-flex flex-column pa-4">
        <div class="d-flex justify-space-between align-center w-100 mb-4">
          <div>
            <h2 class="text-h5 font-weight-medium">
              Dettaglio Pratica AI
              <template v-if="aiPaperwork?.user">
                <span class="text-medium-emphasis">- Caricato da 
                  <RouterLink 
                    :to="`/users/${aiPaperwork.user.id}`"
                    class="text-decoration-none"
                  >
                    {{ aiPaperwork.user.name }} {{ aiPaperwork.user.last_name }}
                  </RouterLink>
                </span>
              </template>
            </h2>
            <div v-if="basename" class="text-subtitle-2 text-medium-emphasis">{{ basename }}</div>
          </div>
          <VChip
            :color="getStatusChipColor(aiPaperwork?.status)"
            size="small"
            class="text-capitalize"
          >
            {{ getStatusText(aiPaperwork?.status) }}
          </VChip>
        </div>
        
        <div class="d-flex justify-space-between align-center w-100">
          <div class="d-flex gap-2">
            <VBtn
              color="primary"
              prepend-icon="tabler-download"
              @click="downloadFile"
            >
              Scarica PDF
            </VBtn>
            <VBtn
              v-if="aiPaperwork?.status === 2"
              color="success"
              prepend-icon="tabler-check"
              @click="confirmPaperwork"
            >
              Conferma Pratica
            </VBtn>
            <VBtn
              v-if="aiPaperwork?.status !== 8 && aiPaperwork?.status !== 5"
              color="error"
              prepend-icon="tabler-x"
              @click="cancelPaperwork"
            >
              Annulla Pratica
            </VBtn>
          </div>
          <div class="d-flex gap-2">
            <VBtn
              v-if="aiPaperwork?.status !== 5 && ![0, 1, 8, 9].includes(aiPaperwork?.status)"
              color="primary"
              prepend-icon="tabler-device-floppy"
              :loading="isSaving"
              :disabled="isSaving"
              @click="saveModifications"
            >
              {{ isSaving ? 'Salvataggio...' : 'Salva Modifiche' }}
            </VBtn>
            <VBtn
              v-if="aiPaperwork?.status === 0"
              color="primary"
              @click="processDocument"
              :loading="isProcessing"
              :disabled="isProcessing"
            >
              {{ isProcessing ? 'Elaborazione in corso...' : 'Processa Documento' }}
            </VBtn>
          </div>
        </div>
      </VCardTitle>

      <VCardText>
        <template v-if="![0, 1, 8, 9].includes(aiPaperwork?.status)">
          <VRow>
            <!-- Customer Section -->
            <VCol cols="6">
              <VForm>
                <h3 class="text-h6 mb-2">Dati Cliente Estratti</h3>
                <VDivider class="mb-4" />
                
                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.name"
                      label="Nome"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.last_name"
                      label="Cognome"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="9">
                    <AppTextField
                      v-model="extractedCustomer.email"
                      label="Email"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="3" class="d-flex align-center">
                    <VBtn
                      v-if="aiPaperwork?.status !== 5"
                      color="primary"
                      variant="outlined"
                      size="small"
                      class="mt-6"
                      @click="updateEmailAndRefresh"
                      :loading="isUpdatingEmail"
                      :disabled="!extractedCustomer.email || !extractedCustomer.email.trim()"
                    >
                      Aggiorna Email
                    </VBtn>
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <CustomerTelPhoneInput
                      v-model="extractedCustomer.phone"
                      :type="'phone'"
                      label="Telefono"
                      placeholder="Telefono fisso"
                      :readonly="aiPaperwork?.status === 5"
                      :customer-id="extractedPaperwork.customer_id"
                    />
                  </VCol>
                  <VCol cols="6">
                    <CustomerTelPhoneInput
                      v-model="extractedCustomer.mobile"
                      :type="'mobile'"
                      label="Cellulare"
                      placeholder="Cellulare"
                      :readonly="aiPaperwork?.status === 5"
                      :customer-id="extractedPaperwork.customer_id"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedCustomer.address"
                      label="Indirizzo"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.city"
                      label="Città"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.zip_code"
                      label="CAP"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.province"
                      label="Provincia"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.region"
                      label="Regione"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.tax_id_code"
                      label="Codice Fiscale"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                  <VCol cols="6">
                    <AppTextField
                      v-model="extractedCustomer.vat_number"
                      label="Partita IVA"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>
              </VForm>
            </VCol>

            <!-- Paperwork Section -->
            <VCol cols="6">
              <VForm>
                <h3 class="text-h6 mb-2">Dati Pratica Estratti</h3>
                <VDivider class="mb-4" />

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.type"
                      label="Tipo Fornitura"
                      :readonly="aiPaperwork?.status === 5"
                      :items="['ENERGIA', 'TELEFONIA']"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.product_id"
                      :items="products"
                      item-title="name"
                      item-value="id"
                      label="Prodotto"
                      placeholder="Seleziona un Prodotto"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.brand_id"
                      :items="brands"
                      item-title="name"
                      item-value="id"
                      label="Brand"
                      placeholder="Seleziona un Brand"
                      :readonly="aiPaperwork?.status === 5 || !!extractedPaperwork.product_id"
                      :loading="isLoadingBrands"
                      :class="{ 'opacity-50': aiPaperwork?.status === 5 || !!extractedPaperwork.product_id }"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.account_pod_pdr"
                      label="POD/PDR"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.category"
                      label="Categoria"
                      :readonly="aiPaperwork?.status === 5"
                      :items="['ALLACCIO', 'OTP', 'SUBENTRO', 'VOLTURA', 'SWITCH']"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppAutocomplete
                      v-model="extractedPaperwork.contract_type"
                      label="Tipo Contratto"
                      :readonly="aiPaperwork?.status === 5"
                      :items="['Residenziale', 'Business']"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.annual_consumption"
                      label="Consumo Annuo"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>

                <VRow>
                  <VCol cols="12">
                    <AppTextField
                      v-model="extractedPaperwork.previous_provider"
                      label="Fornitore Precedente"
                      :readonly="aiPaperwork?.status === 5"
                    />
                  </VCol>
                </VRow>
              </VForm>
            </VCol>
          </VRow>
        </template>
        <VRow v-else class="justify-center align-center py-8">
          <VCol cols="12" class="text-center">
            <VIcon
              :icon="aiPaperwork?.status === 0 ? 'tabler-clock' : aiPaperwork?.status === 1 ? 'tabler-loader-2' : 'tabler-x'"
              size="48"
              :color="getStatusChipColor(aiPaperwork?.status)"
              class="mb-4"
            />
            <h3 class="text-h6 mb-2">
              {{ aiPaperwork?.status === 0 ? 'Documento in attesa di elaborazione' :
                 aiPaperwork?.status === 1 ? 'Elaborazione in corso...' :
                 aiPaperwork?.status === 8 ? 'Documento annullato' :
                 'Si è verificato un errore durante l\'elaborazione' }}
            </h3>
            <p class="text-medium-emphasis">
              {{ aiPaperwork?.status === 0 ? 'Clicca su "Processa Documento" per avviare l\'elaborazione' :
                 aiPaperwork?.status === 1 ? 'Attendi il completamento dell\'elaborazione' :
                 aiPaperwork?.status === 8 ? 'Questo documento è stato annullato e non può essere modificato' :
                 'Non è possibile visualizzare o modificare i dati estratti' }}
            </p>
          </VCol>
        </VRow>
      </VCardText>
    </VCard>
  </section>
</template> 
