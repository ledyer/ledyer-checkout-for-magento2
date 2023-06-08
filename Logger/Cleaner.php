<?php

namespace Ledyer\Payment\Logger;

class Cleaner
{
    /**
     * List of keys that shouldn't be logged
     *
     * @var array
     */
    public $restrictedKeys = [
        'access_token',
        'companyId',
        'firstname',
        'lastname',
        'email',
        'phone'
    ];

    /**
     * Value that replaces restricted values
     *
     * @var string
     */
    public $replaceValue = '** Removed value **';

    /**
     * Replaces restricted values
     *
     * @param array $input
     * @return array
     */
    public function clean(array $input)
    {
        $keys = array_keys($input);
        foreach ($keys as $key) {
            if (is_array($input[$key])) {
                $input[$key] = $this->clean($input[$key]);
            } elseif (in_array($key, $this->restrictedKeys, true)) {
                $input[$key] = $this->replaceValue;
            }
        }

        return $input;
    }
}
