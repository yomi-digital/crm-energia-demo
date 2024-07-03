<script setup>
import DealReviewComplete from '@/views/wizard-examples/create-deal/DealReviewComplete.vue';
import DealUsage from '@/views/wizard-examples/create-deal/DealUsage.vue';
import PaperworkAgent from '@/views/workflow/paperworks/PaperworkAgent.vue';
import PaperworkCustomer from '@/views/workflow/paperworks/PaperworkCustomer.vue';
import PaperworkDetails from '@/views/workflow/paperworks/PaperworkDetails.vue';
import CreatePaperworkType from '@/views/workflow/paperworks/PaperworkType.vue';

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
    icon: 'tabler-users',
  },
  {
    title: 'Deal Details',
    subtitle: 'Provide deal details',
    icon: 'tabler-id',
  },
  {
    title: 'Deal Usage',
    subtitle: 'Limitations & Offers',
    icon: 'tabler-credit-card',
  },
  {
    title: 'Review & Complete',
    subtitle: 'Launch a deal',
    icon: 'tabler-checkbox',
  },
]

const currentStep = ref(0)
const isCurrentStepValid = ref(true)

const validateAgentSelected = () => {
  if (!createPaperworkData.value.agent.id) {
    isCurrentStepValid.value = false
  } else {
    isCurrentStepValid.value = true
  }
}

const createPaperworkData = ref({
  agent: {
    id: null,
  },
  customer: {
    id: null,
    appointment: null,
  },
  paperworkType: {
    typology: 'ENERGIA',
    typologyType: null,
    type: null,
    mobileType: null,
  },
  dealDetails: {
    title: '',
    code: '',
    description: '',
    offeredUItems: [],
    cartCondition: null,
    dealDuration: '',
    notification: {
      email: false,
      sms: false,
      pushNotification: false,
    },
  },
  dealUsage: {
    userType: null,
    maxUsers: null,
    cartAmount: null,
    promotionFree: null,
    paymentMethod: null,
    dealStatus: null,
    isSingleUserCustomer: false,
  },
  dealReviewComplete: { isDealDetailsConfirmed: true },
})

const onSubmit = () => {
  console.log('createPaperworkData :>> ', createPaperworkData.value)
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
            <VWindowItem>
              <PaperworkAgent v-model:form-data="createPaperworkData.agent" />
            </VWindowItem>

            <VWindowItem>
              <PaperworkCustomer v-model:form-data="createPaperworkData.customer" />
            </VWindowItem>

            <VWindowItem>
              <CreatePaperworkType v-model:form-data="createPaperworkData.paperworkType" />
            </VWindowItem>

            <VWindowItem>
              <PaperworkDetails v-model:form-data="createPaperworkData.dealDetails" />
            </VWindowItem>

            <VWindowItem>
              <DealUsage v-model:form-data="createPaperworkData.dealUsage" />
            </VWindowItem>

            <VWindowItem>
              <DealReviewComplete v-model:form-data="createPaperworkData.dealReviewComplete" />
            </VWindowItem>
          </VWindow>

          <div class="d-flex flex-wrap gap-4 justify-space-between mt-6">
            <VBtn
              color="secondary"
              variant="tonal"
              :disabled="currentStep === 0"
              @click="currentStep--"
            >
              <VIcon
                icon="tabler-arrow-left"
                start
                class="flip-in-rtl"
              />
              Previous
            </VBtn>

            <VBtn
              v-if="createPaperworkSteps.length - 1 === currentStep"
              color="success"
              @click="onSubmit"
            >
              submit
            </VBtn>

            <VBtn
              v-else
              @click="currentStep++"
            >
              Next

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
  </VCard>
</template>
