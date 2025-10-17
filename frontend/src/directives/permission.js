import { hasPermission, hasAnyPermission, hasAllPermissions } from "@/utils/permissions";

/**
 * Vue Directive pentru verificarea permisiunilor
 * 
 * Folosire:
 * 
 * 1. O singură permisiune:
 * <button v-permission="'create users'">Add User</button>
 * 
 * 2. Orice permisiune din listă (OR):
 * <button v-permission:any="['create users', 'edit users']">Add/Edit User</button>
 * 
 * 3. Toate permisiunile (AND):
 * <button v-permission:all="['create users', 'delete users']">Full Access</button>
 */
export default {
  mounted(el, binding) {
    const { value, arg } = binding;

    // Determină ce verificare să facă
    let hasRequiredPermission = false;

    if (arg === 'any') {
      // OR logic - cel puțin una din permisiuni
      hasRequiredPermission = hasAnyPermission(Array.isArray(value) ? value : [value]);
    } else if (arg === 'all') {
      // AND logic - toate permisiunile
      hasRequiredPermission = hasAllPermissions(Array.isArray(value) ? value : [value]);
    } else {
      // Default - o singură permisiune
      hasRequiredPermission = hasPermission(value);
    }

    // Ascunde elementul dacă nu are permisiunea
    if (!hasRequiredPermission) {
      el.style.display = 'none';
      // Sau alternativ, elimină complet elementul:
      // el.parentNode?.removeChild(el);
    }
  },

  updated(el, binding) {
    // Re-verifică la update
    const { value, arg } = binding;
    let hasRequiredPermission = false;

    if (arg === 'any') {
      hasRequiredPermission = hasAnyPermission(Array.isArray(value) ? value : [value]);
    } else if (arg === 'all') {
      hasRequiredPermission = hasAllPermissions(Array.isArray(value) ? value : [value]);
    } else {
      hasRequiredPermission = hasPermission(value);
    }

    if (!hasRequiredPermission) {
      el.style.display = 'none';
    } else {
      el.style.display = '';
    }
  }
};

