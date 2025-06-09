<?php

namespace Taler\Api\Exchange\Dto;

use Taler\Api\Dto\RelativeTime;

/**
 * DTO for exchange partner list entry
 * 
 * @see https://docs.taler.net/core/api-common.html
 */
class ExchangePartnerListEntry
{
    /**
     * @param string $partner_base_url Base URL of the partner exchange
     * @param string $partner_master_pub Public master key of the partner exchange (EdDSA)
     * @param string $wad_fee Per exchange-to-exchange transfer (wad) fee
     * @param RelativeTime $wad_frequency Exchange-to-exchange wad (wire) transfer frequency
     * @param string $start_date When did this partnership begin (under these conditions)
     * @param string $end_date How long is this partnership expected to last
     * @param string $master_sig Signature using the exchange's offline key over TALER_WadPartnerSignaturePS with purpose TALER_SIGNATURE_MASTER_PARTNER_DETAILS
     */
    public function __construct(
        public readonly string $partner_base_url,
        public readonly string $partner_master_pub,
        public readonly string $wad_fee,
        public readonly RelativeTime $wad_frequency,
        public readonly string $start_date,
        public readonly string $end_date,
        public readonly string $master_sig,
    ) {
    }

    /**
     * Creates a new instance from an array of data
     *
     * @param array{
     *     partner_base_url: string,
     *     partner_master_pub: string,
     *     wad_fee: string,
     *     wad_frequency: array{d_us: int|string},
     *     start_date: string,
     *     end_date: string,
     *     master_sig: string
     * } $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            partner_base_url: $data['partner_base_url'],
            partner_master_pub: $data['partner_master_pub'],
            wad_fee: $data['wad_fee'],
            wad_frequency: RelativeTime::fromArray($data['wad_frequency']),
            start_date: $data['start_date'],
            end_date: $data['end_date'],
            master_sig: $data['master_sig']
        );
    }
} 