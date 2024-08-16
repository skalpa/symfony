<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo;

/**
 * Identifier of a PHP native type.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
enum TypeIdentifier: string implements TypeIdentifierInterface
{
    case ARRAY = 'array';
    case BOOL = 'bool';
    case CALLABLE = 'callable';
    case FALSE = 'false';
    case FLOAT = 'float';
    case INT = 'int';
    case ITERABLE = 'iterable';
    case MIXED = 'mixed';
    case NULL = 'null';
    case OBJECT = 'object';
    case RESOURCE = 'resource';
    case STRING = 'string';
    case TRUE = 'true';
    case NEVER = 'never';
    case VOID = 'void';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Implement compatibility between PHP types.
     *
     * NEVER is not accepted anywhere (even by itself)
     * MIXED accepts anything except NEVER
     * BOOL accepts the 3 boolean types
     * ITERABLE is an alias for array|Traversable
     * Other types only accept themselves
     */
    public function accepts(TypeIdentifierInterface $other, array $classNames = []): bool
    {
        return match ($this) {
            self::NEVER => false,
            self::MIXED => self::NEVER !== $other,
            self::NULL => \in_array($other, [self::NULL, self::VOID], true),
            self::BOOL => \in_array($other, [self::BOOL, self::FALSE, self::TRUE], true),
            self::OBJECT => self::OBJECT === $other,
            self::ITERABLE => self::ARRAY === $other || self::OBJECT === $other && \in_array(\Traversable::class, $classNames, true),
            default => $this === $other,
        };
    }
}
