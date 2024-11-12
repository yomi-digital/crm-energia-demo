<script setup>
import FormCreate from '@/views/workflow/communications/FormCreate.vue';
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <!-- 👉 Crea Comunicazione -->
        <VCard
          title="Crea Comunicazione"
        >
          <VCardText>
            <FormCreate @communication-data="communicationData => $router.push({ name: 'workflow-communications-id', params: { id: communicationData.id } })" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
