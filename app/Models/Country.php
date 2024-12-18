<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    // Define the table name (if it's not the plural of the model name)
    protected $table = 'countries';

    // Specify the fillable attributes for mass assignment
    protected $fillable = [
        'country_code',
        'country_name',
        'country_status'
    ];

    /**
     * Define the relationship with the MerchantShareholder model.
     * A country can have many shareholders.
     */
    public function shareholders()
    {
        return $this->hasMany(MerchantShareholder::class, 'country');
    }

    // Add inverse relationship
    public function merchants()
    {
        return $this->belongsToMany(Merchant::class, 'merchant_operating_countries');
    }


    /**
     * Get the name of the country by country ID
     *
     * @param int $countryID
     * @return string
     */
    public static function getCountryNameByID(int $countryID): string
    {
        return self::where('id', $countryID)->value('country_name');
    }

}
