<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Marshaller;

use Symfony\Component\Cache\Exception\CacheException;

/**
 * Compresses values using zstd_compress().
 */
class ZstdMarshaller implements MarshallerInterface
{
    private int $level;

    /**
     * @param int|null $level The compression level, between 1 and ZSTD_COMPRESS_LEVEL_MAX, defaults to ZSTD_COMPRESS_LEVEL_DEFAULT
     */
    public function __construct(
        private MarshallerInterface $marshaller,
        ?int $level = null,
    ) {
        if (!\function_exists('zstd_compress')) {
            throw new CacheException('The "zstd" PHP extension is not loaded.');
        }

        $this->level = $level ?? \ZSTD_COMPRESS_LEVEL_DEFAULT;
    }

    public function marshall(array $values, ?array &$failed): array
    {
        return array_map(fn (string $value) => zstd_compress($value, $this->level), $this->marshaller->marshall($values, $failed));
    }

    public function unmarshall(string $value): mixed
    {
        if (false !== $uncompressedValue = @zstd_uncompress($value)) {
            $value = $uncompressedValue;
        }

        return $this->marshaller->unmarshall($value);
    }
}
