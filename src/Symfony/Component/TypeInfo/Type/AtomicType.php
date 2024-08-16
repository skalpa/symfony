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
use Symfony\Component\TypeInfo\TypeIdentifierInterface;

/**
 * Custom atomic type with no pre-established behavior.
 *
 * A custom type only accepts other custom types with the same name, type identifier and compatible variable types.
 *
 * They are by default seen as non-nullable, but users are free to implement their own types
 * with a different behavior by extending this class.
 *
 * @template TPrimitive of TypeIdentifier
 * @template TVariables of Type|void
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @experimental
 */
class AtomicType extends Type
{
    /**
     * @var list<TVariables>
     */
    private readonly array $variableTypes;

    /**
     * @param TPrimitive $typeIdentifier   Native type represented by this type
     * @param string     $name             Type name
     * @param bool       $isNullable       Whether this type is nullable
     * @param string     $compareName      Whether to check another type name before accepting it
     * @param TVariables ...$variableTypes
     */
    public function __construct(
        private readonly TypeIdentifierInterface $typeIdentifier,
        private readonly string $name,
        private readonly bool $isNullable = false,
        private readonly bool $compareName = false,
        Type ...$variableTypes
    ) {
        $this->variableTypes = $variableTypes;
    }

    final public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return list<TVariables>
     */
    final public function getVariableTypes(): array
    {
        return $this->variableTypes;
    }

    final public function getTypeIdentifier(): TypeIdentifierInterface
    {
        return $this->typeIdentifier;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function asNonNullable(): Type
    {
        if (!$this->isNullable()) {
            return $this;
        }

        throw new LogicException(sprintf('"%s" cannot be turned as non nullable.', (string) $this));
    }

    public function accepts(Type $type): bool
    {
        if ($this->compareName && (!$type instanceof self || $this->name !== $type->name)) {
            return false;
        }

        return $this->typeIdentifier->accepts($type->getTypeIdentifier()) && $this->acceptsVariables($type);
    }

    public function __toString(): string
    {
        return $this->name.$this->renderVariableTypes();
    }

    protected function renderVariableTypes(): string
    {
        return $this->variableTypes ? '<'.implode(', ', $this->variableTypes).'>' : '';
    }

    protected function acceptsVariables(Type $type): bool
    {
        if (count($this->variableTypes) && !$type instanceof self) {
            return false;
        }
        $types = $type->getVariableTypes();

        foreach ($this->getVariableTypes() as $n => $variableType) {
            if (!isset($types[$n]) || !$variableType->accepts($types[$n])) {
                return false;
            }
        }

        return true;
    }
}
