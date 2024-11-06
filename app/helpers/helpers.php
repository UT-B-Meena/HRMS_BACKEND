<?php

if (!function_exists('getErrorMessage')) {
    function getErrorMessage($errors, $field)
    {
        return $errors->has($field) ? $errors->first($field) : '';
    }
}
