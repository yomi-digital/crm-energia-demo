<script setup>
const isUploading = ref(false)
const extractedText = ref(null)
const uploadedFile = ref(null)
const dropZoneRef = ref()
const products = ref([])
const isLoadingProducts = ref(false)

const emit = defineEmits(['clear-dropzone'])

// Function to check if values are different
const isDifferent = (dbValue, extractedValue) => {
  if (dbValue === null && extractedValue === null) return false
  return dbValue !== extractedValue
}

const uploadContract = async (files) => {
  if (!files.length) return
  
  isUploading.value = true
  const formData = new FormData()
  formData.append('contract', files[0])
  
  try {
    const response = await $api('/upload-contract', {
      method: 'POST',
      body: formData,
    })
    
    uploadedFile.value = {
      name: files[0].name,
      customer: response.customer,
      db_customer: response.db_customer,
      paperwork: response.paperwork,
      contract: response.contract,
    }
    
    dropZoneRef.value.clearDropzone()
  } catch (error) {
    console.error('Upload failed:', error)
  } finally {
    isUploading.value = false
  }
}

// Function to load products for search
const searchProducts = async (search) => {
  if (!search) return

  isLoadingProducts.value = true
  try {
    const response = await $api('/products/search', {
      params: { search }
    })
    products.value = response.data
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoadingProducts.value = false
  }
}

// Function to handle product selection
const onProductSelect = (product) => {
  if (!uploadedFile.value.contract) {
    uploadedFile.value.contract = {}
  }
  uploadedFile.value.contract.product = product
}
</script>

