<?php

namespace Taler\Api\WireTransfers;

use Taler\Api\Base\AbstractApiClient;
use Taler\Api\WireTransfers\Dto\GetTransfersRequest;
use Taler\Api\WireTransfers\Dto\TransfersList;
use Taler\Exception\TalerException;

class WireTransfersClient extends AbstractApiClient
{
    /**
     * @param GetTransfersRequest|null $request Request params
     * @param array<string, string> $headers Optional request headers
     * @return TransfersList|array<string, mixed>
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTransfers(?GetTransfersRequest $request = null, array $headers = []): TransfersList|array
    {
        return Actions\GetTransfers::run($this, $request, $headers);
    }

    /**
     * @param GetTransfersRequest|null $request Request params
     * @param array<string, string> $headers Optional request headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function getTransfersAsync(?GetTransfersRequest $request = null, array $headers = []): mixed
    {
        return Actions\GetTransfers::runAsync($this, $request, $headers);
    }

    /**
     * Delete a wire transfer by its transfer serial ID.
     *
     * @param string $tid
     * @param array<string, string> $headers
     * @return void
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteTransfer(string $tid, array $headers = []): void
    {
        Actions\DeleteTransfer::run($this, $tid, $headers);
    }

    /**
     * Async delete transfer.
     *
     * @param string $tid
     * @param array<string, string> $headers
     * @return mixed
     * @throws TalerException
     * @throws \Throwable
     */
    public function deleteTransferAsync(string $tid, array $headers = []): mixed
    {
        return Actions\DeleteTransfer::runAsync($this, $tid, $headers);
    }
}


