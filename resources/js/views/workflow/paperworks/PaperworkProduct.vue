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
})

const emit = defineEmits(['update:formData'])

const formData = ref(props.formData)
const selectedBrand = ref({})

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
  const response = await $api(`/brands?itemsPerPage=999999&with=products&product_details=1&type=${props.ptype.user_type}&category=${props.ptype.type}`)
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
        <AppAutocomplete
          v-model="formData.brand_id"
          label="Brand"
          :items="brands"
          item-title="name"
          item-value="id"
          placeholder="Seleziona un Brand"
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
        />
      </VCol>
    </VRow>
  </VForm>
</template>
