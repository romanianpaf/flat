/**
 * Permission Helper
 * 
 * Sistem centralizat pentru verificarea permisiunilor utilizatorului
 */

/**
 * Obține permisiunile utilizatorului curent din localStorage
 * @returns {Array<string>}
 */
export function getUserPermissions() {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    return user?.permissions || [];
  } catch (error) {
    console.error("Error getting user permissions:", error);
    return [];
  }
}

/**
 * Verifică dacă utilizatorul are o permisiune specifică
 * @param {string} permission - Numele permisiunii (ex: 'view users', 'create items')
 * @returns {boolean}
 */
export function hasPermission(permission) {
  const permissions = getUserPermissions();
  return permissions.includes(permission);
}

/**
 * Verifică dacă utilizatorul are cel puțin una din permisiunile specificate
 * @param {Array<string>} permissions - Lista de permisiuni
 * @returns {boolean}
 */
export function hasAnyPermission(permissions) {
  const userPermissions = getUserPermissions();
  return permissions.some(permission => userPermissions.includes(permission));
}

/**
 * Verifică dacă utilizatorul are toate permisiunile specificate
 * @param {Array<string>} permissions - Lista de permisiuni
 * @returns {boolean}
 */
export function hasAllPermissions(permissions) {
  const userPermissions = getUserPermissions();
  return permissions.every(permission => userPermissions.includes(permission));
}

/**
 * Verifică dacă utilizatorul are un rol specific
 * DEPRECATED: Folosește hasPermission() în loc de verificări de roluri
 * @param {string} roleName
 * @returns {boolean}
 */
export function hasRole(roleName) {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    return user?.roles?.some(role => role.name === roleName) || false;
  } catch (error) {
    console.error("Error checking user role:", error);
    return false;
  }
}

/**
 * Verifică dacă utilizatorul are cel puțin unul din rolurile specificate
 * DEPRECATED: Folosește hasAnyPermission() în loc de verificări de roluri
 * @param {Array<string>} roleNames
 * @returns {boolean}
 */
export function hasAnyRole(roleNames) {
  try {
    const user = JSON.parse(localStorage.getItem("user"));
    return user?.roles?.some(role => roleNames.includes(role.name)) || false;
  } catch (error) {
    console.error("Error checking user roles:", error);
    return false;
  }
}

// Export default pentru import convenabil
export default {
  hasPermission,
  hasAnyPermission,
  hasAllPermissions,
  hasRole,
  hasAnyRole,
  getUserPermissions,
};

