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
 * Represents a template placeholder, such as "T" in "Collection<T>".
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
final class TemplateType extends Type implements WrappingTypeInterface
{
    public function __construct(
        private readonly string $name,
        private readonly Type $bound,
    ) {
    }

    public function getTypeIdentifier(): TypeIdentifier
    {
        return $this->getBound()->getTypeIdentifier();
    }

    public function isA(TypeIdentifier|string $subject): bool
    {
        return false;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWrappedType(): Type
    {
        return $this->bound;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
