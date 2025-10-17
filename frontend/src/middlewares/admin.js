import store from "../store";

export default function admin({ router }) {
  const userRole = store.getters["profile/profile"]?.roles[0].name;
  const adminRoles = ["admin", "sysadmin"];
  if (!adminRoles.includes(userRole)) {
    return router.push({ name: "Default" });
  }
}