<template>
  <VCard>
    <VCardTitle>Carica contratto per creazione rapida pratica</VCardTitle>
    <VCardText>
      <!-- Wrap dropzone in a container with max-width -->
      <div> <!-- or you can use class="w-25" for 25% width -->
        <DropZoneContracts
          @dropped="uploadContract"
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
      </div>
      
      <!-- Summary section after successful upload -->
      <div v-if="uploadedFile" class="mt-4">
        <VCard>
          <VCardTitle>
            File Caricato: {{ uploadedFile.name }}
          </VCardTitle>
          
          <VCardText>
            <VRow>
              <!-- Customer Section -->
              <VCol cols="6">
                <VForm>
                  <h3 class="text-h6 mb-2">Dati Cliente</h3>
                  <VDivider class="mb-4" />
                  
                  <VRow>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.name"
                        label="Nome"
                        :color="isDifferent(uploadedFile.db_customer.name, uploadedFile.customer.nome) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.name, uploadedFile.customer.nome) ? `Originale: ${uploadedFile.customer.nome}` : ''"
                        persistent-hint
                      />
                    </VCol>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.last_name"
                        label="Cognome"
                        :color="isDifferent(uploadedFile.db_customer.last_name, uploadedFile.customer.cognome) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.last_name, uploadedFile.customer.cognome) ? `Originale: ${uploadedFile.customer.cognome}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.db_customer.email"
                        label="Email"
                        :color="isDifferent(uploadedFile.db_customer.email, uploadedFile.customer.email) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.email, uploadedFile.customer.email) ? `Originale: ${uploadedFile.customer.email}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.phone"
                        label="Telefono"
                        :color="isDifferent(uploadedFile.db_customer.phone, uploadedFile.customer.telefono) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.phone, uploadedFile.customer.telefono) ? `Originale: ${uploadedFile.customer.telefono}` : ''"
                        persistent-hint
                      />
                    </VCol>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.mobile"
                        label="Cellulare"
                        :color="isDifferent(uploadedFile.db_customer.mobile, uploadedFile.customer.mobile) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.mobile, uploadedFile.customer.mobile) ? `Originale: ${uploadedFile.customer.mobile}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.db_customer.address"
                        label="Indirizzo"
                        :color="isDifferent(uploadedFile.db_customer.address, uploadedFile.customer.indirizzo) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.address, uploadedFile.customer.indirizzo) ? `Originale: ${uploadedFile.customer.indirizzo}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.city"
                        label="Città"
                        :color="isDifferent(uploadedFile.db_customer.city, uploadedFile.customer.citta) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.city, uploadedFile.customer.citta) ? `Originale: ${uploadedFile.customer.citta}` : ''"
                        persistent-hint
                      />
                    </VCol>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.zip_code"
                        label="CAP"
                        :color="isDifferent(uploadedFile.db_customer.zip_code, uploadedFile.customer.cap) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.zip_code, uploadedFile.customer.cap) ? `Originale: ${uploadedFile.customer.cap}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.province"
                        label="Provincia"
                        :color="isDifferent(uploadedFile.db_customer.province, uploadedFile.customer.provincia) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.province, uploadedFile.customer.provincia) ? `Originale: ${uploadedFile.customer.provincia}` : ''"
                        persistent-hint
                      />
                    </VCol>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.region"
                        label="Regione"
                        :color="isDifferent(uploadedFile.db_customer.region, uploadedFile.customer.regione) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.region, uploadedFile.customer.regione) ? `Originale: ${uploadedFile.customer.regione}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.tax_id_code"
                        label="Codice Fiscale"
                        :color="isDifferent(uploadedFile.db_customer.tax_id_code, uploadedFile.customer.codice_fiscale) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.tax_id_code, uploadedFile.customer.codice_fiscale) ? `Originale: ${uploadedFile.customer.codice_fiscale}` : ''"
                        persistent-hint
                      />
                    </VCol>
                    <VCol cols="6">
                      <VTextField
                        v-model="uploadedFile.db_customer.vat_number"
                        label="Partita IVA"
                        :color="isDifferent(uploadedFile.db_customer.vat_number, uploadedFile.customer.partita_iva) ? 'warning' : undefined"
                        :hint="isDifferent(uploadedFile.db_customer.vat_number, uploadedFile.customer.partita_iva) ? `Originale: ${uploadedFile.customer.partita_iva}` : ''"
                        persistent-hint
                      />
                    </VCol>
                  </VRow>
                </VForm>
              </VCol>

              <!-- Paperwork Section -->
              <VCol cols="6">
                <VForm>
                  <h3 class="text-h6 mb-2">Dati Pratica</h3>
                  <VDivider class="mb-4" />
                  
                  <!-- Product Section -->
                  <VRow>
                    <VCol cols="12">
                      <!-- Show product name if exists -->
                      <div v-if="uploadedFile.contract?.product" class="mb-4">
                        <div class="text-subtitle-2 text-medium-emphasis mb-1">Prodotto</div>
                        <div class="text-h6">{{ uploadedFile.contract.product.name }}</div>
                      </div>
                      
                      <!-- Show product selector if no product -->
                      <VAutocomplete
                        v-else
                        v-model="uploadedFile.contract.product"
                        :items="products"
                        :loading="isLoadingProducts"
                        label="Seleziona Prodotto"
                        placeholder="Cerca prodotto..."
                        item-title="name"
                        item-value="id"
                        class="mb-4"
                        @update:search="searchProducts"
                        @update:model-value="onProductSelect"
                      >
                        <template v-slot:item="{ props, item }">
                          <VListItem v-bind="props">
                            <VListItemTitle>{{ item.raw.name }}</VListItemTitle>
                            <VListItemSubtitle>
                              Sconto: {{ item.raw.discount_percent }}% | Creato il: {{ item.raw.created_at }}
                            </VListItemSubtitle>
                          </VListItem>
                        </template>
                      </VAutocomplete>
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.type"
                        label="Tipo Fornitura"
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.account_pod_pdr"
                        label="POD/PDR"
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.category"
                        label="Categoria"
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.contract_type"
                        label="Tipo Contratto"
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.annual_consumption"
                        label="Consumo Annuo"
                      />
                    </VCol>
                  </VRow>

                  <VRow>
                    <VCol cols="12">
                      <VTextField
                        v-model="uploadedFile.paperwork.previous_provider"
                        label="Fornitore Precedente"
                      />
                    </VCol>
                  </VRow>
                </VForm>
              </VCol>
            </VRow>
          </VCardText>
        </VCard>
      </div>
    </VCardText>
  </VCard>
</template>

<style lang="scss" scoped>
// Optional: Add some responsive breakpoints if needed
@media (max-width: 600px) {
  .dropzone-container {
    max-width: 100%;
  }
}
</style> 
