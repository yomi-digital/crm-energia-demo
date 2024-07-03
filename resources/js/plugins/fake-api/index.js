import { setupWorker } from 'msw/browser'

// Handlers
import { handlerAppBarSearch } from '@/plugins/fake-api/handlers/app-bar-search/index'
import { handlerAppsAcademy } from '@/plugins/fake-api/handlers/apps/academy/index'
import { handlerAppsCalendar } from '@/plugins/fake-api/handlers/apps/calendar/index'
import { handlerAppsChat } from '@/plugins/fake-api/handlers/apps/chat/index'
import { handlerAppsEcommerce } from '@/plugins/fake-api/handlers/apps/ecommerce/index'
import { handlerAppsEmail } from '@/plugins/fake-api/handlers/apps/email/index'
import { handlerAppsInvoice } from '@/plugins/fake-api/handlers/apps/invoice/index'
import { handlerAppLogistics } from '@/plugins/fake-api/handlers/apps/logistics/index'
import { handlerAppsPermission } from '@/plugins/fake-api/handlers/apps/permission/index'
import { handlerAppsUsers } from '@/plugins/fake-api/handlers/apps/users/index'
import { handlerAuth } from '@/plugins/fake-api/handlers/auth/index'
import { handlerDashboard } from '@/plugins/fake-api/handlers/dashboard/index'
import { handlerPagesDatatable } from '@/plugins/fake-api/handlers/pages/datatable/index'
import { handlerPagesFaq } from '@/plugins/fake-api/handlers/pages/faq/index'
import { handlerPagesHelpCenter } from '@/plugins/fake-api/handlers/pages/help-center/index'
import { handlerPagesProfile } from '@/plugins/fake-api/handlers/pages/profile/index'

const worker = setupWorker(...handlerAppsEcommerce, ...handlerAppsAcademy, ...handlerAppsInvoice, ...handlerAppsUsers, ...handlerAppsEmail, ...handlerAppsCalendar, ...handlerAppsChat, ...handlerAppsPermission, ...handlerPagesHelpCenter, ...handlerPagesProfile, ...handlerPagesFaq, ...handlerPagesDatatable, ...handlerAppBarSearch, ...handlerAppLogistics, ...handlerDashboard)
export default function () {
  const workerUrl = `${import.meta.env.BASE_URL.replace(/build\/$/g, '') ?? '/'}mockServiceWorker.js`

  worker.start({
    serviceWorker: {
      url: workerUrl,
    },
    onUnhandledRequest: 'bypass',
  })
}
