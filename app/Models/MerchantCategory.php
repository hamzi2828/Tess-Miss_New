<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantCategory extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'title', 'added_by'];

    /**
     * Parent category relationship (self-referencing).
     */
    public function parentCategory()
    {
        return $this->belongsTo(MerchantCategory::class, 'parent_id');
    }

    /**
     * Children categories relationship (self-referencing).
     */
    public function childCategories()
    {
        return $this->hasMany(MerchantCategory::class, 'parent_id');
    }

    /**
     * User who added the category.
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Relationship to the Merchant model.
     * A category can have many merchants.
     */
    public function merchants()
    {
        return $this->hasMany(Merchant::class, 'merchant_category');
    }


    public static function getParentCategoryTitleById($id)
    {

        // Find the category by its ID
        $category = self::find($id);
        if ($category && $category->parent_id) {
            // Find the parent category using parent_id
            $parentCategory = self::find($category->parent_id);
            return $parentCategory; // Return parent category details
        }

        return null;
    }
}
