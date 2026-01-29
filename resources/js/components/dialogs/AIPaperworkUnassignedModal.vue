<template>
  <VDialog
    v-model="isOpen"
    max-width="600"
  >
    <VCard>
      <VCardTitle class="d-flex align-center justify-space-between pa-6">
        <span class="text-h5">Pratica non assegnata</span>
        <VBtn
          icon
          variant="text"
          size="small"
          @click="close"
        >
          <VIcon>mdi-close</VIcon>
        </VBtn>
      </VCardTitle>

      <VDivider />

      <VCardText class="pa-6">
        <div class="mb-4">
          <p class="text-body-1 mb-2">
            Questa pratica AI ha un brand associato:
          </p>
          <VChip
            v-if="brandName"
            color="primary"
            size="large"
            class="mb-4"
          >
            {{ brandName }}
          </VChip>
          <VChip
            v-else
            color="default"
            size="large"
            class="mb-4"
          >
            Brand non disponibile
          </VChip>
        </div>

        <VAlert
          type="info"
          variant="tonal"
          class="mb-4"
        >
          <div class="text-body-1">
            <strong>Nessun backoffice disponibile</strong>
            <p class="mt-2 mb-0">
              Attualmente non ci sono backoffice che hanno questo brand abilitato.
            </p>
          </div>
        </VAlert>

        <div class="text-body-1">
          <p class="mb-2">
            <strong>Per assegnare questa pratica:</strong>
          </p>
          <ul class="ml-4">
            <li>Puoi lavorarla tu manualmente</li>
            <li>Oppure assegna {{ brandName }} a uno o più backoffice nelle impostazioni</li>
          </ul>
        </div>

        <VAlert
          type="success"
          variant="tonal"
          class="mt-4"
        >
          <div class="text-body-2">
            <strong>Riassegnamento automatico</strong>
            <p class="mt-1 mb-0">
              Il sistema riassegnerà automaticamente questa pratica al backoffice meno carico che può lavorare questo brand generalmente entro 1-2 minuti.
            </p>
          </div>
        </VAlert>
      </VCardText>

      <VDivider />

      <VCardActions class="pa-6">
        <VBtn
          color="secondary"
          variant="outlined"
          prepend-icon="tabler-users"
          @click="goToBackofficeUsers"
        >
          Gestisci Backoffice
        </VBtn>
        <VSpacer />
        <VBtn
          color="primary"
          @click="close"
        >
          Ho capito
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  brandName: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)

watch(() => props.modelValue, (newVal) => {
  isOpen.value = newVal
})

watch(isOpen, (newVal) => {
  if (!newVal) {
    emit('update:modelValue', false)
  }
})

const close = () => {
  isOpen.value = false
}

const goToBackofficeUsers = () => {
  close()
  router.push({
    name: 'admin-users',
    query: {
      role: 'backoffice',
    },
  })
}
</script>
