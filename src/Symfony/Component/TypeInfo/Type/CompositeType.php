<?php
declare(strict_types=1);

namespace Symfony\Component\TypeInfo\Type;

use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\TypeIdentifier;

/**
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @internal
 *
 * @template T of Type
 */
class CompositeType extends Type implements CompositeTypeInterface
{
    private string $name;
    /**
     * @var list<T>
     */
    protected readonly array $types;

    public function __construct(
        private readonly TypeIdentifier     $typeIdentifier,
        private readonly bool               $isNullable,
        private readonly string             $glue,
        private readonly CompositeMatchMode $matchMode,
        Type ...$types,
    ) {
        $this->name = '';
        $this->types = $types;
    }

    public function getTypeIdentifier(): TypeIdentifier
    {
        return $this->typeIdentifier;
    }

    public function getName(): string
    {
        return $this->name ?: (string) $this;
    }

    /**
     * Whether this union represents a nullable type.
     *
     * A union is nullable if it contains "null" (as it may not contain unions or mixed).
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    public function asNonNullable(): Type
    {
        if (!$this->isNullable) {
            return $this;
        }
        $nonNullableTypes = [];
        foreach ($this->types as $type) {
            if (TypeIdentifier::NULL !== $type->getTypeIdentifier()) {
                $nonNullableTypes[] = $type->isNullable() ? $type->asNonNullable() : $type;
            }
        }

        return 1 < \count($nonNullableTypes) ? new self(...$nonNullableTypes) : $nonNullableTypes[0];
    }

    /**
     * @return list<T>
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * @param callable(T): bool $callable
     * @return list<T>
     */
    public function filter(callable $callable): array
    {
        return $this->filterTypes($callable, ...$this->getTypes());
    }

    public function reduce(callable $callable, mixed $initial = null): mixed
    {
        return array_reduce($this->getTypes(), $callable, $initial);
    }

    public function accepts(Type $type): bool
    {
        return CompositeMatchMode::ANY === $this->matchMode ? $this->anyAccepts($type) : $this->allAccept($type);
    }

    public function anyAccepts(Type $type): bool
    {
        foreach ($this->types as $member) {
            if ($member->accepts($type)) {
                return true;
            }
        }

        return false;
    }

    public function allAccept(Type $type): bool
    {
        foreach ($this->types as $member) {
            if (!$member->accepts($type)) {
                return false;
            }
        }

        return true;
    }

    public function __toString(): string
    {
        $string = '';
        $glue = '';
        foreach ($this->types as $t) {
            $string .= $glue.($t instanceof CompositeTypeInterface && !$t instanceof self ? '('.((string) $t).')' : ((string) $t));
            $glue = $this->glue;
        }

        return $string;
    }

    /**
     * @param callable(T): bool $callable
     * @param array<T>          $types
     * @return list<T>
     */
    protected function filterTypes(callable $callable, Type ...$types): array
    {
        return array_values(array_filter($types, $callable));
    }
}
