<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Each ingredient in a recipe - tracks what you need and nutrition info
 * 
 * I went with storing nutrition data per 100g since that's what most APIs give us.
 * Makes it easier to calculate total nutrition for the actual quantity used.
 */
class RecipeIngredient extends Model
{
    use HasFactory;

    /**
     * What we can safely mass assign when creating ingredients
     * Basically everything except the timestamps and ID
     */
    protected $fillable = [
        'recipe_id',         
        'name',              
        'quantity',          
        'unit',              
        'carbs_per_100g',    
        'fat_per_100g',      
        'protein_per_100g',  
        'calories_per_100g'  
    ];

    /**
     * Cast these to decimals for proper nutrition calculations
     * Two decimal places should be enough precision for cooking
     */
    protected $casts = [
        'quantity' => 'decimal:2',          
        'carbs_per_100g' => 'decimal:2',    
        'fat_per_100g' => 'decimal:2',      
        'protein_per_100g' => 'decimal:2',  
        'calories_per_100g' => 'decimal:2', 
    ];

    /**
     * Belongs to a recipe - standard many-to-one relationship
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}