<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Presentation;

use PeterDev\Invoices\Domain\Invoice;
use PeterDev\Invoices\Presentation\Pdf\ChromeRenderer;
use PeterDev\Invoices\Presentation\View\InvoiceViewRenderer;

final class InvoicePdfRenderer
{
    public function render(Invoice $invoice): string
    {
        $htmlRenderer = new InvoiceViewRenderer();
        $pdfRenderer = new ChromeRenderer();

        return $pdfRenderer->render($htmlRenderer->render($invoice));
    }
}
