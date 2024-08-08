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
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * Represents a key/value collection type.
 *
 * It proxies every method to the main type and adds methods related to key and value types.
 *
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of BuiltinType<TypeIdentifier::ARRAY>|BuiltinType<TypeIdentifier::ITERABLE>|ObjectType|GenericType
 *
 * @experimental
 */
final class CollectionType extends Type implements AtomicTypeInterface
{
    /**
     * @var class-string|'array'|'iterable'
     */
    private readonly string $name;

    /**
     * @param T $type
     */
    public function __construct(
        TypeIdentifier|string $type,
        private readonly bool $isList = false,
        private readonly bool $allowEmpty = true,
        Type ...$variableTypes,
    ) {
        if ('' === $type || $type instanceof TypeIdentifier && !\in_array($type, [TypeIdentifier::ARRAY, TypeIdentifier::ITERABLE], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid collection type. Expected array,iterable or class-string, got "%s".', $type->value));
        }
        parent::__construct(is_string($type) ? TypeIdentifier::OBJECT : $type, ...$variableTypes);

        if (!$isList && 2 <= count($variableTypes)) {
            $keyType = $variableTypes[0];
            $isValid =
                ($isList && $keyType instanceof BuiltinType && TypeIdentifier::INT === $keyType->getTypeIdentifier()) ||
                ($keyType instanceof BuiltinType && \in_array($keyType->getTypeIdentifier(), [TypeIdentifier::INT, TypeIdentifier::STRING], true)) ||
                ($keyType instanceof UnionType && 2 === count($keyType->getTypes()) && 'int|string' === (string) $keyType)
            ;
            if (!$isValid) {
                $msg = '"%s" is not a valid '.($isList ? 'list' : 'collection').' key type';
                throw new InvalidArgumentException(\sprintf($msg, (string) $keyType));
            }
        }
        $this->name = is_string($type) ? $type : $type->value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    public function canBeEmpty(): bool
    {
        return $this->allowEmpty;
    }

    public function getCollectionKeyType(): BuiltinType|UnionType
    {
        if (2 <= count($this->getVariableTypes())) {
            return $this->getVariableTypes()[0];
        }

        return $this->isList ? self::int() : self::union(self::int(), self::string());
    }

    public function getCollectionValueType(): Type
    {
        return match (\count($this->getVariableTypes())) {
            0 => self::mixed(),
            1 => $this->getVariableTypes()[0],
            default => $this->getVariableTypes()[1],
        };
    }

    public function accepts(Type $type): bool
    {
    }

    public function __toString(): string
    {
        return $this->name.$this->renderVariableTypes();
    }
}
