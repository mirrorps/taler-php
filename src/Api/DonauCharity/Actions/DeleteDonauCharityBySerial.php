<?php

namespace Taler\Api\DonauCharity\Actions;

use Psr\Http\Message\ResponseInterface;
use Taler\Api\DonauCharity\DonauCharityClient;
use Taler\Exception\TalerException;

class DeleteDonauCharityBySerial
{
    public function __construct(
        private DonauCharityClient $client
    ) {}

    /**
     * Unlink the Donau charity instance identified by $DONAU_SERIAL.
     *
     * Endpoint: DELETE private/donau/$DONAU_SERIAL
     *
     * @param DonauCharityClient $client
     * @param int $donauSerial
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public static function run(
        DonauCharityClient $client,
        int $donauSerial,
        array $headers = []
    ): void {
        $self = new self($client);

        try {
            $self->client->setResponse(
                $self->client->getClient()->request(
                    'DELETE',
                    "private/donau/{$donauSerial}",
                    $headers
                )
            );

            $client->handleWrappedResponse($self->handleResponse(...));
        } catch (TalerException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $sanitized = \Taler\Helpers\sanitizeString((string) $e->getMessage());
            $client->getTaler()->getLogger()->error("Taler delete Donau charity by serial request failed: {$e->getCode()}, {$sanitized}");
            throw $e;
        }
    }

    /**
     * Async variant
     *
     * @param DonauCharityClient $client
     * @param int $donauSerial
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public static function runAsync(
        DonauCharityClient $client,
        int $donauSerial,
        array $headers = []
    ): mixed {
        $self = new self($client);

        return $client
            ->getClient()
            ->requestAsync(
                'DELETE',
                "private/donau/{$donauSerial}",
                $headers
            )
            ->then(function (ResponseInterface $response) use ($self) {
                $self->client->setResponse($response);
                $self->client->handleWrappedResponse($self->handleResponse(...));
            });
    }

    /**
     * Handle 204 No Content response
     */
    private function handleResponse(ResponseInterface $response): void
    {
        $statusCode = $response->getStatusCode();
        $this->client->parseResponseBody($response, 204);
    }
}



