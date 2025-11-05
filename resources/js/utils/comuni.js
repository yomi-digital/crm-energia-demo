import comuniData from './comuni.json'

/**
 * Carica tutti i comuni italiani dal file JSON
 * @returns {Array} Array di oggetti con title (nome comune) e value (nome comune)
 */
export const getAllComuni = () => {
  return comuniData.map(comune => ({
    title: comune.nome,
    value: comune.nome,
  }))
}

