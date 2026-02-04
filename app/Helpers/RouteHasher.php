<?php

namespace App\Helpers;

use Vinkla\Hashids\Facades\Hashids;

class RouteHasher
{
    /**
     * Codifica un ID numérico a un hash de ruta
     */
    public static function encode($id)
    {
        if (empty($id)) {
            return null;
        }
        if (!class_exists(\Vinkla\Hashids\Facades\Hashids::class)) {
            return (string) $id;
        }
        return Hashids::encode($id);
    }

    /**
     * Decodifica un hash de ruta a su ID numérico original
     */
    public static function decode($hash)
    {
        if (empty($hash)) {
            return null;
        }
        if (!class_exists(\Vinkla\Hashids\Facades\Hashids::class)) {
            return is_numeric($hash) ? (int) $hash : null;
        }
        $decoded = Hashids::decode($hash);
        return !empty($decoded) ? $decoded[0] : null;
    }

    /**
     * Valida si un hash es válido
     */
    public static function isValid($hash)
    {
        if (empty($hash)) {
            return false;
        }
        if (!class_exists(\Vinkla\Hashids\Facades\Hashids::class)) {
            return is_numeric($hash);
        }
        $decoded = Hashids::decode($hash);
        return !empty($decoded);
    }
}
