<script setup>
definePage({
  meta: {
    action: 'create',
    subject: 'paperworks',
  },
})
import { $api } from '@/utils/api';
import PaperworkAgent from '@/views/workflow/paperworks/PaperworkAgent.vue';
import PaperworkCustomer from '@/views/workflow/paperworks/PaperworkCustomer.vue';
import PaperworkProduct from '@/views/workflow/paperworks/PaperworkProduct.vue';
import PaperworkReviewComplete from '@/views/workflow/paperworks/PaperworkReviewComplete.vue';
import CreatePaperworkType from '@/views/workflow/paperworks/PaperworkType.vue';

const route = useRoute('workflow-paperworks-create-wizard')

const isPaperworkCreated = ref(false)
const isPaperworkCreatedDialogVisible = ref(false)

const createPaperworkSteps = [
  {
    title: 'Agente',
    subtitle: 'Seleziona un agente',
    icon: 'tabler-briefcase',
  },
  {
    title: 'Cliente',
    subtitle: 'Seleziona un cliente',
    icon: 'tabler-user',
  },
  {
    title: 'Tipo Pratica',
    subtitle: 'Seleziona un tipo di pratica',
    icon: 'tabler-id',
  },
  {
    title: 'Prodotto',
    subtitle: 'Seleziona Brand e Prodotto',
    icon: 'tabler-star',
  },
  {
    title: 'Mandato',
    subtitle: 'Seleziona un mandato (opzionale)',
    icon: 'tabler-file-text',
  },
  {
    title: 'Crea Pratica',
    subtitle: 'Rivedi i dettagli e completa la pratica',
    icon: 'tabler-checkbox',
  },
]

// If current logged in user has role agent, remove step 1
const loggedInUser = useCookie('userData').value
// Check if in the roles array there is a role with name 'agente'
const isAgent = loggedInUser.roles.some(role => role.name === 'agente')

if (isAgent) {
  createPaperworkSteps.splice(0, 1)
}

const currentStep = ref(0)
const isCurrentStepValid = ref(true)

const validateAgentSelected = () => {
  if (!createPaperworkData.value.agent.id) {
    isCurrentStepValid.value = false
  } else {
    isCurrentStepValid.value = true
  }
}

const validateCustomerSelected = () => {
  if (!createPaperworkData.value.customer.id) {
    isCurrentStepValid.value = false
  } else {
    isCurrentStepValid.value = true
  }
}

const validatePaperworkType = () => {
  const paperworkType = createPaperworkData.value.paperworkType
  
  // Validazione campi base
  if (!paperworkType.category ||
      !paperworkType.energy_type ||
      !paperworkType.type) {
    isCurrentStepValid.value = false
    return
  }

  // Validazione dinamica POD/PDR
  // Campo obbligatorio se category != 'ALLACCIO' e energy_type != 'MOBILE'
  const isPodRequired = paperworkType.category !== 'ALLACCIO' && paperworkType.energy_type !== 'MOBILE'
  
  if (isPodRequired && (!paperworkType.account_pod_pdr || paperworkType.account_pod_pdr.trim() === '')) {
    isCurrentStepValid.value = false
    return
  }

  isCurrentStepValid.value = true
}

const validateProductSelected = () => {
  if (!createPaperworkData.value.product.brand_id ||
      !createPaperworkData.value.product.product_id) {
    isCurrentStepValid.value = false
  } else {
    isCurrentStepValid.value = true
  }
}

const validateMandateSelected = () => {
  // Il mandato è opzionale, quindi lo step è sempre valido
  isCurrentStepValid.value = true
}

// Caricamento mandati
const mandates = ref([])
const isLoadingMandates = ref(false)

const fetchMandates = async () => {
  isLoadingMandates.value = true
  try {
    const response = await $api('/mandates?itemsPerPage=999999')
    mandates.value = response.mandates.map(mandate => ({
      title: mandate.name,
      value: mandate.id,
    }))
  } catch (error) {
    console.error('Failed to load mandates:', error)
    mandates.value = []
  } finally {
    isLoadingMandates.value = false
  }
}

