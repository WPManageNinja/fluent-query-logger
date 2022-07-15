<?php

namespace FluentQueryLogger\App\Models;

use FluentQueryLogger\App\Models\Model;

class DbLog extends Model
{
    protected $table = 'fql_logs';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'counter' => 'int',
        'total_counter' => 'int',
        'result' => 'int',
        'ltime' => 'float'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'sql',
        'trace',
        'hash',
        'caller',
        'last_url',
        'last_data',
        'ltime',
        'type',
        'result',
        'counter',
        'total_counter',
        'context_type',
        'context',
        'status'
    ];

    public function setLastDataAttribute($data)
    {
        $this->attributes['last_data'] = \maybe_serialize($data);
    }

    public function getLastDataAttribute($data)
    {
        return maybe_unserialize($data);
    }
}
