<script setup>
import RelationshipsTable from './RelationshipsTable.vue';

const props = defineProps({
  userData: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <VRow>
    <VCol cols="12">
      <RelationshipsTable :user-data="props.userData" />
    </VCol>
  </VRow>
</template>
