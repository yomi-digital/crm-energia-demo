import amministrazione from './amministrazione'
import business from './business'
import comunicazioni from './comunicazioni'
import configurazione from './configurazione'
import general from './general'
import reports from './reports'
import workflow from './workflow'
// import marketing from './marketing'

import dashboard from './dashboard'

export default [
    ...general,
    ...workflow,
    ...configurazione,
    ...comunicazioni,
    ...amministrazione,
    ...reports,
    ...business,
    // ...marketing,
    ...dashboard,
]
