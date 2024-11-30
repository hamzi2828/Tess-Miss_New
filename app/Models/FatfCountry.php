<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FatfCountry extends Model
{
    use HasFactory;

    protected $table = 'fatf_countries';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = true;

    protected $fillable = [
        'country_code',
        'country_name',
        'country_status',
        'created_at',
        'updated_at',
    ];

    // Add casts if needed for specific fields
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
