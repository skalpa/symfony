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
 * @template T of ObjectType|GenericType<ObjectType>|CollectionType<GenericType<ObjectType>>
 * @implements CompositeTypeInterface<T>
 *
 * @experimental
 */
final class IntersectionType extends CompositeType
{
    /**
     * @param list<T> $types
     */
    public function __construct(Type ...$types)
    {
        if (\count($types) < 2) {
            throw new InvalidArgumentException(\sprintf('"%s" expects at least 2 types.', self::class));
        }
        // Only accept named object types
        foreach ($types as $t) {
            if (TypeIdentifier::OBJECT !== $t->getTypeIdentifier() && '' === $t->getName()) {
                throw new InvalidArgumentException(\sprintf('Intersections must be made of named object types, got "%s".', (string) $t));
            }
        }
        // All subtypes are sorted alphabetically
        usort($types, fn (Type $a, Type $b): int => $a->getName() <=> $b->getName());

        parent::__construct(TypeIdentifier::OBJECT, false, '&', CompositeMatchMode::ALL, ...array_values(array_unique($types)));
    }
}
