<script setup>
import { useGenerateImageVariant } from '@core/composable/useGenerateImageVariant'
import CreateDealBackgroundDark from '@images/pages/DealTypeBackground-dark.png'
import CreateDealBackgroundLight from '@images/pages/DealTypeBackground-light.png'
import energy from '@images/svg/energy.svg'
import phone from '@images/svg/phone-call.svg'

const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const createDealBackground = useGenerateImageVariant(CreateDealBackgroundLight, CreateDealBackgroundDark)

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
</script>

<template>
  <VForm>
    <VRow>
      <!-- 👉 Shopping girl image -->
      <!-- <VCol cols="12">
        <div class="d-flex align-center justify-center w-100 deal-type-image-wrapper border rounded px-5 pt-2 pb-5">
          <VImg :src="sittingGirlWithLaptop" />
          <VImg
            :src="createDealBackground"
            class="position-absolute deal-type-background-img d-md-block d-none"
          />
        </div>
      </VCol> -->

      <VCol cols="12">
        <CustomRadiosWithIcon
          v-model:selected-radio="formData.type"
          :radio-content="paperworkTypes"
          :grid-column="{ cols: '12', sm: '6' }"
          @change="formData.energy_type = null"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
      >
        <AppSelect
          v-model="formData.category"
          label="Tipo Contratto"
          :items="contractTypes"
          placeholder="Seleziona un tipo contratto"
        />
      </VCol>

      <VCol
        cols="12"
        sm="6"
        v-if="formData.category === 'SWITCH'"
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
        cols="12"
        sm="6"
      >
        <AppSelect
          v-show="formData.energy_type === 'MOBILE'"
          v-model="formData.mobile_type"
          label="Tipologia Mobile"
          :items="[{ title: 'MNP', value: 'MNP' }, { title: 'NIP', value: 'NIP' }]"
          placeholder="Seleziona un tipo"
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
