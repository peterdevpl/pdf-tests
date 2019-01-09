<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Presentation\Pdf;

interface Renderer
{
    public function render(string $document): string;
}
