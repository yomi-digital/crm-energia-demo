import amministrazione from './amministrazione'
import business from './business'
import configurazione from './configurazione'
import extra from './extra'
import general from './general'
import lead from './lead'
import reports from './reports'
import workflow from './workflow'
// import marketing from './marketing'


export default [
    ...general,
    ...workflow,
    ...configurazione,
    // ...comunicazioni,
    ...amministrazione,
    ...reports,
    ...business,
    ...lead,
    ...extra,
    // ...marketing,
    // ...dashboard,
]
