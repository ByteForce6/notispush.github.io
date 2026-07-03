/**
 * Devuelve una literal de objeto que se puede usar para enviar
 * en formato JSON al servidor.
 * DTO es un acrónimo para Data Transder Object, u
 * objeto para transferencia de datos.
 * @param { PushSubscription } suscripcion
 */
export function calculaDtoParaSuscripcion(suscripcion) {
  const key = suscripcion.getKey("p256dh");
  const token = suscripcion.getKey("auth");
  const json = suscripcion.toJSON();
  const endpoint = suscripcion.endpoint;

  /**
   * Convierte base64 estándar a base64url (URL-safe) compatible con WebPush.
   * @param {string} b64
   */
  function base64ToBase64Url(b64) {
    return b64.replace(/\+/g, "-").replace(/\//g, "_").replace(/=+$/g, "");
  }

  if (key === null) throw new Error("Falta p256dh (publicKey) en la suscripción.");
  if (token === null)
    throw new Error("Falta auth (authToken) en la suscripción.");

  const publicKey = base64ToBase64Url(
    // @ts-ignore
    btoa(String.fromCharCode.apply(null, new Uint8Array(key))),
  );

  const authToken = base64ToBase64Url(
    // @ts-ignore
    btoa(String.fromCharCode.apply(null, new Uint8Array(token))),
)

  // Fallback a aesgcm si el navegador no lo expone.
  const contentEncoding =
    typeof json?.contentEncoding === "string" && json.contentEncoding !== ""
      ? json.contentEncoding
      : "aesgcm";

  return {
    endpoint,
    publicKey,
    authToken,
    contentEncoding,
  };
}

