<?php
class PlatformValidation {

    public static function validateString($field): bool {
        return empty($field) || !preg_match('/^[\p{L}]+(?:[\s]*[\p{L}]+)*$/u', $field);
    }

    public static function validateIdDataType($id): bool {
        return !(is_numeric($id) && $id > 0);
    }
}
