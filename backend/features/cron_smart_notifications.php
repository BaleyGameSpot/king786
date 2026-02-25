<?php
/**
 * CRON: Smart Notifications Queue Processor
 * ------------------------------------------
 * Processes scheduled and recurring push notification queue.
 *
 * Add to server cron (every minute):
 *   * * * * * php /path/to/backend/features/cron_smart_notifications.php >> /path/to/logs/smart_notif.log 2>&1
 *
 * Or use the existing system_cron_jobs_780226.php integration pattern.
 */

// Bootstrap the app
$rootPath = dirname(__DIR__);
if (!file_exists($rootPath . '/common.php')) {
    die("Cannot find common.php at: $rootPath\n");
}
require_once $rootPath . '/common.php';

echo "[" . date('Y-m-d H:i:s') . "] Smart Notifications Cron Started\n";

require_once __DIR__ . '/SmartNotifications.php';

$notif  = new SmartNotifications($obj, $tconfig);
$result = json_decode($notif->handleRequest(['notif_action' => 'processQueue']), true);

$count = $result['processed'] ?? 0;
echo "[" . date('Y-m-d H:i:s') . "] Processed: $count notifications\n";
echo "[" . date('Y-m-d H:i:s') . "] Done.\n";
