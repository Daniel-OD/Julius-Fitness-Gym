<?php

use App\Enums\MemberImportField;
use App\Models\Member;
use App\Models\User;
use App\Services\Members\MemberImportColumnMapper;
use App\Services\Members\MemberImportService;
use App\Services\Members\MemberImportSpreadsheetReader;
use App\Support\Members\MemberImportDataset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;

uses(RefreshDatabase::class);

function memberImportCsvPath(): string
{
    $path = storage_path('framework/testing/member-import-sample.csv');
    File::ensureDirectoryExists(dirname($path));
    File::put($path, <<<'CSV'
Prenume,Nume,Email,Telefon,Data nasterii,Note
Andrei,Popescu,andrei@example.test,0721111111,15/03/1990,Notă test
Maria,Ionescu,maria@example.test,0722222222,22/07/1985,
CSV);

    return $path;
}

it('reads csv and suggests column mapping', function (): void {
    $reader = app(MemberImportSpreadsheetReader::class);
    $dataset = $reader->read(memberImportCsvPath(), 'csv');
    $headers = $reader->headersFromFirstRow($dataset, true);
    $mapping = app(MemberImportColumnMapper::class)->suggest($headers);

    expect($mapping[0])->toBe(MemberImportField::FirstName->value)
        ->and($mapping[2])->toBe(MemberImportField::Email->value);
});

it('imports members from mapped rows', function (): void {
    $reader = app(MemberImportSpreadsheetReader::class);
    $service = app(MemberImportService::class);
    $dataset = $reader->read(memberImportCsvPath(), 'csv');
    $headers = $reader->headersFromFirstRow($dataset, true);
    $mapping = app(MemberImportColumnMapper::class)->suggest($headers);
    $rows = $service->buildMappedRows($dataset, true, $mapping);

    $result = $service->importChunk($rows, 0, 'skip');

    expect($result->imported)->toBe(2)
        ->and(Member::query()->where('email', 'andrei@example.test')->exists())->toBeTrue()
        ->and(Member::query()->where('name', 'Maria Ionescu')->exists())->toBeTrue();
});

it('skips duplicate emails when configured', function (): void {
    Member::factory()->create(['email' => 'andrei@example.test', 'name' => 'Existing']);

    $reader = app(MemberImportSpreadsheetReader::class);
    $service = app(MemberImportService::class);
    $dataset = $reader->read(memberImportCsvPath(), 'csv');
    $mapping = app(MemberImportColumnMapper::class)->suggest(
        $reader->headersFromFirstRow($dataset, true),
    );
    $rows = $service->buildMappedRows($dataset, true, $mapping);

    $result = $service->importChunk($rows, 0, 'skip');

    expect($result->skippedDuplicates)->toBe(1)
        ->and($result->imported)->toBe(1);
});

it('updates duplicate emails when configured', function (): void {
    $existing = Member::factory()->create([
        'email' => 'andrei@example.test',
        'name' => 'Existing',
        'contact' => '000',
    ]);

    $reader = app(MemberImportSpreadsheetReader::class);
    $service = app(MemberImportService::class);
    $dataset = $reader->read(memberImportCsvPath(), 'csv');
    $mapping = app(MemberImportColumnMapper::class)->suggest(
        $reader->headersFromFirstRow($dataset, true),
    );
    $rows = $service->buildMappedRows($dataset, true, $mapping);

    $service->importChunk($rows, 0, 'update');

    expect($existing->fresh()->name)->toBe('Andrei Popescu')
        ->and($existing->fresh()->contact)->toBe('0721111111');
});

it('flags rows without email or name in analysis', function (): void {
    $dataset = new MemberImportDataset(
        ['Col 1', 'Col 2'],
        [['', ''], ['valoare', 'altceva']],
    );

    $service = app(MemberImportService::class);
    $rows = $service->buildMappedRows($dataset, false, [
        0 => MemberImportField::Ignore->value,
        1 => MemberImportField::Ignore->value,
    ]);

    $analysis = $service->analyze($rows);

    expect($analysis->importableCount)->toBe(0)
        ->and($analysis->errorCount)->toBe(2);
});

it('serves a static member import template from public', function (): void {
    $path = public_path('templates/membri-template.xlsx');

    expect(is_file($path))->toBeTrue()
        ->and(filesize($path))->toBeGreaterThan(100);
});

it('downloads member import template for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('members.import.template'))
        ->assertSuccessful()
        ->assertDownload('membri-template.xlsx');
});
