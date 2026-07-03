const URL_SERVIDOR = "https://notis---upclass.web.app/"

if (self instanceof ServiceWorkerGlobalScope) {

 // El siguiente código se activa cuando llega una notificación push.
 self.addEventListener("push", (/** @type {PushEvent} */ event) => {
  const notificacion = event.data
  /* Si el navegador tiene permitido mostrar notificaciones push,
   * nuestra la que se ha recibido. */
  if (notificacion !== null && self.Notification.permission === 'granted') {
   event.waitUntil(muestraNotificacion(notificacion))
  }
 })

 self.addEventListener("notificationclick",
  (/** @type {NotificationEvent} */ event) => {
   event.notification.close()
   event.waitUntil(muestraVentana())
  })
}

/**
 * @param {PushMessageData} notificacion
 */
async function muestraNotificacion(notificacion) {
 if (self instanceof ServiceWorkerGlobalScope) {
  const mensaje = notificacion.text()
  await self.registration.showNotification(mensaje)
 }
}

async function muestraVentana() {
 if (self instanceof ServiceWorkerGlobalScope) {
  const clientes = await self.clients.matchAll({ type: "window" })
  for (const cliente of clientes) {
   if (cliente.url.startsWith(URL_SERVIDOR)) {
    return cliente.focus()
   }
  }
  return self.clients.openWindow("/")
 }
}