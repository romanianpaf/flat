import { hasAnyPermission } from "../utils/permissions.js";

export default function viewMembers({ next, router }) {
  // Permite acces dacă are "view members" SAU "view users"
  if (!hasAnyPermission(['view members', 'view users'])) {
    return router.push({ name: 'Acasă' });
  }
  return next();
}
