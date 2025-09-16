<template>
  <VDialog
    :model-value="isVisible"
    @update:model-value="$emit('update:isVisible', $event)"
    max-width="600"
    persistent
  >
    <VCard>
      <VCardTitle class="pa-4">
        <div class="d-flex align-center">
          <VIcon
            icon="tabler-transfer"
            color="warning"
            class="me-3"
            size="24"
          />
          <span class="text-h6">Stai per trasferire la pratica</span>
        </div>
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-4">
        <VAlert
          v-if="!isAdmin"
          type="warning"
          variant="tonal"
          class="mb-4"
        >
          <VAlertTitle>Attenzione</VAlertTitle>
          <p class="mb-0">
            Qui in basso potrebbero esserci brands a cui non sei registrato. 
            Se cambi e trasferisci potresti non essere più in grado di lavorare questa pratica AI.
          </p>
        </VAlert>

        <div class="text-body-2 text-medium-emphasis mb-4">
          <strong>Pratica AI ID:</strong> {{ aiPaperworkId }}
        </div>

        <!-- Select per brand not-personal -->
        <VSelect
          v-model="selectedBrand"
          :items="brands"
          label="Seleziona Brand di Destinazione"
          placeholder="Scegli un brand..."
          :loading="isLoadingBrands"
          :disabled="isLoadingBrands"
          variant="outlined"
          density="comfortable"
          clearable
        />

        <!-- Preview del trasferimento quando è selezionato un brand -->
        <div v-if="selectedBrand" class="transfer-preview mt-4 pa-3 bg-grey-lighten-4 rounded">
          <p class="text-caption text-grey-darken-1 mb-2">Anteprima trasferimento:</p>
          <div class="d-flex align-center justify-center gap-2">
            <VChip
              size="small"
              color="warning"
              variant="tonal"
              :loading="isLoadingCurrentBrand"
            >
              {{ currentBrandName || `Brand ID: ${currentBrandId}` || 'N/A' }}
            </VChip>
            
            <VIcon icon="tabler-arrow-right" size="16" />
            
            <VChip
              size="small"
              color="success"
              variant="tonal"
            >
              {{ getSelectedBrandName() }}
            </VChip>
          </div>
        </div>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-4">
        <VSpacer />
        
        <VBtn
          variant="text"
          @click="$emit('update:isVisible', false)"
        >
          Annulla
        </VBtn>
        
        <VBtn
          v-if="selectedBrand"
          color="warning"
          variant="elevated"
          @click="handleConfirmTransfer"
        >
          <VIcon
            icon="tabler-send"
            start
            size="16"
          />
          Conferma Trasferimento
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false,
  },
  aiPaperworkId: {
    type: [String, Number],
    required: true,
  },
  currentBrandId: {
    type: [String, Number],
    required: false,
    default: null,
  },
  currentBrandName: {
    type: String,
    required: false,
    default: '',
  },
  isLoadingCurrentBrand: {
    type: Boolean,
    required: false,
    default: false,
  },
})

const emit = defineEmits(['update:isVisible', 'confirm'])

// Controlla se l'utente è admin
const loggedInUser = useCookie('userData').value
const isAdmin = loggedInUser?.roles?.some(role => role.name === 'gestione' || role.name === 'amministrazione')

// Gestione brand not-personal
const selectedBrand = ref('')
const brands = ref([])
const isLoadingBrands = ref(false)



const fetchNotPersonalBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands/not-personal?itemsPerPage=999999&enabled=1')
    brands.value = response.brands.map(brand => ({
      title: brand.name,
      value: brand.id,
    }))
  } catch (error) {
    alert('Impossibile caricare i brand di trasferimento')
    console.error(error)
    brands.value = []
  } finally {
    isLoadingBrands.value = false
  }
}



// Carica i brand quando il modal diventa visibile
watch(() => props.isVisible, (newVal) => {
  if (newVal) {
    fetchNotPersonalBrands()
  } else {
    // Reset quando si chiude il modal
    selectedBrand.value = ''
  }
})

const getSelectedBrandName = () => {
  if (!selectedBrand.value) return ''
  const brand = brands.value.find(b => b.value === selectedBrand.value)
  return brand ? brand.title : `Brand ID: ${selectedBrand.value}`
}



const handleConfirmTransfer = () => {
  if (selectedBrand.value) {
    emit('confirm', {
      aiPaperworkId: props.aiPaperworkId,
      brandId: selectedBrand.value
    })
    emit('update:isVisible', false)
  }
}
</script>

<style scoped>
.transfer-preview {
  border-left: 4px solid rgb(var(--v-theme-success));
}
</style>
