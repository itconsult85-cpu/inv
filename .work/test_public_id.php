<?php
function env($key) { return 'test-secret-for-public-id'; }
require __DIR__ . '/../app/Libraries/PublicId.php';
$t = \App\Libraries\PublicId::encode('15', 'po-keluar-id');
$ok = \App\Libraries\PublicId::decode($t, 'po-keluar-id');
$wrong = \App\Libraries\PublicId::decode($t, 'sample-faktur');
$parts = explode('.', $t, 2);
$last = substr($parts[1], -1);
$tampered = $parts[0] . '.' . substr($parts[1], 0, -1) . ($last === 'A' ? 'B' : 'A');
$bad = \App\Libraries\PublicId::decode($tampered, 'po-keluar-id');
echo json_encode([
    'roundtrip' => $ok,
    'wrong_context' => $wrong,
    'tampered' => $bad,
    'token_prefix' => strtok($t, '.'),
], JSON_UNESCAPED_SLASHES) . PHP_EOL;
