<?php

use App\Services\Exports\CsvExportService;

it('streams CSV content for keyed rows', function () {
    $response = app(CsvExportService::class)->streamDownload('profiles.csv', [
        ['name' => 'Supplier One', 'status' => 'verified'],
        ['name' => 'Supplier Two', 'status' => 'submitted'],
    ]);

    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($content)->toContain('name,status')
        ->and($content)->toContain('"Supplier One",verified')
        ->and($content)->toContain('"Supplier Two",submitted');
});
