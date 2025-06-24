<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Recipe model - handles all the recipe data and relationships
 * 
 * Stores the basic recipe info plus calculated nutrition totals
 * Links to ingredients and cooking steps through relationships
 */
class Recipe extends Model
{
    use HasFactory;

    /**
     * What can be mass assigned when creating/updating recipes
     */
    protected $fillable = [
        'name',           // Recipe name
        'description',    // Optional description
        'servings',       // How many servings it makes
        'prep_time',      // Prep time in minutes
        'cook_time',      // Cook time in minutes
        'total_calories', // Calculated nutrition totals
        'total_carbs',    
        'total_fat',      
        'total_protein'   
    ];

    /**
     * Cast nutrition values to decimals for precision
     */
    protected $casts = [
        'total_calories' => 'decimal:2', // Precise calorie calculation
        'total_carbs' => 'decimal:2',    // Precise carbohydrate calculation
        'total_fat' => 'decimal:2',      // Precise fat calculation
        'total_protein' => 'decimal:2',  // Precise protein calculation
    ];

    /**
     * Get all ingredients for this recipe
     */
    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Get all cooking steps for this recipe, in order
     */
    public function steps()
    {
        return $this->hasMany(RecipeStep::class)->orderBy('step_number');
    }

    /**
     * Calculate and update the total nutritional values for this recipe.
     * 
     * This method iterates through all ingredients and calculates:
     * - Total calories (using 4 cal/g for carbs and protein, 9 cal/g for fat)
     * - Total carbohydrates in grams
     * - Total fat in grams
     * - Total protein in grams
     * 
     * The calculation is based on nutritional values per 100g of each ingredient
     * multiplied by the actual quantity used in the recipe.
     *
     * @return void
     */
    public function calculateNutrition()
    {
        // Initialize nutrition totals
        $totalCalories = 0;
        $totalCarbs = 0;
        $totalFat = 0;
        $totalProtein = 0;

        // Loop through each ingredient in the recipe
        foreach ($this->ingredients as $ingredient) {
            // Convert ingredient quantity to factor based on 100g
            // If ingredient quantity is 150g, factor = 1.5
            $quantityFactor = $ingredient->quantity / 100;
            
            // Add carbohydrates (multiplied by quantity factor)
            $totalCarbs += ($ingredient->carbs_per_100g ?? 0) * $quantityFactor;
            
            // Add fat content (multiplied by quantity factor)
            $totalFat += ($ingredient->fat_per_100g ?? 0) * $quantityFactor;
            
            // Add protein content (multiplied by quantity factor)
            $totalProtein += ($ingredient->protein_per_100g ?? 0) * $quantityFactor;
            
            // Calculate calories using standard conversion:
            // - Carbohydrates: 4 calories per gram
            // - Protein: 4 calories per gram
            // - Fat: 9 calories per gram
            $totalCalories += (($ingredient->carbs_per_100g ?? 0) * 4 + 
                              ($ingredient->protein_per_100g ?? 0) * 4 + 
                              ($ingredient->fat_per_100g ?? 0) * 9) * $quantityFactor;
        }

        // Update the recipe with calculated nutritional values
        // Round to 2 decimal places for precision
        $this->update([
            'total_calories' => round($totalCalories, 2),
            'total_carbs' => round($totalCarbs, 2),
            'total_fat' => round($totalFat, 2),
            'total_protein' => round($totalProtein, 2),
        ]);
    }
}