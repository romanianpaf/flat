import Echo from "laravel-echo";
import Pusher from "pusher-js";

/**
 * Conexiunea websocket (Laravel Reverb) pentru notificări live.
 *
 * Singleton lazy: se creează la primul apel, doar dacă există token și
 * configurare Reverb (VITE_REVERB_APP_KEY). Autorizarea canalelor private se
 * face pe /api/broadcasting/auth cu tokenul Passport (Bearer).
 *
 * Dacă websocket-ul nu e disponibil, apelanții trebuie să funcționeze în
 * continuare (clopoțelul păstrează fallback-ul pe fetch).
 */

window.Pusher = Pusher;

let echoInstance = null;

export function getEcho() {
  if (echoInstance) return echoInstance;

  const key = import.meta.env.VITE_REVERB_APP_KEY;
  const token = localStorage.getItem("token");
  if (!key || !token) return null;

  const apiBase = import.meta.env.VITE_API_BASE_URL || "";
  const authEndpoint = apiBase.replace(/\/v2\/?$/, "") + "/broadcasting/auth";

  echoInstance = new Echo({
    broadcaster: "reverb",
    key,
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT || 6001),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT || 6001),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || "https") === "https",
    enabledTransports: ["ws", "wss"],
    authEndpoint,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
        Accept: "application/json",
      },
    },
  });

  return echoInstance;
}

/** Închide conexiunea (la logout / schimbare de user, ex. impersonare). */
export function disconnectEcho() {
  if (echoInstance) {
    echoInstance.disconnect();
    echoInstance = null;
  }
}
