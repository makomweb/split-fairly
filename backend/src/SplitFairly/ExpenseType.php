<?php

declare(strict_types=1);

namespace App\SplitFairly;

enum ExpenseType: string
{
    case GROCERIES = 'Groceries';
    case NON_FOOD = 'Non-Food';
    case LENT = 'Lent';
}
