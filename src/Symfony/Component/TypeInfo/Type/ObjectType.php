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

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\TypeIdentifierInterface;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of class-string
 *
 * @experimental
 */
class ObjectType extends AtomicType
{
    /**
     * List of classes/interfaces of this class.
     *
     * @var array<string>
     */
    private readonly array $classNames;

    /**
     * @param T $className
     */
    public function __construct(string|array $className, Type ... $variableTypes)
    {
        $this->classNames = array_values(array_filter((array) $className));

        parent::__construct(TypeIdentifier::OBJECT, $this->classNames[0] ?? '', false, false, ...$variableTypes);
    }

    /**
     * @return T
     */
    public function getClassNames(): array
    {
        return $this->classNames;
    }

    public function accepts(Type $type): bool
    {
        return (!isset($this->classNames[0]) || \in_array($this->classNames[0], $type->getClassNames(), true)) &&
            $this->getTypeIdentifier()->accepts($type->getTypeIdentifier()) &&
            $this->acceptsVariables($type)
        ;
    }

    public function __toString(): string
    {
        return ($this->getName() ?: 'object').$this->renderVariableTypes();
    }
}