// Carica i mandati all'avvio del componente
onMounted(() => {
  fetchMandates()
})

const createPaperworkData = ref({
  agent: {
    id: isAgent ? loggedInUser.id : null,
    name: isAgent ? loggedInUser.name + ' ' + loggedInUser.last_name : null,
    mandate_name: null,
  },
  customer: {
    id: null,
    name: null,
    appointment_id: null,
    appointment_title: null,
  },
  paperworkType: {
    category: 'ALLACCIO',
    type: 'ENERGIA',
    energy_type: null,
    mobile_type: null,
    user_type: null,
    previous_provider: null,
    account_pod_pdr: null,
    annual_consumption: null,
  },
  product: {
    brand_id: null,
    brand_name: null,
    product_id: null,
    product_name: null,
  },
  mandate: {
    mandate_id: null,
    mandate_name: null,
  },
  paperworkReviewComplete: { notes: null, owner_notes: null, isPaperworkDetailsConfirmed: false },
})

const getCustomerName = (customer) => {
  let name = ''
  if (customer.name) {
    name = [customer.name, customer.last_name].join(' ').trim()
  } else {
    name = customer.business_name
  }
  if (customer.vat_number) {
    name += ` - ${customer.vat_number}`
  }
  if (customer.tax_id_code) {
    name += ` - ${customer.tax_id_code}`
  }

  return name
}

if (route.query.customer_id) {
  const responseCustomer = await $api('/customers?itemsPerPage=1&select=1&id=' + route.query.customer_id)
  createPaperworkData.value.customer.id = responseCustomer.customers[0].id
  createPaperworkData.value.customer_name = getCustomerName(responseCustomer.customers[0])
}

if (route.query.agent_id) {
  const responseAgent = await $api('/agents?itemsPerPage=1&select=1&id=' + route.query.agent_id)
  createPaperworkData.value.agent.id = responseAgent.agents[0].id
  createPaperworkData.value.agent_name = [responseAgent.agents[0].name, responseAgent.agents[0].last_name].join(' ')
}

const goToNextStep = () => {
  if (currentStep.value === 0) {
    validateAgentSelected()
  } else if (currentStep.value === 1) {
    validateCustomerSelected()
  } else if (currentStep.value === 2) {
    validatePaperworkType()
  } else if (currentStep.value === 3) {
    validateProductSelected()
  } else if (currentStep.value === 4) {
    validateMandateSelected()
  }

  if (isCurrentStepValid.value) {
    currentStep.value++
  }
}

const isCreating = ref(false)

const router = useRouter()

const onSubmit = async () => {
  try{
    if (! createPaperworkData.value.paperworkReviewComplete.isPaperworkDetailsConfirmed) {
      return false
    }
    isCreating.value = true
    const response = await $api('/paperworks', {
      method: 'POST',
      body: {
        user_id: createPaperworkData.value.agent.id,
        customer_id: createPaperworkData.value.customer.id.value,
        appointment_id: createPaperworkData.value.customer.appointment_id,
        product_id: createPaperworkData.value.product.product_id,
        mandate_id: createPaperworkData.value.mandate.mandate_id,
        account_pod_pdr: createPaperworkData.value.paperworkType.account_pod_pdr,
        annual_consumption: createPaperworkData.value.paperworkType.annual_consumption,
        notes: createPaperworkData.value.paperworkReviewComplete.notes,
        contract_type: createPaperworkData.value.paperworkType.user_type,
        category: createPaperworkData.value.paperworkType.category,
        type: createPaperworkData.value.paperworkType.type,
        energy_type: createPaperworkData.value.paperworkType.energy_type,
        mobile_type: createPaperworkData.value.paperworkType.mobile_type,
        previous_provider: createPaperworkData.value.paperworkType.previous_provider,
      }
    })
    isCreating.value = false

    isPaperworkCreated.value = true
    isPaperworkCreatedDialogVisible.value = true

    // Set a 5 seconds delay before redirecting to the paperwork page
    setTimeout(() => {
      router.push({ name: 'workflow-paperworks-id', params: { id: response.id } })
    }, 5000)
  } catch (error) {
    alert(error.message || 'Errore durante la creazione della pratica')
  }
}
</script>

