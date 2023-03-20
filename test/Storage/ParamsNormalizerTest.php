<?php

namespace LaminasTest\Mail\Storage;

use ArrayIterator;
use InvalidArgumentException;
use Laminas\Mail\Storage\ParamsNormalizer;
use PHPUnit\Framework\TestCase;

class ParamsNormalizerTest extends TestCase
{
    /** @psalm-return iterable<string, array{0: mixed}> */
    public static function invalidParams(): iterable
    {
        yield 'null'         => [null];
        yield 'bool'         => [true];
        yield 'int'          => [1];
        yield 'float'        => [1.1];
        yield 'string'       => ['string'];
        yield 'list'         => [[1, 2, 3]];
    }

    /**
     * @dataProvider invalidParams
     */
    public function testRaisesErrorOnInvalidParamsTypes(mixed $params): void
    {
        $this->expectException(InvalidArgumentException::class);
        ParamsNormalizer::normalizeParams($params);
    }

    public function testReturnsArrayMapVerbatim(): void
    {
        $params = [
            'foo'   => 'bar',
            'baz'   => [
                'this' => 'that',
            ],
            'some'  => 1,
            'thing' => 1.1,
            'else'  => null,
            'here'  => (object) ['foo' => 'bar'],
        ];

        self::assertSame($params, ParamsNormalizer::normalizeParams($params));
    }

    public function testConvertsIterableMapToArrayMap(): void
    {
        $paramsArray = [
            'foo'   => 'bar',
            'baz'   => [
                'this' => 'that',
            ],
            'some'  => 1,
            'thing' => 1.1,
            'else'  => null,
            'here'  => (object) ['foo' => 'bar'],
        ];
        $params      = new ArrayIterator($paramsArray);

        self::assertSame($paramsArray, ParamsNormalizer::normalizeParams($params));
    }

    public function testConvertsObjectToArrayMap(): void
    {
        $paramsArray = [
            'foo'   => 'bar',
            'baz'   => [
                'this' => 'that',
            ],
            'some'  => 1,
            'thing' => 1.1,
            'else'  => null,
            'here'  => (object) ['foo' => 'bar'],
        ];
        $params      = (object) $paramsArray;

        self::assertSame($paramsArray, ParamsNormalizer::normalizeParams($params));
    }
}
