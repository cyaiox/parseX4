<?php

namespace CallCenter;

require "./Log.php";
require "./ParseCalls.php";
require "./CallCenterCalls.php";
require "./ManualCalls/ManualCalls.php";
require "./PredictiveCalls/PredictiveCalls.php";
require "./conectorDB.php";
require "./Record.php";
require "./ManualCalls/ManualRecord.php";
require "./PredictiveCalls/PredictiveRecord.php";

$ini_file = parse_ini_file('/etc/microvoz/sistema.ini', true);

$log_predictive_call = new Log('/var/log/microvoz/parseX4_llamadas_predictivas.log');
$log_manual_call = new Log('/var/log/microvoz/parseX4_llamadas_manuales.log');

$db = new ConectorDB(
    'parseX',
    $ini_file['DB']['password'],
    $ini_file['DB']['database'],
    $ini_file['DB']['host']
);

$record_predictive = new PredictiveCalls\PredictiveRecord($db, 'asterisk.cc_predictivo');
$predictive_call = new PredictiveCalls\PredictiveCalls($record_predictive, $db, $log_predictive_call);

$record_manual = new ManualCalls\ManualRecord($db, 'asterisk.cc_manual');
$manual_call = new ManualCalls\ManualCalls($record_manual, $db, $log_manual_call);

$parse = new ParseCalls(5);

$parse->start($manual_call, $predictive_call);