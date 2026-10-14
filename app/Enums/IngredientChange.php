<?php

namespace App\Enums;

enum IngredientChange: string
{
    case Removed = 'removed';
    case Added = 'added';
}
