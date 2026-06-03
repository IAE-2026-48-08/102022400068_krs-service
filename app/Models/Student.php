<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
    ];

    public function krsItems()
    {
        return $this->hasMany(KrsItem::class);
    }
}
