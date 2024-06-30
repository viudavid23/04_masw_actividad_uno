<?php
class SerieValidation {

    public static function validateIdDataType($id): bool {
        return !(is_numeric($id) && $id > 0);
    }
}
