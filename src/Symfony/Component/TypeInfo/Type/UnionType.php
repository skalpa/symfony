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
 * @author Mathias Arlaud <mathias.arlaud@gmail.com>
 * @author Baptiste Leduc <baptiste.leduc@gmail.com>
 *
 * @template T of Type
 * @implements CompositeTypeInterface<T>
 *
 * @experimental
 */
final class UnionType extends CompositeType
{
    public function __construct(Type ...$types)
    {
        if (\count($types) < 2) {
            throw new InvalidArgumentException(\sprintf('"%s" expects at least 2 types.', self::class));
        }

        $nullable = false;
        $hasClassType = false;
        $hasObject = false;
        $identifiers = [];
        foreach ($types as $t) {
            if ($t instanceof CompositeTypeInterface && !$t instanceof IntersectionType) {
                throw new InvalidArgumentException(\sprintf('Cannot set "%s" as a "%s" part.', $t, self::class));
            }
            if ($t->getTypeIdentifier()->isStandalone()) {
                throw new InvalidArgumentException(\sprintf('Type %s can only be used as a standalone type', $t->getTypeIdentifier()->value));
            }
            if (TypeIdentifier::NULL === $t->getTypeIdentifier()) {
                $nullable = true;
            }
            $hasClassType = $hasClassType || TypeIdentifier::OBJECT === $t->getTypeIdentifier() && '' === $t->getName();
            $hasObject = $hasObject || TypeIdentifier::OBJECT === $t->getTypeIdentifier() && '' !== $t->getName();
            $identifiers[$t->getTypeIdentifier()->name] = $t->getTypeIdentifier();
        }
        if ($hasClassType && $hasObject) {
            throw new InvalidArgumentException('Union contains both object and a class type, which is redundant.');
        }

        $types = array_values(array_unique($types));

        // bool, true and false cannot be composed together (use same errors as PHP in similar cases)
        $booleanTypes = $this->filterTypes(fn(Type $t): bool => $t->getTypeIdentifier()->isBool(), ...$types);
        if (1 < \count($booleanTypes)) {
            throw \in_array(TypeIdentifier::TRUE, $identifiers, true) && \in_array(TypeIdentifier::FALSE, $identifiers, true)
                ? new InvalidArgumentException('Union type contains both true and false, bool should be used instead.')
                : new InvalidArgumentException('Duplicate boolean type is redundant.');
        }

        $typeIdentifier = 1 === count($identifiers) ? current($identifiers) : TypeIdentifier::MIXED;

        parent::__construct($typeIdentifier, $nullable, '|', CompositeMatchMode::ANY, ...$this->sortSubtypesForRendering(...$types));
    }

    /**
     * Sort intersections first, then classes, then builtins and order alphabetically within each group.
     *
     * @param array<T> $types
     * @return list<T>
     */
    private function sortSubtypesForRendering(Type ...$types): array
    {
        $prefix = function (Type $t): string {
            return match ($t::class) {
                    IntersectionType::class => '!!',
                    ObjectType::class => '!',
                    default => '',
                }.$t;
        };
        usort($types, fn (Type $a, Type $b): int => $prefix($a) <=> $prefix($b));

        return array_values($types);
    }
}
