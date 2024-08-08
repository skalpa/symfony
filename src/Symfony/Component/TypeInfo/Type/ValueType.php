<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Exception\InvalidArgumentException;
use Symfony\Component\TypeInfo\Exception\LogicException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Represents a constant value, such as 'foo' or 42.
 *
 * Examples: array<int|'min'|'max'>
 *
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @extends AtomicType<TypeIdentifier::OBJECT|TypeIdentifier::STRING|TypeIdentifier::FLOAT|TypeIdentifier::INT|TypeIdentifier::BOOL|TypeIdentifier::NULL, void>
 *
 * @experimental
 */
final class ValueType extends AtomicType
{
    public function __construct(
        private readonly \UnitEnum|string|float|int|bool|null $value,
        ?string $label = null,
    ) {
        if ($value instanceof \UnitEnum && null === $label) {
            $label = $value::class.'::'.$value->name;
        }

        parent::__construct(TypeIdentifier::from(get_debug_type($this->value)), $label ?? var_export($value, true));
    }

    public function getValue(): \UnitEnum|string|float|int|bool|null
    {
        return $this->value;
    }

    public function accepts(Type $type): bool
    {
        return $type instanceof self && $this->value === $type->value;
    }
}
