<?php
/**
 * dotProject BR - command-line database upgrade.
 *
 * Runs the same steps as the web installer in "upgrade" mode, using the
 * existing includes/config.php. Useful on servers where the installer should
 * not be exposed to the web.
 *
 * Usage:  php install/upgrade_cli.php [--dry-run]
 *
 * Always take a full database backup first.
 */

if (PHP_SAPI !== 'cli') {
	die('Run this script from the command line.');
}

define('DP_BASE_DIR', dirname(__DIR__));
$baseDir = DP_BASE_DIR;
$baseUrl = 'http://localhost';
$dryRun = in_array('--dry-run', $argv, true);

if (!is_file(DP_BASE_DIR . '/includes/config.php')) {
	fwrite(STDERR, "includes/config.php not found. Configure dotProject first.\n");
	exit(1);
}

global $dPconfig, $db, $dbprefix, $lastDBUpdate, $dbErr, $dbMsg;
$dPconfig = array();
require DP_BASE_DIR . '/includes/config.php';
require_once DP_BASE_DIR . '/install/install.inc.php';
require_once DP_BASE_DIR . '/install/versions.inc.php';
require_once DP_BASE_DIR . '/lib/adodb/adodb.inc.php';
require DP_BASE_DIR . '/includes/version.php';

$AppUI = new InstallerUI();
$dbprefix = isset($dPconfig['dbprefix']) ? $dPconfig['dbprefix'] : '';
$lastDBUpdate = '';
$dbErr = false;
$dbMsg = '';

$db = NewADOConnection($dPconfig['dbtype'] ? $dPconfig['dbtype'] : 'mysqli');
if (!$db || !$db->Connect($dPconfig['dbhost'], $dPconfig['dbuser'], $dPconfig['dbpass'], $dPconfig['dbname'])) {
	fwrite(STDERR, "Could not connect to the database.\n");
	exit(1);
}
$db->Execute("SET sql_mode := ''");

$current_version = $dp_version_major . '.' . $dp_version_minor;
$current_version .= isset($dp_version_patch) ? ('.' . $dp_version_patch) : '';
$current_version .= isset($dp_version_prepatch) ? ('-' . $dp_version_prepatch) : '';

$db_version = InstallGetVersion('upgrade', $db);
$last_version = $db_version['code_version'];
echo "Database version: {$last_version} (last db update {$db_version['last_db_update']})\n";
echo "Code version:     {$current_version}\n";

$from_key = array_search($last_version, $versionPath);
$to_key = array_search($current_version, $versionPath);
if ($from_key === false || $to_key === false) {
	fwrite(STDERR, "Unknown upgrade path from {$last_version} to {$current_version}.\n");
	exit(1);
}

$steps = array();
if ($last_version != $current_version) {
	for ($i = $from_key; $i < $to_key; $i++) {
		$from = str_replace(array('.', '-'), '', $versionPath[$i]);
		$to = str_replace(array('.', '-'), '', $versionPath[$i + 1]);
		$file = DP_BASE_DIR . "/db/upgrade_{$from}_to_{$to}.sql";
		$steps[] = array($versionPath[$i] . ' -> ' . $versionPath[$i + 1], $file);
	}
} elseif (file_exists(DP_BASE_DIR . '/db/upgrade_latest.sql')) {
	$steps[] = array('latest', DP_BASE_DIR . '/db/upgrade_latest.sql');
}

foreach ($steps as $step) {
	list($label, $file) = $step;
	if (!file_exists($file)) {
		echo "[{$label}] no SQL changes\n";
		continue;
	}
	echo "[{$label}] " . basename($file) . "\n";
	if ($dryRun) {
		foreach (InstallSplitSql(file_get_contents($file), $db_version['last_db_update']) as $sql) {
			$sql = trim(str_replace('%dbprefix%', $dbprefix, $sql));
			if ($sql !== '') {
				echo "   would run: " . preg_replace('/\s+/', ' ', mb_substr($sql, 0, 160)) . "\n";
			}
		}
		continue;
	}
	InstallLoadSql($file, $db_version['last_db_update']);
	if ($lastDBUpdate) {
		$db_version['last_db_update'] = $lastDBUpdate;
	}
}

if ($dryRun) {
	echo "Dry run: nothing was changed.\n";
	exit(0);
}

// Data modifications (same as the web installer).
$code_updated = '';
require_once DP_BASE_DIR . '/includes/main_functions.php';
require_once DP_BASE_DIR . '/includes/db_connect.php';
require_once DP_BASE_DIR . '/classes/permissions.class.php';
if (file_exists(DP_BASE_DIR . '/db/upgrade_latest.php')) {
	include_once DP_BASE_DIR . '/db/upgrade_latest.php';
	$code_updated = dPupgrade($db_version['code_version'], $current_version, $db_version['last_code_update']);
}

if (empty($lastDBUpdate)) {
	$lastDBUpdate = $code_updated;
}
$db->Execute('UPDATE ' . $dbprefix . 'dpversion SET db_version = ' . $db->qstr($dp_version_major)
	. ', last_db_update = ' . $db->qstr($lastDBUpdate)
	. ', code_version = ' . $db->qstr($current_version)
	. ', last_code_update = ' . $db->qstr($code_updated) . ' WHERE 1');

if ($dbErr) {
	echo "Finished with errors:\n" . strip_tags(str_replace('<br />', "\n", $dbMsg)) . "\n";
	exit(2);
}
echo "Database upgraded to {$current_version}.\n";
