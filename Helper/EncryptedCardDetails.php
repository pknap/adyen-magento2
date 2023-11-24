<?php
/**
 *
 * Adyen Payment module (https://www.adyen.com/)
 *
 * Copyright (c) 2023 Adyen N.V. (https://www.adyen.com/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>
 */

namespace Adyen\Payment\Helper;

use Adyen\Payment\Model\Cache\Type\AdyenCache;
use Magento\Framework\App\CacheInterface;

class EncryptedCardDetails
{
    // Expiry date of the cache in seconds.
    const LIFE_TIME = 3 * 60 * 60;
    const CACHE_ID_PREFIX = 'encryptedCardHash';

    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache
    ) {
        $this->cache = $cache;
    }

    public function isCardHashStoredInCache(string $encryptedCardNumber, bool $setAfterChecking = false): bool
    {
        $cardHash = $this->calculateCardHash($encryptedCardNumber);
        $isCardHashStored = (bool) $this->cache->load($this->getCardHashCacheId($cardHash));

        if ($setAfterChecking && !$isCardHashStored) {
            $this->storeCardHashInCache($cardHash);
        }

        return $isCardHashStored;
    }

    private function storeCardHashInCache(string $cardHash): void
    {
        $this->cache->save(
            true,
            $this->getCardHashCacheId($cardHash),
            [AdyenCache::CACHE_TAG],
            self::LIFE_TIME
        );
    }

    private function calculateCardHash(string $encryptedCardNumber): string
    {
        return hash('sha256', $encryptedCardNumber);
    }

    private function getCardHashCacheId(string $cardHash): string
    {
        return sprintf(
            "%s-%s-%s",
            AdyenCache::TYPE_IDENTIFIER,
            self::CACHE_ID_PREFIX,
            $cardHash
        );
    }
}
