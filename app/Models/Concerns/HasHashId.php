<?php

namespace App\Models\Concerns;

use Hashids\HashidsInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Vinkla\Hashids\Facades\Hashids;

/**
 * @property-read string $hash_id
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
    public function hashId(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $this->hashids()->encode($attributes['id']),
        )->shouldCache();
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
            return;
        }

        if (! $value = current($this->hashids()->decode($value))) {
            return;
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
