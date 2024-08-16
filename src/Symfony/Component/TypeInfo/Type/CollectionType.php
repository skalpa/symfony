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
use Symfony\Component\TypeInfo\TypeIdentifierInterface;

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
final class CollectionType extends AtomicType
{
    /**
     * @var array<string>
     */
    private readonly array $classNames;

    /**
     * @param T $type
     */
    public function __construct(
        private readonly TypeIdentifier|string $type,
        private readonly bool $isList = false,
        private readonly bool $allowEmpty = false,
        Type ...$variableTypes,
    ) {
        if ('' === $type || $type instanceof TypeIdentifier && !\in_array($type, [TypeIdentifier::ARRAY, TypeIdentifier::ITERABLE], true)) {
            throw new InvalidArgumentException(\sprintf('Invalid collection type. Expected array,iterable or class-string, got "%s".', $type->value));
        }

        parent::__construct(
            $type instanceof TypeIdentifier ? $type : TypeIdentifier::OBJECT,
            $type instanceof TypeIdentifier ? $type->toString() : $type,
            false,
            false,
            ...$variableTypes,
        );

        if (!$isList && 2 <= count($variableTypes)) {
            $keyType = $variableTypes[0];
            if (!$keyType instanceof UnionType) {
                return $keyType instanceof AtomicType && \in_array($keyType->getTypeIdentifier(), [TypeIdentifier::INT, TypeIdentifier::STRING], true);
            }
            $typeIdentifiers = array_unique(array_map(fn(Type $type): TypeIdentifier => $type->getTypeIdentifier(), $keyType->getTypes()));

            $isValid =
                ($keyType instanceof AtomicType && \in_array($keyType->getTypeIdentifier(), [TypeIdentifier::INT, TypeIdentifier::STRING], true)) ||
                ($keyType instanceof UnionType && 2 === count($keyType->getTypes()) && 'int|string' === (string) $keyType)
            ;
            if (!$isValid) {
                throw new InvalidArgumentException(\sprintf('"%s" is not a valid list key type.', (string) $keyType));
            }
        }



        if ($this->isList()) {
            $keyType = $this->getCollectionKeyType();

            if (!$keyType instanceof BuiltinType || TypeIdentifier::INT !== $keyType->getTypeIdentifier()) {
                throw new InvalidArgumentException(\sprintf('"%s" is not a valid list key type.', (string) $keyType));
            }
        }
    }

    public function getBaseType(): BuiltinType|ObjectType
    {
        return $this->getType()->getBaseType();
    }

    /**
     * @return T
     */
    public function getType(): BuiltinType|ObjectType|GenericType
    {
        return $this->type;
    }

    public function isA(TypeIdentifier|string $subject): bool
    {
        return $this->getType()->isA($subject);
    }

    public function isList(): bool
    {
        return $this->isList;
    }

    public function asNonNullable(): self
    {
        return $this;
    }

    public function getCollectionKeyType(): Type
    {
        $defaultCollectionKeyType = self::union(self::int(), self::string());

        if ($this->type instanceof GenericType) {
            return match (\count($this->type->getVariableTypes())) {
                2 => $this->type->getVariableTypes()[0],
                1 => self::int(),
                default => $defaultCollectionKeyType,
            };
        }

        return $defaultCollectionKeyType;
    }

    public function getCollectionValueType(): Type
    {
        $defaultCollectionValueType = self::mixed();

        if ($this->type instanceof GenericType) {
            return match (\count($this->type->getVariableTypes())) {
                2 => $this->type->getVariableTypes()[1],
                1 => $this->type->getVariableTypes()[0],
                default => $defaultCollectionValueType,
            };
        }

        return $defaultCollectionValueType;
    }

    /**
     * Proxies all method calls to the original type.
     *
     * @param list<mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->type->{$method}(...$arguments);
    }

    private function validateKeyType(Type $type): void
    {

    }
}
