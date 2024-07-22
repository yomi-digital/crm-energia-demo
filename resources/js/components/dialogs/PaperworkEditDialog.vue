<script setup>
import { watch } from 'vue';

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
              v-if="paperworkDataClone.category === 'SWITCH'"
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
