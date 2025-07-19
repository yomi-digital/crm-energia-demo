<template>
  <div>
    <phone-input
      :value="phoneNumber"
      :label="label"
      :placeholder="placeholder"
      :name="name"
      :required="required"
      :default-country="defaultCountry"
      :arrow="arrow"
      :list-height="listHeight"
      :allowed="allowed"
      :has-error="!!errorMessage"
      :error-message="errorMessage"
      @phone="handlePhoneChange"
      @country="handleCountryChange"
      @phoneData="handlePhoneData"
    />
    
  </div>
</template>

<script setup>
import { $api } from '@/utils/api'
import { onMounted, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    required: true,
    validator: (value) => ['phone', 'mobile'].includes(value)
  },
  label: {
    type: String,
    default: 'Telefono'
  },
  placeholder: {
    type: String,
    default: 'Inserisci numero'
  },
  name: {
    type: String,
    default: ''
  },
  required: {
    type: Boolean,
    default: false
  },
  defaultCountry: {
    type: String,
    default: 'IT'
  },
  arrow: {
    type: Boolean,
    default: true
  },
  listHeight: {
    type: Number,
    default: 150
  },
  allowed: {
    type: Array,
    default: () => ['IT', 'US', 'GB', 'DE', 'FR']
  }
})

const emit = defineEmits(['update:modelValue', 'onCheckUpdate', 'onValue'])

// Stato interno
const phoneNumber = ref(props.modelValue || '')
const errorMessage = ref('')

// Debounce per evitare troppe chiamate API
let debounceTimer = null

// Funzione per controllare la disponibilità del numero
const checkAvailability = async (number) => {
    
if (!number || number.length < 10) {
    errorMessage.value = ''
    emit('onCheckUpdate', { available: true, message: '' })
    return
  }

  try {
    const url = `/customers/mobile/${props.type}/check/${encodeURIComponent(number)}`
    
    const response = await $api(url, {
      method: 'GET'
    })
    
    const data = response
    
    const result = {
      available: data.available,
      message: data.message
    }
    
    errorMessage.value = data.available ? '' : data.message
    emit('onCheckUpdate', result)
    
  } catch (error) {
    console.log('API check error:', error) // Debug
    errorMessage.value = 'Errore durante la verifica'
    emit('onCheckUpdate', { available: false, message: 'Errore durante la verifica' })
  }
}

// Gestione cambio telefono
const handlePhoneChange = (phone) => {
  // Aggiungi il + se non presente
  let formattedPhone = phone
  if (phone && !phone.startsWith('+')) {
    formattedPhone = '+' + phone
  }
  
  phoneNumber.value = formattedPhone
  
  // Emetti il valore formattato
  emit('update:modelValue', formattedPhone)
  emit('onValue', formattedPhone)
  
  // Debounce per il controllo disponibilità
  if (debounceTimer) {
    clearTimeout(debounceTimer)
  }
  
  debounceTimer = setTimeout(() => {
    if (phone) {
      // Usa il numero formattato per il check
      let checkNumber = phone
      if (!phone.startsWith('+')) {
        checkNumber = '+' + phone
      }
      checkAvailability(checkNumber)
    } else {
      errorMessage.value = ''
      emit('onCheckUpdate', { available: true, message: '' })
    }
  }, 500)
}

// Gestione cambio paese
const handleCountryChange = (country) => {
  console.log('Paese cambiato:', country)
}

// Gestione phone data
const handlePhoneData = (phoneData) => {
  console.log('Phone data:', phoneData)
}

// Watch per aggiornamenti esterni del modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue !== phoneNumber.value) {
    phoneNumber.value = newValue || ''
  }
})

// Inizializzazione
onMounted(() => {
  if (props.modelValue) {
    phoneNumber.value = props.modelValue
  }
})
</script>

<style scoped>
.error-message {
  color: #d32f2f;
  font-size: 0.75rem;
  margin-top: 0.5rem;
}
</style> 
