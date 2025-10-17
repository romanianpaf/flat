import { hasAnyPermission } from "@/utils/permissions";

/**
 * Admin middleware
 * DEPRECATED: Folosește permission() middleware în schimb
 * 
 * Verifică dacă utilizatorul are permisiuni administrative de bază
 */
export default function admin({ next, router }) {
  // Verifică dacă are permisiuni de admin (view users + view roles + view tenants)
  if (hasAnyPermission(['view users', 'view roles', 'view tenants'])) {
    return next();
  }
  
  return router.push({ name: "Dashboard" });
}
