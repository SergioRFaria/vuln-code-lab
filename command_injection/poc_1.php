<?php
if (isset($_GET['bundle'])) {
    $bundle = $_GET['bundle'];

    if (!is_dir(__DIR__ . '/exports')) {
        mkdir(__DIR__ . '/exports', 0777, true);
    }

    $cmd = "tar -czf exports/latest-support-bundle.tar.gz bundles/$bundle 2>&1";
    $output = shell_exec($cmd);

    echo "<pre>$output</pre>";
} else {
    echo "Missing bundle.";
}
?>
