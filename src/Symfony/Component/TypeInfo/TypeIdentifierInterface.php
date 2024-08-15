<?php

namespace Symfony\Component\TypeInfo;

interface TypeIdentifierInterface extends \BackedEnum
{
    public function accepts(TypeIdentifierInterface $other, array $classNames = []): bool;

    public function toString(): string;
}
