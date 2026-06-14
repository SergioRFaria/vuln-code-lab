<?php
$backend = $_GET['backend'] ?? 'legacy';
$bundle = $_GET['bundle'] ?? 'case-4821';

$backend_files = [
    'legacy' => 'poc_1.php',
    'native_archive' => 'poc_1_safe.php',
];

$bundles = [
    [
        'id' => 'case-4821',
        'customer' => 'Alice Carter',
        'issue' => 'MFA reset loop on mobile app',
        'status' => 'Escalated',
    ],
    [
        'id' => 'case-5910',
        'customer' => 'Daniel Price',
        'issue' => 'Statements missing in portal export',
        'status' => 'Pending Review',
    ],
];

if (!isset($backend_files[$backend])) {
    $backend = 'legacy';
}

$bundle_output = null;
$bundle_message = null;
$bundle_message_type = 'notice';
$download_path = null;
$activity_lines = [];

if (isset($_GET['bundle'])) {
    $backend_file = $backend_files[$backend];
    $expected_file = $backend === 'native_archive'
        ? 'latest-support-bundle.tar'
        : 'latest-support-bundle.tar.gz';
    $expected_export = __DIR__ . '/exports/' . $expected_file;

    if (file_exists($expected_export)) {
        unlink($expected_export);
    }

    ob_start();
    $support_bundle_embedded = true;
    include __DIR__ . '/' . $backend_file;
    unset($support_bundle_embedded);
    $bundle_output = trim(ob_get_clean());

    $activity_lines[] = '[' . date('Y-m-d H:i:s') . '] Export job accepted for bundle ' . $bundle . '.';

    if (file_exists($expected_export)) {
        $download_path = 'exports/' . rawurlencode($expected_file) . '?t=' . time();
        $bundle_message = 'Support bundle generated successfully.';
        $bundle_message_type = 'success';
        $activity_lines[] = '[' . date('Y-m-d H:i:s') . '] Archive created and attached to the case workspace.';
    } elseif ($bundle_output !== '') {
        $bundle_message = 'The export job completed with diagnostics that require review.';
        $bundle_message_type = 'warning';
        $activity_lines[] = '[' . date('Y-m-d H:i:s') . '] Export worker returned additional diagnostics.';
    } else {
        $bundle_message = 'The export request did not produce a bundle file.';
        $bundle_message_type = 'warning';
        $activity_lines[] = '[' . date('Y-m-d H:i:s') . '] Export finished without a generated artifact.';
    }

    if ($bundle_output !== '') {
        foreach (preg_split("/\r\n|\n|\r/", strip_tags($bundle_output)) as $line) {
            $line = trim($line);
            if ($line !== '') {
                $activity_lines[] = '[' . date('Y-m-d H:i:s') . '] ' . $line;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NorthBank Support Console</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; color: #1f2937; }
        .header { background: #0f3d5e; color: white; padding: 20px 40px; font-size: 24px; font-weight: bold; }
        .container { max-width: 1020px; margin: 40px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .subtle { color: #6b7280; }
        .layout { display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 28px; margin-top: 24px; }
        .panel { border: 1px solid #e5e7eb; border-radius: 10px; padding: 22px; background: #fff; }
        .summary { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 24px; }
        .summary-card { background: #eef6fb; border-radius: 10px; padding: 18px; }
        .label { color: #6b7280; font-size: 13px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
        .value { font-size: 24px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border-bottom: 1px solid #e5e7eb; text-align: left; padding: 10px; }
        th { background: #eef6fb; }
        form label { display: block; margin-top: 16px; font-weight: bold; }
        input, select { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 15px; box-sizing: border-box; }
        button, .download-link { margin-top: 22px; background: #0f3d5e; color: white; padding: 12px 18px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; text-decoration: none; display: inline-block; }
        button:hover, .download-link:hover { background: #0b2f49; }
        .notice, .success, .warning { padding: 12px; border-radius: 6px; margin-top: 18px; }
        .notice { background: #eef6fb; border: 1px solid #bfdbfe; color: #1d4ed8; }
        .success { background: #ecfdf3; border: 1px solid #86efac; color: #166534; }
        .warning { background: #fff7ed; border: 1px solid #fdba74; color: #9a3412; }
        .small { margin-top: 18px; font-size: 13px; color: #6b7280; }
        .artifact { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 18px; }
        .job-log { background: #111827; color: #e5e7eb; border-radius: 10px; padding: 16px; margin-top: 18px; white-space: pre-wrap; word-break: break-word; font-family: "Courier New", Courier, monospace; }
        .pill { display: inline-block; margin-top: 12px; background: #eef2ff; color: #3730a3; padding: 5px 10px; border-radius: 999px; font-size: 12px; font-weight: bold; }
        code { background: #f3f4f6; padding: 2px 5px; border-radius: 4px; }
        @media (max-width: 860px) {
            .container { margin: 20px; padding: 20px; }
            .layout, .summary { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="header">NorthBank Support Console</div>

    <div class="container">
        <h1>Diagnostic Bundle Export</h1>
        <p class="subtle">Support engineers can export a packaged evidence bundle before handing a customer case to operations or fraud response.</p>

        <div class="summary">
            <div class="summary-card">
                <div class="label">Open Escalations</div>
                <div class="value">12</div>
            </div>
            <div class="summary-card">
                <div class="label">Bundles Generated Today</div>
                <div class="value">37</div>
            </div>
            <div class="summary-card">
                <div class="label">Average Export Time</div>
                <div class="value">18s</div>
            </div>
        </div>

        <div class="layout">
            <div class="panel">
                <h2>Active Cases</h2>
                <p class="subtle">These are the current cases queued for operations review.</p>

                <table>
                    <tr>
                        <th>Case ID</th>
                        <th>Customer</th>
                        <th>Issue</th>
                        <th>Status</th>
                    </tr>
                    <?php foreach ($bundles as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['customer'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['issue'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>

            <div class="panel">
                <h2>Generate Export</h2>
                <p class="subtle">Select the case bundle identifier used by the support storage service and start the export job.</p>

                <form method="GET" action="">
                    <label for="backend">Exporter version</label>
                    <select id="backend" name="backend">
                        <option value="legacy" <?php echo $backend === 'legacy' ? 'selected' : ''; ?>>Legacy shell exporter</option>
                        <option value="native_archive" <?php echo $backend === 'native_archive' ? 'selected' : ''; ?>>Native archive exporter</option>
                    </select>

                    <label for="bundle">Bundle identifier</label>
                    <input id="bundle" name="bundle" value="<?php echo htmlspecialchars($bundle, ENT_QUOTES, 'UTF-8'); ?>" spellcheck="false">

                    <button type="submit">Generate Support Bundle</button>
                </form>

                <?php if ($bundle_message): ?>
                    <div class="<?php echo $bundle_message_type; ?>"><?php echo htmlspecialchars($bundle_message, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>

                <p class="small">Exports are stored in the internal support file area after generation and attached to the escalation record.</p>
                <div class="pill">Active backend file: <?php echo htmlspecialchars($backend_files[$backend], ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
        </div>

        <div class="panel" style="margin-top: 24px;">
            <h2>Generated Artifact</h2>
            <div class="artifact">
                <?php if ($download_path): ?>
                    <p>The export bundle is ready for retrieval.</p>
                    <a class="download-link" href="<?php echo htmlspecialchars($download_path, ENT_QUOTES, 'UTF-8'); ?>">Download Bundle</a>
                <?php else: ?>
                    <p class="subtle">No export artifact is currently available.</p>
                <?php endif; ?>

                <?php if (!empty($activity_lines)): ?>
                    <div class="job-log"><?php echo htmlspecialchars(implode("\n", $activity_lines), ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
