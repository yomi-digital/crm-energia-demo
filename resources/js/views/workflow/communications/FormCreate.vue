<script setup>
import SearchBrand from '@/components/SearchBrand.vue';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
  communication: {
    type: Object,
    default: () => ({}),
  },
})

const title = ref(props.communication.title || '')
const subject = ref(props.communication.subject || '')
const body = ref(typeof props.communication.body === 'string' ? props.communication.body : '')
const sendEmail = ref(false)
const documents = ref([])
const refForm = ref()
const isSaving = ref(false)
const showSnackbar = ref(false)

// Brand multipli
const initialBrandIdsFromBrands = Array.isArray(props.communication.brands)
  ? props.communication.brands.map(brand => brand.id)
  : []

const selectedBrandIds = ref(
  props.communication.brand_ids && props.communication.brand_ids.length
    ? (Array.isArray(props.communication.brand_ids) ? props.communication.brand_ids : [props.communication.brand_ids])
    : initialBrandIdsFromBrands
)
const brands = ref([])
const isLoadingBrands = ref(false)
const newBrandToAdd = ref(null)

// Carica brand abilitati
const fetchBrands = async () => {
  isLoadingBrands.value = true
  try {
    const response = await $api('/brands/personal?itemsPerPage=999999&enabled=1')
    brands.value = response.brands
  } catch (error) {
    console.error('Failed to load brands:', error)
  } finally {
    isLoadingBrands.value = false
  }
}

onMounted(() => {
  fetchBrands()
})

// Aggiungi brand selezionato
watch(newBrandToAdd, (brandId) => {
  if (brandId && !selectedBrandIds.value.includes(brandId)) {
    selectedBrandIds.value.push(brandId)
    newBrandToAdd.value = null
  }
})

// Rimuovi brand
const removeBrand = (brandId) => {
  const index = selectedBrandIds.value.indexOf(brandId)
  if (index > -1) {
    selectedBrandIds.value.splice(index, 1)
  }
}

// Trova nome brand
const getBrandName = (brandId) => {
  const brand = brands.value.find(b => b.id === brandId)
  return brand ? brand.name : ''
}

// Verifica se tutti i campi obbligatori sono compilati
const isFormValid = computed(() => {
  const titleValid = title.value && title.value.trim().length > 0
  const subjectValid = subject.value && subject.value.trim().length > 0
  // Rimuove i tag HTML per verificare se c'è contenuto reale
  const bodyTextOnly = body.value ? body.value.replace(/<[^>]*>/g, '').trim() : ''
  const bodyHasContent = bodyTextOnly.length > 0
  const hasBrands = selectedBrandIds.value.length > 0
  
  return titleValid && subjectValid && bodyHasContent && hasBrands
})

const emit = defineEmits([
  'communicationData',
])

const downloadFile = async (communicationId, documentId) => {
  try {
    const response = await $api(`/communications/${communicationId}/documents/${documentId}/download`)
    if (response.downloadUrl) {
      window.open(response.downloadUrl, '_blank')
    }
  } catch (e) {
    console.error(e)
  }
}

const createCommunication = async () => {
  // Validazione: almeno un brand deve essere selezionato
  if (selectedBrandIds.value.length === 0) {
    return
  }

  isSaving.value = true
  try {
    const formData = new FormData()

    formData.append('title', title.value)
    formData.append('subject', subject.value)
    formData.append('body', body.value)
    formData.append('send_email', sendEmail.value ? '1' : '0')

    // Aggiungi brand_ids
    selectedBrandIds.value.forEach(brandId => {
      formData.append('brand_ids[]', brandId)
    })

    documents.value.forEach(file => {
      formData.append('documents[]', file)
    })

    if (props.communication.id) {
      formData.append('_method', 'PUT')

      const data = await $api(`/communications/${ props.communication.id }`, {
        method: 'POST',
        body: formData,
      })

      showSnackbar.value = true
      isSaving.value = false
      
      setTimeout(() => {
        emit('communicationData', data)
      }, 1000)
    } else {
      const data = await $api('/communications', {
        method: 'POST',
        body: formData,
      })
      
      showSnackbar.value = true
      isSaving.value = false

      setTimeout(() => {
        emit('communicationData', data)
      }, 1000)
    }
  } catch (err) {
    isSaving.value = false
    console.error(err)
  }
}
</script>

