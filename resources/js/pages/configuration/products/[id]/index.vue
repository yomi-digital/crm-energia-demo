<script setup>
definePage({
  meta: {
    action: 'view',
    subject: 'products',
  },
})

import ProductInfoPanel from '@/views/configuration/products/ProductInfoPanel.vue';
import ProductTabFeeBands from '@/views/configuration/products/ProductTabFeeBands.vue';

const route = useRoute('configuration-products-id')
const router = useRouter()

watch(() => route.params.id, () => {
  router.go()
})

const {
  data: productData,
  execute: fetchProduct,
} = await useApi(createUrl(`/products/${ route.params.id }`))

const updatedProductData = (newData) => {
  fetchProduct()
}
</script>

<template>
  <VRow v-if="productData">
    <VCol
      cols="12"
      md="5"
      lg="4"
    >
      <ProductInfoPanel :product-data="productData" @update-product-data="updatedProductData" />
    </VCol>

    <VCol
      cols="12"
      md="7"
      lg="8"
    >
      <ProductTabFeeBands />
    </VCol>
  </VRow>
  <div v-else>
    <VAlert
      type="error"
      variant="tonal"
    >
      Prodotto ID  {{ route.params.id }} non trovato!
    </VAlert>
  </div>
</template>
