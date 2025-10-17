import { hasPermission, hasAnyPermission } from "@/utils/permissions";

/**
 * Middleware pentru verificarea permisiunilor
 * 
 * Folosire în routes:
 * meta: {
 *   middleware: [permission('view users')]
 * }
 * 
 * sau pentru multiple permisiuni (OR logic):
 * meta: {
 *   middleware: [permission(['view users', 'edit users'])]
 * }
 */
export default function permission(requiredPermissions) {
  return function ({ next, router }) {
    // Normalizează la array
    const permissions = Array.isArray(requiredPermissions) 
      ? requiredPermissions 
      : [requiredPermissions];

    // Verifică dacă are cel puțin una din permisiuni (OR logic)
    if (!hasAnyPermission(permissions)) {
      console.warn(`Access denied. Required permissions: ${permissions.join(', ')}`);
      return router.push({ name: "Dashboard" });
    }

    return next();
  };
}

