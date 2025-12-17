<script setup>
import { computed, nextTick, ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: null,
  },
  label: {
    type: String,
    default: 'Brand',
  },
  placeholder: {
    type: String,
    default: 'Seleziona un brand',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  readonly: {
    type: Boolean,
    default: false,
  },
  items: {
    type: Array,
    default: () => [],
  },
  itemTitle: {
    type: String,
    default: 'name',
  },
  itemValue: {
    type: String,
    default: 'id',
  },
})

const emit = defineEmits(['update:modelValue'])

const searchQuery = ref('')
const expandedCategories = ref({})
const menuOpen = ref(false)
const inputRef = ref(null)

// Raggruppa i brand per categoria e tipo
const groupedBrands = computed(() => {
  const groups = {}
  
  props.items.forEach(brand => {
    const category = brand.category || 'Altri'
    const type = brand.type || 'Altri'
    const groupKey = `${category}_${type}`
    
    if (!groups[groupKey]) {
      groups[groupKey] = {
        category,
        type,
        brands: [],
      }
    }
    
    groups[groupKey].brands.push(brand)
  })
  
  return groups
})

// Filtra i brand in base alla ricerca
const filteredGroupedBrands = computed(() => {
  const query = searchQuery.value?.toLowerCase() || ''
  
  if (!query) {
    return groupedBrands.value
  }
  
  const filtered = {}
  
  Object.keys(groupedBrands.value).forEach(key => {
    const group = groupedBrands.value[key]
    const matchingBrands = group.brands.filter(brand => {
      const brandName = brand[props.itemTitle]?.toLowerCase() || ''
      const category = group.category?.toLowerCase() || ''
      const type = group.type?.toLowerCase() || ''
      
      return brandName.includes(query) || category.includes(query) || type.includes(query)
    })
    
    if (matchingBrands.length > 0) {
      filtered[key] = {
        ...group,
        brands: matchingBrands,
      }
    }
  })
  
  return filtered
})

// Ordina le categorie
const sortedCategories = computed(() => {
  return Object.keys(filteredGroupedBrands.value).sort((a, b) => {
    const groupA = filteredGroupedBrands.value[a]
    const groupB = filteredGroupedBrands.value[b]
    
    // Ordina prima per categoria, poi per tipo
    if (groupA.category !== groupB.category) {
      return groupA.category.localeCompare(groupB.category)
    }
    return groupA.type.localeCompare(groupB.type)
  })
})

// Toggle espansione categoria
const toggleCategory = (categoryKey) => {
  expandedCategories.value[categoryKey] = !expandedCategories.value[categoryKey]
}

// Espandi tutte le categorie quando c'è una ricerca
watch(searchQuery, (newQuery) => {
  if (newQuery) {
    Object.keys(filteredGroupedBrands.value).forEach(key => {
      expandedCategories.value[key] = true
    })
  }
})

// Trova il brand selezionato
const selectedBrandName = computed(() => {
  if (!props.modelValue) return ''
  
  for (const group of Object.values(groupedBrands.value)) {
    const found = group.brands.find(b => b[props.itemValue] === props.modelValue)
    if (found) {
      return found[props.itemTitle]
    }
  }
  
  return ''
})

// Seleziona un brand
const selectBrand = (brand) => {
  emit('update:modelValue', brand[props.itemValue])
  searchQuery.value = ''
  nextTick(() => {
    menuOpen.value = false
  })
}

// Gestisce l'apertura del menu
const handleFocus = () => {
  if (!props.readonly && !props.loading) {
    menuOpen.value = true
  }
}

// Pulisce la query quando il menu si chiude
watch(menuOpen, (isOpen) => {
  if (!isOpen) {
    // Delay per permettere eventuali click sugli item
    setTimeout(() => {
      searchQuery.value = ''
    }, 150)
  }
})

// Gestisce il click sull'input
const handleClick = () => {
  if (!props.readonly && !props.loading) {
    menuOpen.value = true
  }
}

// Ottieni il nome completo della categoria
const getCategoryLabel = (group) => {
  const parts = []
  if (group.category && group.category !== 'Altri') {
    parts.push(group.category)
  }
  if (group.type && group.type !== 'Altri') {
    parts.push(group.type)
  }
  return parts.length > 0 ? parts.join(' - ') : 'Altri'
}

const elementId = computed(() => {
  return `search-brand-${Math.random().toString(36).slice(2, 7)}`
})

