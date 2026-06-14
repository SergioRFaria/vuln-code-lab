<?php
$bundles_dir = __DIR__ . '/bundles';
$exports_dir = __DIR__ . '/exports';
$embedded_mode = isset($support_bundle_embedded) && $support_bundle_embedded === true;

if (isset($_GET['bundle'])) {
    $bundle = basename($_GET['bundle']);

    if (!is_dir($exports_dir)) {
        mkdir($exports_dir, 0777, true);
    }

    $bundle_path = realpath($bundles_dir . '/' . $bundle);
    $bundles_root = realpath($bundles_dir);

    if ($bundle_path === false || $bundles_root === false) {
        echo "Bundle not found.";
        if (!$embedded_mode) {
            http_response_code(404);
            exit;
        }
        return;
    }

    $bundles_root_prefix = $bundles_root . DIRECTORY_SEPARATOR;
    if ($bundle_path !== $bundles_root && strpos($bundle_path, $bundles_root_prefix) !== 0) {
        echo "Access denied.";
        if (!$embedded_mode) {
            http_response_code(403);
            exit;
        }
        return;
    }

    $archive_path = $exports_dir . '/latest-support-bundle.tar';
    if (file_exists($archive_path)) {
        unlink($archive_path);
    }

    try {
        $archive = new PharData($archive_path);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($bundle_path, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file_info) {
            if ($file_info->isFile()) {
                $local_name = substr($file_info->getPathname(), strlen($bundle_path) + 1);
                $archive->addFile($file_info->getPathname(), $local_name);
            }
        }

        echo "Bundle exported successfully: " . basename($archive_path);
    } catch (Exception $e) {
        echo "Could not export bundle.";
        if (!$embedded_mode) {
            http_response_code(500);
        }
    }
} else {
    echo "Missing bundle.";
}
?>
