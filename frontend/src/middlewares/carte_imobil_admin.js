import { hasAnyPermission } from "@/utils/permissions";

export default function carteImobilAdmin({ next, router }) {
  if (hasAnyPermission(["approve carte imobil", "configure apartments"])) {
    return next();
  }

  return router.push({ name: "Acasă" });
}