<template>
  <VCard>
    <VRow no-gutters>
      <VCol
        cols="12"
        md="4"
        lg="3"
        :class="$vuetify.display.mdAndUp ? 'border-e' : 'border-b'"
      >
        <VCardText>
          <AppStepper
            v-model:current-step="currentStep"
            direction="vertical"
            :items="createPaperworkSteps"
            :is-active-step-valid="isCurrentStepValid"
            icon-size="22"
            class="stepper-icon-step-bg"
          />
        </VCardText>
      </VCol>

      <VCol
        cols="12"
        md="8"
        lg="9"
      >
        <VCardText>
          <VWindow
            v-model="currentStep"
            class="disable-tab-transition"
          >
            <VWindowItem v-if="!isAgent">
              <PaperworkAgent v-model:form-data="createPaperworkData.agent" />
            </VWindowItem>

            <VWindowItem>
              <PaperworkCustomer v-model:form-data="createPaperworkData.customer" />
            </VWindowItem>

            <VWindowItem>
              <CreatePaperworkType v-model:form-data="createPaperworkData.paperworkType" />
            </VWindowItem>

            <VWindowItem>
              <PaperworkProduct v-model:form-data="createPaperworkData.product" :ptype="createPaperworkData.paperworkType" :agent="createPaperworkData.agent.id" />
            </VWindowItem>

            <VWindowItem>
              <!-- Step Mandato -->
              <VRow>
                <VCol cols="12">
                  <h6 class="text-h6 mb-4">
                    Seleziona Mandato (Opzionale)
                  </h6>
                  <p class="text-sm text-medium-emphasis mb-6">
                    Seleziona un mandato da associare alla pratica. Questo campo è opzionale.
                  </p>
                </VCol>

                <VCol v-if="$can('access', 'mandates')" cols="12">
                  <AppAutocomplete
                    v-model="createPaperworkData.mandate.mandate_id"
                    label="Mandato"
                    :items="mandates"
                    item-title="title"
                    item-value="value"
                    placeholder="Seleziona un mandato"
                    :loading="isLoadingMandates"
                    clearable
                    @update:model-value="(value) => {
                      const selectedMandate = mandates.find(m => m.value === value)
                      createPaperworkData.mandate.mandate_name = selectedMandate ? selectedMandate.title : null
                    }"
                  />
                </VCol>
              </VRow>
            </VWindowItem>

            <VWindowItem>
              <PaperworkReviewComplete v-model:form-data="createPaperworkData.paperworkReviewComplete" :details="createPaperworkData" />
            </VWindowItem>
          </VWindow>

          <div class="d-flex flex-wrap gap-4 justify-space-between mt-6">
            <VBtn
              color="secondary"
              variant="tonal"
              :disabled="currentStep === 0 || isCreating"
              @click="currentStep--"
            >
              <VIcon
                icon="tabler-arrow-left"
                start
                class="flip-in-rtl"
              />
              Indietro
            </VBtn>

            <VBtn
              v-if="createPaperworkSteps.length - 1 === currentStep"
              :disabled="isCreating || !createPaperworkData.paperworkReviewComplete.isPaperworkDetailsConfirmed"
              color="success"
              @click="onSubmit"
            >
              Crea Pratica
            </VBtn>

            <VBtn
              v-else
              @click="goToNextStep"
            >
              Avanti
              <VIcon
                icon="tabler-arrow-right"
                end
                class="flip-in-rtl"
              />
            </VBtn>
          </div>
        </VCardText>
      </VCol>
    </VRow>

    <!-- 👉 Dialog saying the paperwork has been created -->
    <VDialog
      v-model="isPaperworkCreatedDialogVisible"
      max-width="500"
    >
      <VCard class="text-center px-10 py-6">
        <VCardText>
          <VIcon
            icon="tabler-check"
          color="success"
            size="60"
          />
          <h6 class="text-lg font-weight-medium">
            La pratica è stata creata con successo.
          </h6>
        </VCardText>
      </VCard>
    </VDialog>
  </VCard>
</template>
