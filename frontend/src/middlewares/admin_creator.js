import { hasAnyPermission } from "@/utils/permissions";

/**
 * Admin/Creator middleware
 * DEPRECATED: Folosește permission() middleware în schimb
 * 
 * Verifică dacă utilizatorul are permisiuni de creare/administrare conținut
 */
export default function adminCreator({ next, router }) {
  // Verifică dacă are orice permisiune de creare
  if (hasAnyPermission(['create categories', 'create items', 'create service categories', 'view users'])) {
    return next();
  }
  
  return router.push({ name: "Dashboard" });
}
