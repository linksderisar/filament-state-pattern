<?php

namespace Linksderisar\FilamentStatePattern\Classes;

use Illuminate\Support\HtmlString;
use Spatie\ModelStates\Transition;

abstract class TransitionWithFilamentSupport extends Transition
{



    public static function filamentStateFormDescription($record = null): HtmlString|string
    {
        return '';
    }

    public static function filamentFields(): array
    {
        return [];
    }

    public static function fillFilamentFormWithDefaultValues(): array
    {
        return [];
    }
}
