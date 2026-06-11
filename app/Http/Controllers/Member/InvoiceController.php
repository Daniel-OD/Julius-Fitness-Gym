<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Member;
use App\Support\Invoices\InvoiceDocument;
use App\Support\Invoices\InvoiceDocumentNotRenderable;
use App\Support\Invoices\InvoicePdfRenderer;
use Illuminate\Http\Response;

class InvoiceController extends Controller
{
    public function pdf(Invoice $invoice, InvoicePdfRenderer $renderer): Response
    {
        /** @var Member $member */
        $member = auth('member')->user();

        $invoice->load('subscription');

        if ($invoice->subscription?->member_id !== $member->id) {
            abort(403);
        }

        $invoice = InvoiceDocument::loadForRendering($invoice);

        try {
            $pdfBytes = $renderer->render($invoice);
        } catch (InvoiceDocumentNotRenderable) {
            abort(422);
        }

        return response($pdfBytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.InvoiceDocument::pdfFilename($invoice).'"',
        ]);
    }
}
