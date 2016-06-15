<?php

namespace Bluora\LarvelModelTraits;

trait UuidColumnTrait
{
    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
            case 'uuid':
                $value = unpack('H*', $value);

                return strtolower(preg_replace('/([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})/', '$1-$2-$3-$4-$5', $value[1]));
            default:
                return parent::castAttribute($key, $value);
        }
    }

    /**
     * Lookup the model using a UUID.
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function whereUuid($column, $value)
    {
        return self::whereUuidIn($column, $value);
    }

    /**
     * Lookup the models using a UUID.
     *
     * @param string $column
     * @param mixed  $value
     *
     * @return mixed
     */
    public static function whereUuidIn($column, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }
        if (count($value) == 0) {
            return new static();
        }
        $value = str_replace('-', '', $value);
        $value = preg_replace('/([a-zA-Z0-9].*)/', "UNHEX('$1')", $value);
        $value = implode(',', $value);
        $sql = sprintf("$column IN (%s)", $value);

        return parent::__callStatic('whereRaw', [$sql]);
    }
}
