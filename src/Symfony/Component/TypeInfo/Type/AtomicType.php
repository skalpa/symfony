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
 */
class AtomicType extends Type implements AtomicTypeInterface
{
    /**
     * @var list<TVariables>
     */
    private readonly array $variableTypes;

    /**
     * @param TPrimitive $typeIdentifier
     * @param TVariables ...$variableTypes
     */
    public function __construct(
        private readonly TypeIdentifier $typeIdentifier,
        private readonly string $name,
        Type ...$variableTypes
    ) {
        $this->variableTypes = $variableTypes;
    }

    final public function getTypeIdentifier(): TypeIdentifier
    {
        return $this->typeIdentifier;
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

    final public function isGeneric(): bool
    {
        return 0 !== count($this->variableTypes);
    }

    public function accepts(Type $type): bool
    {
        if ($this::class !== $type::class || $this->name !== $type->name || $this->getTypeIdentifier() !== $type->getTypeIdentifier()) {
            return false;
        }

        return $this->acceptVariables(...$type->getVariableTypes());
    }

    public function __toString(): string
    {
        return $this->name.$this->renderVariableTypes();
    }

    protected function renderVariableTypes(): string
    {
        return $this->variableTypes ? '<'.implode(', ', $this->variableTypes).'>' : '';
    }

    protected function acceptVariables(Type ...$types): bool
    {
        foreach ($this->getVariableTypes() as $n => $variableType) {
            if (!isset($types[$n]) || !$variableType->accepts($types[$n])) {
                return false;
            }
        }

        return true;
    }
}
