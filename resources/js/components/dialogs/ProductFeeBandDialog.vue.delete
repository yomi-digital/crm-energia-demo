<script setup>
const props = defineProps({
  feebandData: {
    type: Object,
    required: false,
    default: () => ({
      start_date: '',
      end_date: '',
      fee_type: 'FISSO',
      management_fee: 0,
      agent_fee: 0,
      structure_fee: 0,
      structure_top_fee: 0,
      salesperson_fee: 0,
    }),
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

const feebandData = ref(structuredClone(toRaw(props.feebandData)))

watch(props, () => {
  feebandData.value = structuredClone(toRaw(props.feebandData))
})

const onFormSubmit = () => {
  emit('update:isDialogVisible', false)
  emit('submit', feebandData.value)
}

const onFormReset = () => {
  feebandData.value = structuredClone(toRaw(props.feebandData))
  emit('update:isDialogVisible', false)
}

const dialogModelValueUpdate = val => {
  emit('update:isDialogVisible', val)
}

const feeTypes = [
  { title: 'Fisso', value: 'FISSO' },
  { title: 'Percentuale', value: 'PERCENTUALE' },
  { title: 'Mensilità', value: 'MESE' },
  { title: 'Consumo', value: 'CONSUMO' },
]

const doSomething = (e, val) => {
  console.log('doSomething', e, val)
}
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
          <template v-if="feebandData.id">Modifica</template>
          <template v-else>Aggiungi</template>
          Fascia Compenso
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
            <!-- 👉 Start date -->
            <VCol
              cols="12"
              md="6"
            >
              <AppDateTimePicker
                v-model="feebandData.start_date"
                label="Data inizio"
                placeholder="Seleziona la data di inizio"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 End date -->
            <VCol
              cols="12"
              md="6"
            >
              <AppDateTimePicker
                v-model="feebandData.end_date"
                label="Data fine"
                placeholder="Seleziona la data di fine"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Fee type -->
            <VCol
              cols="12"
              md="12"
            >
              <AppSelect
                v-model="feebandData.fee_type"
                label="Tipo di compenso"
                placeholder="Fisso"
                :rules="[requiredValidator]"
                :items="feeTypes"
              />
            </VCol>

            <!-- 👉 Management fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.management_fee"
                label="Gestione"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Getter fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.getter_fee"
                label="Procacciatore"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Agent fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.agent_fee"
                label="Agente"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Structure fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.structure_fee"
                label="Struttura"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Structure top fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.structure_top_fee"
                label="Struttura Top"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

            <!-- 👉 Salesperson fee -->
            <VCol
              cols="12"
              md="6"
            >
              <AppTextField
                v-model="feebandData.salesperson_fee"
                label="Venditore"
                placeholder="0"
                :rules="[requiredValidator]"
              />
            </VCol>

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
