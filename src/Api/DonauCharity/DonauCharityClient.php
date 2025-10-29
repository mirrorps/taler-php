<?php

namespace Taler\Api\DonauCharity;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\DonauCharity\Actions\GetDonauInstances;
use Taler\Api\DonauCharity\Actions\CreateDonauCharity;
use Taler\Api\DonauCharity\Actions\DeleteDonauCharityBySerial as DonauDeleteBySerialAction;
use Taler\Api\DonauCharity\Dto\DonauInstancesResponse;
use Taler\Api\DonauCharity\Dto\PostDonauRequest;
use Taler\Api\Instance\Dto\Challenge;
use Taler\Exception\TalerException;

class DonauCharityClient extends AbstractApiClient
{
    /**
     * @param array<string, string> $headers Optional request headers
     * @return DonauInstancesResponse|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getInstances(array $headers = []): DonauInstancesResponse|array
    {
        return GetDonauInstances::run($this, $headers);
    }

    /**
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     */
    public function getInstancesAsync(array $headers = []): mixed
    {
        return GetDonauInstances::runAsync($this, $headers);
    }

    /**
     * Link a new Donau charity instance to the current instance context.
     *
     * @param PostDonauRequest $request
     * @param array<string, string> $headers
     * @return Challenge|null Returns Challenge if 2FA is required (202), null on success (204)
     * @throws TalerException
     * @throws \Throwable
     */
    public function createDonauCharity(PostDonauRequest $request, array $headers = []): ?Challenge
    {
        return CreateDonauCharity::run($this, $request, $headers);
    }

    /**
     * Link a new Donau charity instance asynchronously.
     *
     * @param PostDonauRequest $request
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function createDonauCharityAsync(PostDonauRequest $request, array $headers = []): mixed
    {
        return CreateDonauCharity::runAsync($this, $request, $headers);
    }

    /**
     * Unlink the Donau charity instance identified by $DONAU_SERIAL.
     *
     * @param int $donauSerial
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteDonauCharityBySerial(int $donauSerial, array $headers = []): void
    {
        DonauDeleteBySerialAction::run($this, $donauSerial, $headers);
    }

    /**
     * Unlink the Donau charity instance by serial asynchronously.
     *
     * @param int $donauSerial
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteDonauCharityBySerialAsync(int $donauSerial, array $headers = []): mixed
    {
        return DonauDeleteBySerialAction::runAsync($this, $donauSerial, $headers);
    }
}

 

