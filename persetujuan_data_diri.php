<?php
// Reuse the existing personal information flow without changing its implementation.
// Buffer the rendered page so this prototype keeps its own URL when validation fails.
ob_start();
require __DIR__ . '/personal_info.php';
$pageContent = ob_get_clean();

echo str_replace(
    'action="personal_info"',
    'action="persetujuan_data_diri"',
    $pageContent
);
