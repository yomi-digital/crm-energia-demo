<script setup>
definePage({
  meta: {
    action: 'create',
    subject: 'customers',
  },
})
import FormCreate from '@/views/workflow/customers/FormCreate.vue';
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <!-- 👉 Crea Cliente -->
        <VCard
          title="Crea Cliente"
        >
          <VCardText>
            <FormCreate @customer-data="customerData => $router.push({ name: 'workflow-customers-id', params: { id: customerData.id } })" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
