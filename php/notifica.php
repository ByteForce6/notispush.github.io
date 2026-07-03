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
        "subject" => "https://notis---upclass.web.app/",
        "publicKey" => "BIJPyU72s-3wzkJ_39hfyPInpCaSOVUMkIR8Lmf-DRAKno8x3ckwzETYTq47ziq2QRfuRDFcpnZwYJ1Z1nXWtNo",
        "privateKey" => "lHLffbM6bVcEWQOQJrvjbEX7eDpUZwEuTL2m7trD74Q"
    ]
];

$webPush = new WebPush(AUTH);
$mensaje = "Hola! 👋";

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
