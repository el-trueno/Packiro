<?php

declare(strict_types=1);

namespace Packiro\VatValidator\Client\Vies;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Packiro\VatValidator\Client\VatValidatorClientInterface;
use Packiro\VatValidator\Exception\VatIdValidatorClientUnavailableException;
use Psr\Log\LoggerInterface;

class ViesClient implements VatValidatorClientInterface
{
    private const URL_BASE = 'https://ec.europa.eu/taxation_customs/vies/rest-api/';
    private const PATH_POST = 'check-vat-number';
    private const PATH_GET_TEMPLATE = 'ms/{{countryCode}}/vat/{{vatNumber}}';

    private Client $httpClient;

    public function __construct(
        private LoggerInterface $logger,
    ) {
        $this->httpClient = new Client([
            'base_uri' => self::URL_BASE,
        ]);
    }

    public function isVatIdValid(string $vatId): bool
    {
        $vatNumber = substr($vatId, 2);
        $countryCode = substr($vatId, 0, 2);

        try {
            return $this->makeMainRequest($vatNumber, $countryCode);
        } catch (GuzzleException | VatIdValidatorClientUnavailableException $exception) {
            $this->logger->warning('The main VIES request failed', [
                'clientException' => $exception->getMessage(),
            ]);

            return $this->makeFallbackRequest($vatNumber, $countryCode);
        }
    }

    private function makeMainRequest(string $vatNumber, string $countryCode): bool
    {
        $response = $this->httpClient->post(self::PATH_POST, [
            RequestOptions::JSON => [
                'countryCode' => $countryCode,
                'vatNumber' => $vatNumber,
            ],
        ]);

        $decodedResponse = json_decode($response->getBody()->getContents(), true);
        if (isset($decodedResponse['valid'])) {
            return (bool)$decodedResponse['valid'];
        }

        $clientErrors = $decodedResponse['errorWrappers']
            ? json_encode($decodedResponse['errorWrappers'])
            : 'Unexpected error';

        throw new VatIdValidatorClientUnavailableException(sprintf('Main request error: %s', $clientErrors));
    }

    private function makeFallbackRequest(string $vatNumber, string $countryCode): bool
    {
        try {
            $response = $this->httpClient->get(strtr(self::PATH_GET_TEMPLATE, [
                '{{countryCode}}' => $countryCode,
                '{{vatNumber}}' => $vatNumber,
            ]));

            $decodedResponse = json_decode($response->getBody()->getContents(), true);

            if (isset($decodedResponse['userError']) && $decodedResponse['userError'] === 'INVALID') {
                return false;
            }

            // if the service is unavailable or userError = VALID, we treat it as valid VAT id
            return true;
        } catch (GuzzleException $exception) {
            $this->logger->warning('The fallback VIES request failed', [
                'clientException' => $exception->getMessage(),
            ]);

            // if the service is unavailable, we treat it as valid VAT id
            return true;
        }
    }
}
