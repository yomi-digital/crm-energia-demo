/**
 * Resolve user role variant (icon and color)
 * @param {Object} role - User role object with name property
 * @returns {Object} Object with color and icon properties
 */
export const resolveUserRoleVariant = role => {
  if (!role) {
    role = { name: 'undefined' }
  }

  const roleLowerCase = role.name.toLowerCase()
  
  if (roleLowerCase === 'struttura')
    return {
      color: 'success',
      icon: 'tabler-building',
    }
  if (roleLowerCase === 'telemarketing')
    return {
      color: 'info',
      icon: 'tabler-phone',
    }
  if (roleLowerCase === 'agente')
    return {
      color: 'success',
      icon: 'tabler-briefcase',
    }
  if (roleLowerCase === 'backoffice')
    return {
      color: 'warning',
      icon: 'tabler-device-desktop',
    }
  if (roleLowerCase === 'amministrazione')
    return {
      color: 'primary',
      icon: 'tabler-crown',
    }

  return {
    color: 'primary',
    icon: 'tabler-user',
  }
}

/**
 * Get user avatar URL
 * @param {Object} user - User object with name, last_name, and avatar properties
 * @returns {string} Avatar URL
 */
export const getAvatar = (user) => {
  if (user.avatar) {
    return user.avatar
  }

  return 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name + ' ' + user.last_name) + '&background=random&color=fff'
}

/**
 * Resolve user status variant (color only)
 * @param {number} stat - User status (1 = enabled, 0 = disabled)
 * @returns {string} Color variant
 */
export const resolveUserStatusVariant = stat => {
  if (stat !== 1)
    return 'error'
  if (stat === 1)
    return 'success'

  return 'primary'
} 
