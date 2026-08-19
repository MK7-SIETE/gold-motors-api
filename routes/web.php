<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name'    => 'Mukuba Motors API',
        'version' => '1.0.0',
        'status'  => 'running',
    ]);
});
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/run-setup-tasks/{key}', function (string $key, \Illuminate\Http\Request $request) {
    if ($key !== env('SETUP_TASK_KEY')) {
        abort(404);
    }

    Artisan::call('migrate', ['--force' => true]);
    $migrateOutput = Artisan::output();

    $confirmed = $request->query('confirm') === 'yes';
    Artisan::call('hero-images:dedupe', $confirmed ? ['--delete' => true] : []);
    $dedupeOutput = Artisan::output();

    return response()->json([
        'migrate' => $migrateOutput,
        'dedupe'  => $dedupeOutput,
        'note'    => $confirmed
            ? 'Duplicates deleted.'
            : 'Dry run only. Add &confirm=yes to the same URL to actually delete.',
    ]);
});
