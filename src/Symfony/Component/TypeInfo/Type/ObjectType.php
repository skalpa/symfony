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
 * Represents an object type.
 *
 * This class can also be used to represent unnamed object types with variable parts.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of class-string|''
 * @template TVariables of Type
 *
 * @experimental
 */
class ObjectType extends AtomicType
{
    /**
     * @param T $className
     */
    public function __construct(string $className, Type ...$variableTypes)
    {
        parent::__construct(TypeIdentifier::OBJECT, $className, ...$variableTypes);
    }

    /**
     * @return T
     */
    public function getClassName(): string
    {
        return $this->getName();
    }

    public function accepts(Type $type): bool
    {
        if (TypeIdentifier::OBJECT !== $type->getTypeIdentifier()) {
            return false;
        }
        if ('' === $this->getName()) { // 'object' accepts all objects
            return true;
        }
        if (!$type instanceof AtomicType || '' === $type->getName()) { // named objects do not accept 'object'
            return false;
        }
        if (!\is_a($type->getName(), $this->getName(), true)) {
            return false;
        }

        return $this->acceptVariables(...$type->getVariableTypes());
    }

    public function __toString(): string
    {
        return ($this->className ?: 'object').$this->renderVariableTypes();
    }
}
