<?php

namespace Taler\Api\Order\Dto;

use Taler\Api\ContractTerms\Dto\ContractTermsV1;

/**
 * A wallet claimed the order, but did not yet pay for the contract.
 */
class CheckPaymentClaimedResponse
{
    /**
     * @var string
     */
    private string $orderStatus;

    /**
     * @var ContractTermsV1
     */
    private ContractTermsV1 $contractTerms;

    /**
     * Status URL, can be used as a redirect target for the browser
     * to show the order QR code / trigger the wallet.
     * Since protocol **v19**.
     *
     * @var string
     */
    private string $orderStatusUrl;

    /**
     * @param string $orderStatus
     * @param ContractTermsV1 $contractTerms
     * @param string $orderStatusUrl
     */
    public function __construct(
        string $orderStatus,
        ContractTermsV1 $contractTerms,
        string $orderStatusUrl
    ) {
        $this->orderStatus = $orderStatus;
        $this->contractTerms = $contractTerms;
        $this->orderStatusUrl = $orderStatusUrl;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return self
     */
    public static function createFromArray(array $data): self
    {
        return new self(
            $data['order_status'],
            ContractTermsV1::createFromArray($data['contract_terms']),
            $data['order_status_url']
        );
    }

    /**
     * @return string
     */
    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    /**
     * @return ContractTermsV1
     */
    public function getContractTerms(): ContractTermsV1
    {
        return $this->contractTerms;
    }

    /**
     * @return string
     */
    public function getOrderStatusUrl(): string
    {
        return $this->orderStatusUrl;
    }
} 