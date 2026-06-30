import store from "../store";

export default async function auth({ to, next, router }) {
  if (!store.getters["auth/loggedIn"]) {
    return next({ name: "Login" });
  }
  await store.dispatch("profile/getProfile");

  // Onboarding: un locatar nou e forțat să-și completeze cartea de imobil
  // înainte de a putea naviga oriunde altundeva.
  const profile = store.getters["profile/profile"];
  if (profile?.needs_carte_imobil === true && to.name !== "Carte de imobil") {
    return router.push({ name: "Carte de imobil" });
  }

  next();
}
