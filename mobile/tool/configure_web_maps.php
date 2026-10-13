<?php
// Generate a local web bootstrap from the existing Laravel browser Maps key.
require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$key = config('services.google_maps.key');
if (!$key) { fwrite(STDERR, "Clé Google Maps manquante dans Laravel.\n"); exit(1); }
$url = 'https://maps.googleapis.com/maps/api/js?key='.rawurlencode($key).'&language=fr';
$script = 'document.write('.json_encode('<script src="'.$url.'"></script>', JSON_HEX_TAG | JSON_HEX_AMP).');';
file_put_contents(dirname(__DIR__).'/web/maps-config.js', $script);
echo "Configuration Maps web créée.\n";