<template>
  <VForm ref="refForm" @submit.prevent="createCommunication">
    <VRow>
      <!-- 👉 Title -->
      <VCol
        cols="12"
        md="9"
      >
        <AppTextField
          v-model="title"
          label="Titolo"
          placeholder="Titolo"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Subject -->
      <VCol
        cols="12"
        md="9"
      >
        <AppTextField
          v-model="subject"
          label="Oggetto"
          placeholder="Oggetto"
          :rules="[requiredValidator]"
        />
      </VCol>

      <!-- 👉 Brand -->
      <VCol
        cols="12"
        md="9"
      >
        <VLabel class="mb-2">Brand *</VLabel>
        
        <!-- Brand selezionati come chip -->
        <div
          v-if="selectedBrandIds.length > 0"
          class="d-flex flex-wrap gap-2 mb-3"
        >
          <VChip
            v-for="brandId in selectedBrandIds"
            :key="brandId"
            closable
            @click:close="removeBrand(brandId)"
          >
            {{ getBrandName(brandId) }}
          </VChip>
        </div>

        <!-- Campo per aggiungere nuovo brand -->
        <SearchBrand
          v-model="newBrandToAdd"
          label="Aggiungi Brand"
          placeholder="Seleziona un brand da aggiungere"
          :items="brands.filter(b => !selectedBrandIds.includes(b.id))"
          :loading="isLoadingBrands"
          :error="selectedBrandIds.length === 0"
          :select-all="true"
          item-title="name"
          item-value="id"
          @select-all="(allBrandIds) => {
            allBrandIds.forEach(brandId => {
              if (!selectedBrandIds.includes(brandId)) {
                selectedBrandIds.push(brandId)
              }
            })
            newBrandToAdd = null
          }"
          @deselect-all="() => {
            selectedBrandIds = []
            newBrandToAdd = null
          }"
        />

        <div
          v-if="selectedBrandIds.length === 0"
          class="text-error text-caption mt-1"
        >
          Seleziona almeno un brand
        </div>
      </VCol>

      <!-- 👉 Body -->
      <VCol
        cols="12"
        md="9"
      >
        <AppEditor
          v-model="body"
          label="Contenuto"
          placeholder="Contenuto"
          :rules="[requiredValidator]"
          :editor-options="{
            placeholder: 'Contenuto',
            modules: {
              toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['clean']
              ]
            }
          }"
        />
      </VCol>

      <VCol
        cols="12"
        md="9"
      >
        <VFileInput
          v-model="documents"
          label="Allegati"
          placeholder="Seleziona file"
          multiple
          prepend-icon="tabler-paperclip"
        />

        <!-- Existing documents -->
        <VList v-if="props.communication.documents && props.communication.documents.length" class="mt-2 border rounded">
          <VListSubheader>Allegati esistenti</VListSubheader>
          <VListItem
            v-for="doc in props.communication.documents"
            :key="doc.id"
            :title="doc.name"
            prepend-icon="tabler-file"
          >
            <template #append>
              <VBtn
                icon="tabler-download"
                variant="text"
                color="primary"
                @click.prevent="downloadFile(props.communication.id, doc.id)"
              />
            </template>
          </VListItem>
        </VList>
      </VCol>

      <!-- Add a checkbox to send an email -->
      <VCol
        cols="12"
        md="9"
      >
        <VCheckbox v-model="sendEmail" label="Invia email" />
      </VCol>

      <VCol
        cols="12"
        class="d-flex gap-4"
      >
        <VBtn type="submit" :disabled="isSaving || !isFormValid">
          {{ props.communication.id ? 'Salva' : 'Crea' }}
        </VBtn>

        <VBtn
          v-if="! props.communication.id"
          type="reset"
          color="secondary"
          variant="tonal"
        >
          Reset
        </VBtn>
      </VCol>
    </VRow>
  </VForm>

  <VSnackbar
    v-model="showSnackbar"
    timeout="2000"
    location="top"
    :color="props.communication.id ? 'success' : 'success'"
  >
    {{ props.communication.id ? 'Comunicazione aggiornata con successo' : 'Comunicazione creata con successo' }}
  </VSnackbar>
</template>