// Reset delle categorie espanse quando cambiano gli items
watch(() => props.items, () => {
  // Non espandere nulla di default - tutti i gruppi partono chiusi
  expandedCategories.value = {}
}, { immediate: true })
</script>

<template>
  <div class="search-brand">
    <VLabel
      v-if="label"
      :for="elementId"
      class="mb-1 text-body-2"
      :text="label"
    />
    <VMenu
      v-model="menuOpen"
      :close-on-content-click="false"
      :open-on-click="false"
      location="bottom"
      offset-y
      max-height="400"
      :disabled="readonly || loading"
    >
      <template #activator="{ props: menuProps }">
        <VTextField
          :id="elementId"
          ref="inputRef"
          :model-value="menuOpen ? searchQuery : selectedBrandName"
          @update:model-value="searchQuery = $event"
          @focus="handleFocus"
          @click="handleClick"
          :placeholder="selectedBrandName ? selectedBrandName : placeholder"
          :loading="loading"
          :readonly="readonly"
          variant="outlined"
          v-bind="menuProps"
          autocomplete="off"
        >
          <template #append-inner>
            <VIcon
              :icon="menuOpen ? 'tabler-chevron-up' : 'tabler-chevron-down'"
              size="small"
            />
          </template>
        </VTextField>
      </template>
      
      <VCard 
        class="search-brand-menu"
        @mousedown.prevent
      >
        <VCardText class="pa-0">
          <div
            v-if="loading"
            class="pa-4 text-center text-body-2"
          >
            Caricamento...
          </div>
          <div
            v-else-if="sortedCategories.length === 0"
            class="pa-4 text-center text-body-2"
          >
            <div v-if="searchQuery">Nessun brand trovato</div>
            <div v-else>Nessun brand disponibile</div>
          </div>
          <div
            v-else
            class="search-brand-groups"
          >
            <div
              v-for="categoryKey in sortedCategories"
              :key="categoryKey"
              class="search-brand-group"
            >
              <VListItem
                :class="{ 'search-brand-group-header': true, 'expanded': expandedCategories[categoryKey] }"
                @click="toggleCategory(categoryKey)"
              >
                <template #prepend>
                  <VIcon
                    :icon="expandedCategories[categoryKey] ? 'tabler-chevron-down' : 'tabler-chevron-right'"
                    size="small"
                    class="me-2"
                  />
                </template>
                <VListItemTitle class="d-flex align-center justify-space-between">
                  <span>{{ getCategoryLabel(filteredGroupedBrands[categoryKey]) }}</span>
                  <VChip
                    size="x-small"
                    color="primary"
                    variant="tonal"
                    class="ms-2"
                  >
                    {{ filteredGroupedBrands[categoryKey].brands.length }}
                  </VChip>
                </VListItemTitle>
              </VListItem>
              
              <VExpandTransition>
                <div v-show="expandedCategories[categoryKey]">
                  <VListItem
                    v-for="brand in filteredGroupedBrands[categoryKey].brands"
                    :key="brand[itemValue]"
                    :value="brand[itemValue]"
                    :active="modelValue === brand[itemValue]"
                    @click="selectBrand(brand)"
                    class="search-brand-item pa-2 ps-8"
                  >
                    <VListItemTitle>
                      {{ brand[itemTitle] }}
                    </VListItemTitle>
                  </VListItem>
                </div>
              </VExpandTransition>
            </div>
          </div>
        </VCardText>
      </VCard>
    </VMenu>
  </div>
</template>

<style scoped>
.search-brand {
  width: 100%;
}

.search-brand-groups {
  max-height: 400px;
  overflow-y: auto;
}

.search-brand-group {
  border-bottom: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
}

.search-brand-group:last-child {
  border-bottom: none;
}

.search-brand-group-header {
  cursor: pointer;
  user-select: none;
  background-color: rgba(var(--v-theme-surface), 0.5);
  transition: background-color 0.2s;
}

.search-brand-group-header:hover {
  background-color: rgba(var(--v-theme-surface), 0.8);
}

.search-brand-group-header.expanded {
  background-color: rgba(var(--v-theme-primary), 0.1);
}

.search-brand-item {
  cursor: pointer;
  transition: background-color 0.2s;
}

.search-brand-item:hover {
  background-color: rgba(var(--v-theme-primary), 0.05);
}

.search-brand-item[aria-selected="true"] {
  background-color: rgba(var(--v-theme-primary), 0.1);
  font-weight: 500;
}
</style>

