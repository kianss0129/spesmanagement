<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Contract;
use App\Models\Application;
use Carbon\Carbon;

echo 'Scheduled contracts:' . PHP_EOL;
$contracts = Contract::where('status', 'scheduled')->whereDate('contract_date', '>=', Carbon::today())->get();
foreach($contracts as $c) {
    echo 'ID: ' . $c->id . ', Date: ' . $c->contract_date . ', Status: ' . $c->status . PHP_EOL;
    if ($c->application) {
        echo '  Application ID: ' . $c->application->id . ', Beneficiary ID: ' . $c->application->beneficiary_id . PHP_EOL;
    }
}
echo 'Total: ' . $contracts->count() . PHP_EOL;
?>