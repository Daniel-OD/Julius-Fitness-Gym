<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MemberImportDownloadController extends Controller
{
    public function template(): StreamedResponse
    {
        $staticPath = public_path('templates/membri-template.xlsx');

        if (is_file($staticPath)) {
            return response()->download($staticPath, 'membri-template.xlsx');
        }

        return response()->streamDownload(function (): void {
            $writer = new Writer;
            $writer->openToBrowser('membri-template.xlsx');

            $writer->addRow(Row::fromValues([
                'Prenume',
                'Nume',
                'Email',
                'Telefon',
                'Data nasterii',
                'Note',
            ]));

            $writer->addRow(Row::fromValues([
                'Andrei',
                'Popescu',
                'andrei.popescu@example.ro',
                '0721234567',
                '15/03/1990',
                'Abonament anual',
            ]));

            $writer->addRow(Row::fromValues([
                'Maria',
                'Ionescu',
                'maria.ionescu@example.ro',
                '0732987654',
                '22/07/1985',
                '',
            ]));

            $writer->addRow(Row::fromValues([
                'George',
                'Dumitrescu',
                'george.dumitrescu@example.ro',
                '0744112233',
                '01/11/1992',
                'Preferă antrenament dimineața',
            ]));

            $writer->close();
        }, 'membri-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function errorReport(Request $request, string $token): StreamedResponse
    {
        $path = $request->session()->get("member_import_error_report.{$token}");

        abort_unless(
            is_string($path) && Storage::disk('local')->exists($path),
            404,
        );

        return response()->streamDownload(
            fn () => print Storage::disk('local')->get($path),
            'raport-erori-import-membri.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }
}
