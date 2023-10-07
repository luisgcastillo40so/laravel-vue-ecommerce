<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use HasFactory;
    use HasSlug;
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'active', 'parent_id', 'created_by', 'updated_by'];

    public static function getActiveAsTree($resourceClass = false)
    {
        $categories = Category::where('active', true)->orderBy('parent_id')->get();
        return self::buildCategoryTree($categories, null, $resourceClass);
    }

    private static function buildCategoryTree($categories, $parentId = null, $resourceClass = false)
    {
        $categoryTree = [];

        foreach ($categories as $category) {
            if ($category->parent_id === $parentId) {
                $children = self::buildCategoryTree($categories, $category->id, $resourceClass);
                if ($children) {
                    $category->setAttribute('children', $children);
                }
                $categoryTree[] = $resourceClass ? new $resourceClass($category) : $category;
            }
        }

        return $categoryTree;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
