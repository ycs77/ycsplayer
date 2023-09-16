<?php

namespace App\Models\Concerns;

use Hashids\HashidsInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property string $hash_id
 */
trait HasHashId
{
    /**
     * The model hash id.
     *
     * @var string
     */
    protected $hashId;

    /**
     * Get model hash id connection name.
     *
     * Example:
     *
     * The User Model use hash id connection,
     *
     * config/hashids.php:
     *
     *     'connections' => [
     *         ...
     *         'users' => [
     *             'salt' => 'df5g4d5erg',
     *             'length' => 10,
     *         ],
     *     ],
     */
    protected function hashids(): HashidsInterface
    {
        return Hashids::connection(
            in_array($this->getTable(), array_keys(config('hashids.connections')))
                ? $this->getTable()
                : null
        );
    }

    /**
     * Interact with the hash id.
     */
    protected function hashId(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if ($this->hashId) {
                    return $this->hashId;
                }

                return $this->hashId = $this->hashids()->encode($attributes['id']);
            },
            set: function (mixed $value) {
                $this->hashId = $value;
            },
        );
    }

    /**
     * Encode hash ID.
     */
    public static function encodeHashId(int|string $id): string
    {
        return static::make()->hashids()->encode($id);
    }

    /**
     * Decode hashed ID.
     */
    public static function decodeHashId(string $id): ?int
    {
        return static::make()->hashids()->decode($id)[0] ?? null;
    }

    /**
     * Decode the model hash id to id and retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if (is_null($value) || is_numeric($value)) {
            return null;
        }

        if (! $value = current($this->hashids()->decode($value))) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Get the value of the model's route key from model hash id.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->hash_id;
    }
}
