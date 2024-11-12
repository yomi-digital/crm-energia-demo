<script setup>

const route = useRoute('workflow-paperworks-id')
import FormCreate from '@/views/workflow/communications/FormCreate.vue';

const {
  data: communicationData,
  execute: fetchCommunication,
} = await useApi(createUrl(`/communications/${ route.params.id }`))
</script>

<template>
  <div>
    <VRow>
      <VCol cols="12">
        <!-- 👉 Modifica Comunicazione -->
        <VCard
          title="Modifica Comunicazione"
        >
          <VCardText>
            <FormCreate :communication="communicationData" @communication-data="fetchCommunication" />
          </VCardText>
        </VCard>
      </VCol>
    </VRow>
  </div>
</template>
