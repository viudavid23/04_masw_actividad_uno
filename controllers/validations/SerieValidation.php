<?php
class SerieValidation {

    public static function isInvalidIdDataType($id): bool {
        return !(is_numeric($id) && $id > 0);
    }
}
