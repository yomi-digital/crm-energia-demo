<script setup>
import energy from '@images/svg/energy.svg';
import phone from '@images/svg/phone-call.svg';

const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

// Computed per determinare se il campo POD/PDR è obbligatorio
const isPodRequired = computed(() => {
  return formData.value.category !== 'ALLACCIO' && formData.value.energy_type !== 'MOBILE'
})

const paperworkTypes = [
  {
    icon: {
      icon: energy,
      size: '28',
    },
    title: 'Energia',
    desc: 'Crea una pratica per un contratto di Energia (Luce/Gas)',
    value: 'ENERGIA',
  },
  {
    icon: {
      icon: phone,
      size: '28',
    },
    title: 'Telefonia',
    desc: 'Crea una pratica per un contratto di Telefonia (Fisso/Mobile)',
    value: 'TELEFONIA',
  },
  // {
  //   icon: {
  //     icon: diamond,
  //     size: '28',
  //   },
  //   title: 'Prime member',
  //   desc: 'Create prime member only deal to encourage the prime members.',
  //   value: 'prime',
  // },
]

const formData = ref(props.formData)

watch(formData, () => {
  emit('update:formData', formData.value)
})

const selectableTypes = () => {
  if (formData.value.type === 'ENERGIA') {
    return [
      { title: 'Luce', value: 'LUCE' },
      { title: 'Gas', value: 'GAS' },
    ]
  } else if (formData.value.type === 'TELEFONIA') {
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
const contractTypesMobile = ref([
  { title: 'NUOVA LINEA', value: 'NUOVA LINEA' },
  { title: 'PORTABILITÀ', value: 'PORTABILITÀ' },
])
</script>

<template>
  <VForm>
    <VRow>
      <VCol cols="12">
        <CustomRadiosWithIcon
          v-model:selected-radio="formData.type"
          :radio-content="paperworkTypes"
          :grid-column="{ cols: '12', sm: '6' }"
          @change="formData.energy_type = null; formData.category = null"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppSelect
          v-model="formData.category"
          label="Tipo Contratto"
          :items="formData.type === 'ENERGIA' ? contractTypes : contractTypesMobile"
          placeholder="Seleziona un tipo contratto"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        v-if="['SWITCH', 'VOLTURA', 'OTP'].includes(formData.category)"
      >
        <AppTextField
          v-model="formData.previous_provider"
          label="Compagnia Fornitore Uscente"
          placeholder="Enel"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppSelect
          v-model="formData.user_type"
          label="Tipologia Pratica"
          :items="[{ title: 'Residenziale', value: 'RESIDENZIALE' }, { title: 'Business', value: 'BUSINESS' }]"
          placeholder="Seleziona un tipo"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppSelect
          v-model="formData.energy_type"
          label="Tipo Utenza"
          :items="selectableTypes()"
          placeholder="Seleziona un tipo di utenza"
        />
      </VCol>

      <VCol
        v-if="formData.energy_type === 'MOBILE' || formData.type === 'FISSO_MOBILE'"
        cols="12"
        sm="6"
      >
        <AppSelect
          v-model="formData.mobile_type"
          label="Tipologia Mobile"
          :items="[{ title: 'MNP', value: 'MNP' }, { title: 'NIP', value: 'NIP' }]"
          placeholder="Seleziona un tipo"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppTextField
          v-model="formData.account_pod_pdr"
          :label="isPodRequired ? 'Account / POD / PDR *' : 'Account / POD / PDR (opzionale)'"
          placeholder="09886655"
          :error="isPodRequired && (!formData.account_pod_pdr || formData.account_pod_pdr.trim() === '')"
          :error-messages="isPodRequired && (!formData.account_pod_pdr || formData.account_pod_pdr.trim() === '') ? 'Il campo POD/PDR è obbligatorio per questo tipo di pratica' : ''"
          :rules="isPodRequired ? [v => !!v || 'Il campo POD/PDR è obbligatorio per questo tipo di pratica'] : []"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppTextField
          v-model="formData.annual_consumption"
          label="Consumo Annuale (opzionale)"
          placeholder="0"
          type="number"
        />
      </VCol>

    </VRow>
  </VForm>
</template>

<style lang="scss">
.deal-type-image-wrapper {
  position: relative;
  block-size: 240px;
  inline-size: 210px;
}

.deal-type-background-img {
  inline-size: 75%;
  inset-block-end: 0;
}
</style>
