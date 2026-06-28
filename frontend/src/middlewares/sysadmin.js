import { isSystemAdmin } from "@/utils/permissions";

/**
 * Sysadmin middleware
 *
 * Restricționează ecranele de PLATFORMĂ (deasupra tenanților) — ex. managementul
 * tenanților — la operatorul de platformă (sysadmin). Sursa de adevăr este
 * câmpul is_system_admin returnat de /me, nu inspectarea rolurilor.
 */
export default function sysadmin({ next, router }) {
  if (isSystemAdmin()) {
    return next();
  }

  return router.push({ name: "Dashboard" });
}
