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

use Symfony\Component\TypeInfo\Exception\LogicException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Integer type.
 *
 * Supports creating int ranges: "int<10, 20>", "int<min, -1>".
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @extends BuiltinType<TypeIdentifier::INT>
 *
 * @experimental
 */
class IntRangeType extends AtomicType
{
    private readonly int $min;
    private readonly int $max;

    /**
     * @param ValueType<int>|null $min
     * @param ValueType<int>|null $max
     */
    public function __construct(?string $name = 'int', ?ValueType $min = null, ?ValueType $max = null)
    {
        $min ??= new ValueType(PHP_INT_MIN, 'min');
        $max ??= new ValueType(PHP_INT_MAX, 'max');
        $this->min = is_int($min?->getValue()) ? $min->getValue() : PHP_INT_MIN;
        $this->max = is_int($max?->getValue()) ? $max->getValue() : PHP_INT_MAX;

        $variables = null !== $min || null !== $max ? [, ] : [];

        Type::__construct(TypeIdentifier::INT, ...$variables);
    }

    public function accepts(Type $type): bool
    {
        if (TypeIdentifier::INT !== $type->getTypeIdentifier()) {
            return false;
        }
        // We are an unrestricted int
        if (!$this->isGeneric() || PHP_INT_MIN === $this->min && PHP_INT_MAX === $this->max) {
            return true;
        }

        return $type instanceof self && $type->isGeneric() && $type->min >= $this->min && $type->max <= $this->max;
    }

    public function __toString(): string
    {
        return $this->getName().$this->renderVariableTypes();
    }
}
