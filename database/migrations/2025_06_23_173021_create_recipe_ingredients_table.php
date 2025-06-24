<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Recipe ingredients table - tracks what goes into each recipe
 * 
 * Stores the individual ingredients with quantities and nutrition info.
 * Links to recipes via foreign key, cascades on delete.
 */
class CreateRecipeIngredientsTable extends Migration
{
    /**
     * Run the migration to create the recipe_ingredients table.
     * 
     * This method defines the structure for storing ingredient information
     * including quantities and nutritional data retrieved from the nutrition API.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            // Primary key - auto-incrementing ID
            $table->id();
            
            // Foreign key relationship to recipes table
            // constrained() automatically references the 'recipes' table based on naming convention
            // onDelete('cascade') ensures ingredients are deleted when recipe is deleted
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            
            // Ingredient basic information
            $table->string('name');                              // Ingredient name (e.g., "Chicken Breast")
            $table->decimal('quantity', 8, 2);                  // Amount used in recipe (e.g., 250.00)
            $table->string('unit');                              // Unit of measurement (e.g., "g", "ml", "cups")
            
            // Nutritional information per 100g (retrieved from nutrition API)
            // Using nullable() because this data might not be available for all ingredients
            // Using decimal(8,2) for precision: max 999,999.99 with 2 decimal places
            $table->decimal('carbs_per_100g', 8, 2)->nullable();    // Carbohydrates per 100g
            $table->decimal('fat_per_100g', 8, 2)->nullable();      // Fat content per 100g
            $table->decimal('protein_per_100g', 8, 2)->nullable();  // Protein content per 100g
            $table->decimal('calories_per_100g', 8, 2)->nullable(); // Calories per 100g
            
            // Laravel's automatic timestamp columns (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration by dropping the recipe_ingredients table.
     * 
     * This method is called when rolling back the migration.
     * It safely removes the recipe_ingredients table if it exists.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe_ingredients');
    }
}