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
 * Represents a named atomic type.
 *
 * @template TPrimitive of TypeIdentifier
 * @template TVariables of Type
 *
 * @extends TypeInterface<TPrimitive, TVariables>
 */
interface AtomicTypeInterface extends TypeInterface
{
    /**
     * Variable parts of this type (if any).
     *
     * @return array<TVariables>
     */
    public function getVariableTypes(): array;

    /**
     * Whether this type has any variable part.
     */
    public function isGeneric(): bool;
}
