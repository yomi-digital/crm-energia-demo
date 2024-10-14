<script setup>
import { PerfectScrollbar } from 'vue3-perfect-scrollbar';
import { VForm } from 'vuetify/components/VForm';

// 👉 store
const props = defineProps({
  isDrawerOpen: {
    type: Boolean,
    required: true,
  },
  feeband: {
    type: null,
    required: true,
  },
})

const emit = defineEmits([
  'update:isDrawerOpen',
  'addFeeband',
  'updateFeeband',
  'removeFeeband',
])

const refForm = ref()

// 👉 Feeband
const feeband = ref(JSON.parse(JSON.stringify(props.feeband)))
const feeTypes = [
  { title: 'Fisso', value: 'FISSO' },
  { title: 'Percentuale', value: 'PERCENTUALE' },
  { title: 'Mensilità', value: 'MESE' },
  { title: 'Consumo', value: 'CONSUMO' },
]

const resetFeeband = () => {
  feeband.value = JSON.parse(JSON.stringify(props.feeband))
  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

watch(() => props.isDrawerOpen, resetFeeband)

const removeFeeband = () => {
  emit('removeFeeband', String(feeband.value.id))

  // Close drawer
  emit('update:isDrawerOpen', false)
}

const handleSubmit = () => {
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {

      // If id exist on id => Update feeband
      if ('id' in feeband.value)
        emit('updateFeeband', feeband.value)

      // Else => add new feeband
      else
        emit('addFeeband', feeband.value)

      // Close drawer
      emit('update:isDrawerOpen', false)
    }
  })
}


// 👉 Form
const onCancel = () => {
  // Close drawer
  emit('update:isDrawerOpen', false)
  nextTick(() => {
    refForm.value?.reset()
    resetFeeband()
    refForm.value?.resetValidation()
  })
}

const startDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
  }

  // if (feeband.value.end_date)
  //   config.maxDate = feeband.value.end_date

  return config
})

const endDateTimePickerConfig = computed(() => {
  const config = {
    dateFormat: `d/m/Y`,
  }

  if (feeband.value.start_date)
    config.minDate = feeband.value.start_date

  return config
})

const dialogModelValueUpdate = val => {
  emit('update:isDrawerOpen', val)
}

const onStartDateChange = value => {
  // console.log(value)
  // let end = new Date(value)
  // end.setMinutes(end.getMinutes() + 60)
  // event.value.end = end
}
</script>

<template>
  <VNavigationDrawer
    temporary
    location="end"
    :model-value="props.isDrawerOpen"
    width="800"
    :border="0"
    class="scrollable-content"
    @update:model-value="dialogModelValueUpdate"
  >
    <!-- 👉 Header -->
    <AppDrawerHeaderSection
      :title="feeband.id ? 'Modifica Fascia Compenso' : 'Aggiungi Fascia Compenso'"
      @cancel="$emit('update:isDrawerOpen', false)"
    >
    </AppDrawerHeaderSection>

    <VDivider />

    <PerfectScrollbar :options="{ wheelPropagation: false }">
      <VCard flat>
        <VCardText>
          <!-- SECTION Form -->
          <VForm
            ref="refForm"
            @submit.prevent="handleSubmit"
          >
            <VRow>
              <!-- 👉 Start date -->
              <VCol cols="6">
                <AppDateTimePicker
                  :key="JSON.stringify(startDateTimePickerConfig)"
                  @update:model-value="onStartDateChange"
                  v-model="feeband.start_date"
                  :rules="[requiredValidator]"
                  label="Data Inizio"
                  placeholder="Seleziona una data"
                  :config="startDateTimePickerConfig"
                />
              </VCol>

              <!-- 👉 End date -->
              <VCol cols="6">
                <AppDateTimePicker
                  :key="JSON.stringify(endDateTimePickerConfig)"
                  v-model="feeband.end_date"
                  label="Data Fine"
                  placeholder="Seleziona una data"
                  :config="endDateTimePickerConfig"
                />
              </VCol>

              <VCol cols="12">
                <AppSelect
                  v-model="feeband.fee_type"
                  label="Tipo di compenso"
                  placeholder="Fisso"
                  :rules="[requiredValidator]"
                  :items="feeTypes"
                />
              </VCol>

              <!-- 👉 Management fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.management_fee"
                  label="Gestione"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Getter fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.top_partner_fee"
                  label="TOP Partner"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Agent fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.top_fee"
                  label="TOP"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Structure fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.partner_fee"
                  label="Partner"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Structure top fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.collaborator_fee"
                  label="Smart"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>

              <!-- 👉 Salesperson fee -->
              <VCol cols="12" md="12">
                <AppTextField
                  v-model="feeband.smart_fee"
                  label="Collaboratore"
                  placeholder="0"
                  :rules="[requiredValidator]"
                />
              </VCol>


              <!-- 👉 Form buttons -->
              <VCol cols="12">
                <VBtn
                  type="submit"
                  class="me-3"
                >
                  Salva
                </VBtn>
                <VBtn
                  variant="outlined"
                  color="secondary"
                  @click="onCancel"
                >
                  Annulla
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        <!-- !SECTION -->
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>
