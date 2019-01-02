<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Presentation\View;

use PeterDev\Invoices\Domain\Invoice;

final class InvoiceViewRenderer
{
    public function render(Invoice $invoice)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../../templates');
        $twig = new \Twig_Environment($loader, [
            'cache' => __DIR__ . '/../../../var/cache/templates',
        ]);
        $view = new InvoiceView($invoice);

        return $twig->render('invoice.html.twig', $view->getData());
    }
}
