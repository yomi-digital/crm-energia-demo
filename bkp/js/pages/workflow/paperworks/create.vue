<script setup>
import FormCreate from '@/views/workflow/paperworks/FormCreate.vue';
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <!-- 👉 Crea Pratica -->
        <VCard
          title="Crea Pratica"
        >
          <VCardText>
            <FormCreate @paperwork-data="paperworkData => $router.push({ name: 'workflow-paperworks-id', params: { id: paperworkData.id } })" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
