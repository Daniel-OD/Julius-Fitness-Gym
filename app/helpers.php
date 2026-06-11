<?php

use App\Helpers\Helpers;

if (! function_exists('gym_name')) {
    function gym_name(): string
    {
        return Helpers::gymName();
    }
}
