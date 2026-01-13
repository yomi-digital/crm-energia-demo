<script setup>
import { watch } from 'vue';

const props = defineProps({
  formData: {
    type: null,
    required: true,
  },
  ptype: {
    type: null,
    required: true,
  },
  agent: {
    type: null,
    required: true,
  },
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)
const selectedBrand = ref({})

const hasBrandError = computed(() => !formData.value.brand_id)
const hasProductError = computed(() => !formData.value.product_id)

watch(formData, () => {
  emit('update:formData', formData.value)
})
watch(props.ptype, () => {
  formData.value.brand_id = null
  formData.value.product_id = null
  fetchBrands('')
})

const brands = ref([])

const fetchBrands = async (query) => {
  const response = await $api(`/brands/personal?itemsPerPage=999999&with=products&product_details=1&type=${props.ptype.user_type}&category=${props.ptype.type}&agent=${props.agent}&enabled=1`)
  brands.value = response.brands
}
await fetchBrands('')


const selectBrand = () => {
  if (!formData.value.brand_id) {
    return
  }
  selectedBrand.value = brands.value.find(brand => brand.id === formData.value.brand_id)
  formData.value.brand_name = selectedBrand.value.name
  formData.value.product_id = null
  // Remove disabled products
  selectedBrand.value.products = selectedBrand.value.products.filter(product => product.enabled)
}

watch(() => formData.value.brand_id, selectBrand)
watch(() => formData.value.product_id, () => {
  if (!formData.value.product_id) {
    return
  }
  const selected = selectedBrand.value.products.find(product => product.id === formData.value.product_id)
  formData.value.product_name = selected.name
})
</script>

<template>
  <VForm>
    <VRow>
      <VCol
        cols="12"
        sm="12"
      >
        <SearchBrand
          v-model="formData.brand_id"
          label="Brand"
          :items="brands"
          item-title="name"
          item-value="id"
          placeholder="Seleziona un Brand"
          :error="hasBrandError"
          :error-messages="hasBrandError ? 'Seleziona un brand' : ''"
          :rules="[v => !!v || 'Seleziona un brand']"
        />
      </VCol>

      <VCol
        cols="12"
        sm="12"
      >
        <AppAutocomplete
          :disabled="!formData.brand_id"
          v-model="formData.product_id"
          label="Prodotto"
          item-title="name"
          item-value="id"
          :items="selectedBrand ? selectedBrand.products : []"
          placeholder="Seleziona un Prodotto"
          :error="hasProductError"
          :error-messages="hasProductError ? 'Seleziona un prodotto' : ''"
          :rules="[v => !!v || 'Seleziona un prodotto']"
        />
      </VCol>
    </VRow>
  </VForm>
</template>
