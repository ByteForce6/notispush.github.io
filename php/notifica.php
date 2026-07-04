<?php

require_once __DIR__ . "/lib/manejaErrores.php";
require_once __DIR__ . "/../vendor/autoload.php";
require_once  __DIR__ . "/lib/devuelveJson.php";
require_once  __DIR__ . "/Bd.php";
require_once __DIR__ . "/Suscripcion.php";
require_once __DIR__ . "/suscripcionElimina.php";

use Minishlink\WebPush\WebPush;

const AUTH = [
    "VAPID" => [
        "subject" => "https://notispush.infinityfreeapp.com",
        "publicKey" => "BNFrp97cZ7TdAs6_9xT0vb8zxRWS8K9fCxtWG9U9aKumdRArY0xO0cF4Filg_SvxPnKQViDWG7i_5dl-X5oO92U",
        "privateKey" => "78k3TF-sx2fBYZdbEM2dWysqb62Zt4V-6Al_EpcymVg"
    ]
];

$webPush = new WebPush(AUTH);

// Lee el mensaje enviado desde el frontend.
$input = json_decode(file_get_contents("php://input"), true);
$mensaje = (is_array($input) && isset($input["mensaje"]) && is_string($input["mensaje"]))
    ? $input["mensaje"]
    : "Hola! 👋";

// Envia el mensaje a todas las suscripciones.
$bd = Bd::pdo();
$stmt = $bd->query("SELECT * FROM SUSCRIPCION");
$suscripciones =
    $stmt->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, Suscripcion::class);

foreach ($suscripciones as $suscripcion) {
    $webPush->queueNotification($suscripcion, $mensaje);
}
$reportes = $webPush->flush();


// Genera el reporte de envio a cada suscripcion.
    $reporteDeEnvios = "";
foreach ($reportes as $reporte) {
    $endpoint = $reporte->getRequest()->getUri();
    $htmlEndpoint = htmlentities($endpoint);
    // IMPORTANTE: el reporte puede venir con reason no-escapable/encodable raro
    if ($reporte->isSuccess()) {

        // Reporte de éxito.
        $reporteDeEnvios .= "<dt>$htmlEndpoint</dt><dd>Éxito</dd>";
    } else {
        if ($reporte->isSubscriptionExpired()) {
            suscripcionElimina($bd, $endpoint);
        }
        // Reporte de fallo.
        $explicacion = htmlentities($reporte->getReason());
        $reporteDeEnvios .= "<dt>$endpoint</dt><dd>Fallo: $explicacion</dd>";
    }
}

devuelveJson(["reporte" => ["innerHTML" => $reporteDeEnvios]]);
