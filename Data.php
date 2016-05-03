<?php

namespace cebe\jsonstore;

/**
 * A storable data objects.
 */
interface Data
{
    /**
     * @return string the serialized representation of the object.
     */
    public function serialize();

    /**
     * @param string $data the serialized representation of the object.
     * @return object the reassembled object.
     */
    public static function unserialize($data);
}
