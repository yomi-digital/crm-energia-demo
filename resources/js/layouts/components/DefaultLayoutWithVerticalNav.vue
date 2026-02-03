<script setup>
import navItems from '@/navigation/vertical'
import { can } from '@layouts/plugins/casl'

// Components
import NavBarContractUpload from '@/components/NavBarContractUpload.vue'
import Footer from '@/layouts/components/Footer.vue'
import NavBarNotifications from '@/layouts/components/NavBarNotifications.vue'
import NavbarThemeSwitcher from '@/layouts/components/NavbarThemeSwitcher.vue'
import UserProfile from '@/layouts/components/UserProfile.vue'
// @layouts plugin
import { VerticalNavLayout } from '@layouts'

/** Filtra navItems: nasconde le sezioni (heading) che non hanno alcuna voce visibile sotto. */
function filterNavItemsWithVisibleSections(items) {
  if (!items?.length) return []
  const result = []
  let i = 0
  while (i < items.length) {
    if ('heading' in items[i]) {
      const headingIndex = i
      i += 1
      const sectionItems = []
      while (i < items.length && !('heading' in items[i])) {
        sectionItems.push(items[i])
        i += 1
      }
      const visibleItems = sectionItems.filter(
        item => item.action != null && item.subject != null && can(item.action, item.subject)
      )
      if (visibleItems.length > 0) {
        result.push(items[headingIndex])
        result.push(...visibleItems)
      }
    } else {
      const item = items[i]
      if (item.action != null && item.subject != null && can(item.action, item.subject))
        result.push(item)
      i += 1
    }
  }
  return result
}

const filteredNavItems = computed(() => filterNavItemsWithVisibleSections(navItems))

// SECTION: Loading Indicator
const isFallbackStateActive = ref(false)
const refLoadingIndicator = ref(null)

watch([
  isFallbackStateActive,
  refLoadingIndicator,
], () => {
  if (isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.fallbackHandle()
  if (!isFallbackStateActive.value && refLoadingIndicator.value)
    refLoadingIndicator.value.resolveHandle()
}, { immediate: true })
// !SECTION
</script>

<template>
  <VerticalNavLayout :nav-items="filteredNavItems">
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <IconBtn
          id="vertical-nav-toggle-btn"
          class="ms-n3 d-lg-none"
          @click="toggleVerticalOverlayNavActive(true)"
        >
          <VIcon
            size="26"
            icon="tabler-menu-2"
          />
        </IconBtn>

        <!-- <NavSearchBar class="ms-lg-n3" /> -->

        <VSpacer />

        <NavBarContractUpload class="me-1" />
        <NavbarThemeSwitcher />
        <!-- <NavbarShortcuts /> -->
        <NavBarNotifications class="me-1" />
        <UserProfile />
      </div>
    </template>

    <AppLoadingIndicator ref="refLoadingIndicator" />

    <!-- 👉 Pages -->
    <RouterView v-slot="{ Component }">
      <Suspense
        :timeout="0"
        @fallback="isFallbackStateActive = true"
        @resolve="isFallbackStateActive = false"
      >
        <Component :is="Component" />
      </Suspense>
    </RouterView>

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>
