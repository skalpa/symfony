<?php
declare(strict_types=1);

namespace Symfony\Component\TypeInfo\Type;

enum IntRangeName: string
{
    case INT = 'int';

    /**
     * int<1, max>
     */
    case POSITIVE_INT = 'positive-int';

    /**
     * int<min, -1>
     */
    case NEGATIVE_INT = 'negative-int';

    /**
     * int<min, 0>
     */
    case NON_POSITIVE_INT = 'non-positive-int';

    /**
     * int<0, max>
     */
    case NON_NEGATIVE_INT = 'non-negative-int';
}
