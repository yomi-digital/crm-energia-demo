<script setup>
import { watch } from 'vue';
import { VDivider } from 'vuetify/lib/components/index.mjs';

const props = defineProps({
  paperworkData: {
    type: Object,
    required: true,
    // default: () => ({
    //   id: 0,
    //   name: '',
    //   last_name: '',
    //   business_name: '',
    //   tax_code_id: '',
    //   vat_number: '',
    //   email: '',
    //   phone: '',
    //   mobile: '',
    //   ateco_code: '',
    //   pec: '',
    //   unique_code: '',
    //   category: '',
    //   address: '',
    //   region: '',
    //   province: '',
    //   city: '',
    //   zip: '',
    // }),
  },
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'submit',
  'update:isDialogVisible',
])

const paperworkDataClone = ref(structuredClone(toRaw(props.paperworkData)))

watch(props, () => {
  paperworkDataClone.value = structuredClone(toRaw(props.paperworkData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', paperworkDataClone.value)
}

const onFormReset = () => {
  paperworkDataClone.value = structuredClone(toRaw(props.paperworkData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const types = ref([
  { title: 'ENERGIA', value: 'ENERGIA' },
  { title: 'TELEFONIA', value: 'TELEFONIA' },
])

const selectableTypes = () => {
  if (paperworkDataClone.value.type === 'ENERGIA') {
    return [
      { title: 'Luce', value: 'LUCE' },
      { title: 'Gas', value: 'GAS' },
    ]
  } else if (paperworkDataClone.value.type === 'TELEFONIA') {
    return [
      { title: 'Fisso', value: 'FISSO' },
      { title: 'Mobile', value: 'MOBILE' },
      { title: 'Fisso e Mobile', value: 'FISSO_MOBILE' },
    ]
  }
}

const contractTypes = ref([
  { title: 'ALLACCIO', value: 'ALLACCIO' },
  { title: 'OTP', value: 'OTP' },
  { title: 'SUBENTRO', value: 'SUBENTRO' },
  { title: 'VOLTURA', value: 'VOLTURA' },
  { title: 'SWITCH', value: 'SWITCH' },
])

const statuses = ref([
  { title: 'ACCANTONATO', value: 'ACCANTONATO' },
  { title: 'ATTIVO', value: 'ATTIVO' },
  { title: 'IN PROVISIONING', value: 'IN PROVISIONING' },
  { title: 'INSERITO', value: 'INSERITO' },
  { title: 'INVIATO', value: 'INVIATO' },
])

// Agenti disponibili per riassegnare la pratica
const agents = ref([])

const fetchAgents = async () => {
  agents.value = []
  try {
    // Stesso endpoint del wizard: include agenti, strutture, gestione e backoffice
    const response = await $api('/agents?itemsPerPage=99999999&select=1&structures=1&gestione=1&backoffice=1')
    agents.value = response.agents.map(agent => ({
      title: [agent.name, agent.last_name].filter(Boolean).join(' '),
      value: agent.id,
    }))
  } catch (error) {
    console.error('Failed to load agents:', error)
    agents.value = []
  }
}

await fetchAgents()

const mandates = ref([]);
const { data: mandatesData, execute: fetchMandates } = await useApi('/mandates');
mandates.value = mandatesData.value.mandates.map(mandate => ({
  title: mandate.name,
  value: mandate.id
}))

// Aggiunta logica per i prodotti filtrati per brand
const products = ref([])
const isLoadingProducts = ref(false)

const fetchProducts = async (brandId) => {
  isLoadingProducts.value = true
  try {
    const url = brandId ? `/products?itemsPerPage=999999&brand=${brandId}&enabled=1` : '/products?itemsPerPage=999999&enabled=1';
    const response = await $api(url);
    products.value = response.products.map(product => ({
      title: product.name, // Modifica qui: usa product.name
      value: product.id,
    }));
  } catch (error) {
    console.error('Failed to load products:', error)
    products.value = []
  } finally {
    isLoadingProducts.value = false
  }
}

// Carica i prodotti all'apertura del dialogo, usando il brand del prodotto attuale della paperwork
watch(() => props.isDialogVisible, (isVisible) => {
  if (isVisible && props.paperworkData?.product?.brand_id) {
    fetchProducts(props.paperworkData.product.brand_id)
  } else if (isVisible) {
    // Se il brand non è disponibile, carica tutti i prodotti (o gestisci diversamente)
    fetchProducts()
  }
})

watch(() => paperworkDataClone.value.type, () => {
  paperworkDataClone.value.energy_type = ''
  // if (paperworkDataClone.value.type === 'TELEFONIA') {
  // }
})
</script>

<template>
  <VDialog
    :width="$vuetify.display.smAndDown ? 'auto' : 900"
    :model-value="props.isDialogVisible"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- Dialog close btn -->
    <DialogCloseBtn @click="dialogModelValueUpdate(false)" />

    <VCard class="pa-sm-10 pa-2">
      <VCardText>
        <!-- 👉 Title -->
        <h4 class="text-h4 text-center mb-2">
          Modifica Pratica
        </h4>
        <!-- <p class="text-body-1 text-center mb-6">
          Updating user details will receive a privacy audit.
        </p> -->

        <!-- 👉 Form -->
        <VForm
          class="mt-6"
          @submit.prevent="onFormSubmit"
        >
          <VRow>
            <!-- Selettore Agente -->
            <VCol
              cols="12"
              sm="6"
            >
              <AppAutocomplete
                v-model="paperworkDataClone.user_id"
                label="Agente"
                :items="agents"
                item-title="title"
                item-value="value"
                placeholder="Seleziona un agente"
              />
            </VCol>

            <VCol
              cols="12"
              sm="12"
            >
              <AppSelect
                v-model="paperworkDataClone.contract_type"
                label="Tipologia Pratica"
                :items="[{ title: 'Residenziale', value: 'RESIDENZIALE' }, { title: 'Business', value: 'BUSINESS' }]"
                placeholder="Seleziona un tipo"
              />
            </VCol>

            <!-- 👉 Category -->
            <VCol
              cols="12"
              md="6"
            >
              <AppSelect
                v-model="paperworkDataClone.type"
                label="Categoria"
                placeholder="Seleziona"
                :items="[{ title: 'ENERGIA', value: 'ENERGIA' }, { title: 'TELEFONIA', value: 'TELEFONIA' }]"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="paperworkDataClone.energy_type"
                label="Tipo Utenza"
                :items="selectableTypes()"
                placeholder="Seleziona un tipo di utenza"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-model="paperworkDataClone.category"
                label="Tipo Contratto"
                :items="contractTypes"
                placeholder="Seleziona un tipo contratto"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
              v-if="['SWITCH', 'VOLTURA', 'OTP'].includes(paperworkDataClone.category)"
            >
              <AppTextField
                v-model="paperworkDataClone.previous_provider"
                label="Compagnia Fornitore Uscente"
                placeholder="Enel"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppSelect
                v-show="paperworkDataClone.energy_type === 'MOBILE'"
                v-model="paperworkDataClone.mobile_type"
                label="Tipologia Mobile"
                :items="[{ title: 'MNP', value: 'MNP' }, { title: 'NIP', value: 'NIP' }]"
                placeholder="Seleziona un tipo"
              />
            </VCol>

            <VDivider class="mt-4" />

            <VCol
              cols="12"
              sm="6"
            >
              <AppTextField
                v-model="paperworkDataClone.account_pod_pdr"
                label="Account / POD / PDR"
                placeholder="IT000000000000"
              />
            </VCol>

            <VCol
              cols="12"
              sm="6"
            >
              <AppTextField
                v-model="paperworkDataClone.annual_consumption"
                label="Consumo Annuo"
                placeholder="1000"
              />
            </VCol>

            <template v-if="$can('access', 'mandates')">
              <VDivider class="mt-4" />
  
              <!-- New mandate_id field -->
              <VCol v-if="$can('access', 'mandates')" cols="12" sm="6">
                <AppSelect
                  v-model="paperworkDataClone.mandate_id"
                  label="Mandato"
                  :items="mandates"
                  placeholder="Seleziona un mandato"
                />
              </VCol>

              <!-- Campo per la selezione del Prodotto -->
              <VCol cols="12" sm="6">
                <AppAutocomplete
                  v-model="paperworkDataClone.product_id"
                  label="Prodotto"
                  :items="products"
                  item-title="title"
                  item-value="value"
                  placeholder="Seleziona un Prodotto"
                  :loading="isLoadingProducts"
                />
              </VCol>
            </template>

            <VDivider class="mt-4" />

            <!-- 👉 Submit and Cancel -->
            <VCol
              cols="12"
              class="d-flex flex-wrap justify-center gap-4"
            >
              <VBtn type="submit">
                Salva
              </VBtn>

              <VBtn
                color="secondary"
                variant="tonal"
                @click="onFormReset"
              >
                Annulla
              </VBtn>
            </VCol>
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
  </VDialog>
</template>
