<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';

const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'feebandData',
])

const isFormValid = ref(false)
const refForm = ref()
const startDate = ref('')
const endDate = ref('')
const feeType = ref('FISSO')
const getterFee = ref(0)
const managementFee = ref('')
const agentFee = ref('')
const structureFee = ref('')
const structureTopFee = ref(0)
const salespersonFee = ref(0)

const feeTypes = [
  { title: 'Fisso', value: 'FISSO' },
  { title: 'Percentuale', value: 'PERCENTUALE' },
  { title: 'Mensilità', value: 'MESE' },
  { title: 'Consumo', value: 'CONSUMO' },
]

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    refForm.value?.resetValidation()
  })
}

const onSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      emit('feebandData', {
        start_date: startDate.value,
        end_date: endDate.value,
        fee_type: feeType.value,
        management_fee: managementFee.value,
        agent_fee: agentFee.value,
        structure_fee: structureFee.value,
        structure_top_fee: structureTopFee.value,
        salesperson_fee: salespersonFee.value,
      })
      emit('update:isDrawerOpen', false)
      nextTick(() => {
        refForm.value?.reset()
        refForm.value?.resetValidation()
      })
    }
  })
}

const handleDrawerModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}
</script>

<template>
  <VNavigationDrawer
    temporary
    :width="400"
    location="end"
    class="scrollable-content"
    :model-value="props.isDrawerOpen"
    @update:model-value="handleDrawerModelValueUpdate"
  >
    <!-- 👉 Title -->
    <AppDrawerHeaderSection
      title="Aggiungi Fascia Compenso"
      @cancel="closeNavigationDrawer"
    />

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- 👉 Form -->
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Start date -->
              <VCol cols="12">
                <AppDateTimePicker
                  :key="1234"
                  v-model="startDate"
                  label="Data inizio"
                  placeholder="Seleziona la data di inizio"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 End date -->
              <VCol cols="12">
                <AppDateTimePicker
                  :key="5678"
                  v-model="endDate"
                  label="Data fine"
                  placeholder="Seleziona la data di fine"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="feeType"
                  label="Tipo di compenso"
                  placeholder="Fisso"
                  :rules="[requiredValidator]"
                  :items="feeTypes"
                />
              </VCol>

              <!-- 👉 Management fee -->
              <VCol cols="12">
                <AppTextField
                  v-model="managementFee"
                  label="Gestione"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Getter fee -->
              <VCol cols="12">
                <AppTextField
                  v-model="getterFee"
                  label="Procacciatore"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Agent fee -->
              <VCol cols="12">
                <AppTextField
                  v-model="agentFee"
                  label="Agente"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Structure fee -->
              <VCol cols="12">
                <AppTextField
                  v-model="structureFee"
                  label="Struttura"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Structure top fee -->
              <VCol cols="12">
                <AppTextField
                  v-model="structureTopFee"
                  label="Struttura Top"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Salesperson fee -->
              <VCol cols="12">
                <AppTextField
                  label="Venditore"
                />
              </VCol>

              <!-- 👉 Submit and Cancel -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Aggiungi
                </VBtn>
                <VBtn
                  type="reset"
                  variant="tonal"
                  color="error"
                  @click="closeNavigationDrawer"
                >
                  Annulla
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
