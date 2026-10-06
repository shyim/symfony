<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Tests\Marshaller;

use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Cache\Marshaller\ZstdMarshaller;

#[RequiresPhpExtension('zstd')]
class ZstdMarshallerTest extends TestCase
{
    public function testMarshall()
    {
        $defaultMarshaller = new DefaultMarshaller();
        $zstdMarshaller = new ZstdMarshaller($defaultMarshaller);

        $values = ['abc' => [str_repeat('def', 100)]];

        $failed = [];
        $defaultResult = $defaultMarshaller->marshall($values, $failed);

        $zstdResult = $zstdMarshaller->marshall($values, $failed);
        $zstdResult['abc'] = zstd_uncompress($zstdResult['abc']);

        $this->assertSame($defaultResult, $zstdResult);
    }

    public function testUnmarshall()
    {
        $defaultMarshaller = new DefaultMarshaller();
        $zstdMarshaller = new ZstdMarshaller($defaultMarshaller);

        $values = ['abc' => [str_repeat('def', 100)]];

        $defaultResult = $defaultMarshaller->marshall($values, $failed);
        $zstdResult = $zstdMarshaller->marshall($values, $failed);

        $this->assertSame($values['abc'], $zstdMarshaller->unmarshall($zstdResult['abc']));
        $this->assertSame($values['abc'], $zstdMarshaller->unmarshall($defaultResult['abc']));
    }
}
