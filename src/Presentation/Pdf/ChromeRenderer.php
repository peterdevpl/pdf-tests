<?php
declare(strict_types=1);

namespace PeterDev\Invoices\Presentation\Pdf;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

final class ChromeRenderer implements Renderer
{
    /** @var ClientInterface */
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://chrome:3000',
        ]);
    }

    public function render(string $document): string
    {
        try {
            /** @var ResponseInterface $response */
            $response = $this->client->request('post', '/pdf', [
                'json' => [
                    'html' => $document,
                    'options' => [
                        'displayHeaderFooter' => false,
                        'printBackground' => true,
                        'format' => 'A4',
                    ],
                ],
            ]);

            return $response->getBody()->getContents();
        } catch (BadResponseException $e) {

        }
    }
}
