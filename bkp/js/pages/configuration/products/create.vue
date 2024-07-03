<script setup>
import FormCreate from '@/views/configuration/products/FormCreate.vue';
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <!-- 👉 Crea Prodotto -->
        <VCard
          title="Crea Prodotto"
        >
          <VCardText>
            <FormCreate @product-data="productData => $router.push({ name: 'configuration-products-id', params: { id: productData.id } })" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
