import store from "../store";

export default function adminCreator({ router }) {
  const userRole = store.getters["profile/profile"]?.roles[0].name;
  const allowedRoles = ["admin", "sysadmin", "creator", "cex"];
  if (!allowedRoles.includes(userRole)) {
    return router.push({ name: "Default" });
  }
}
