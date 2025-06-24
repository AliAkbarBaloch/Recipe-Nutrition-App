<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Main recipes table - stores the basic recipe info and calculated nutrition totals
 * 
 * This is the parent table that ingredients and steps reference.
 * Nutrition values get calculated from the ingredients when a recipe is saved.
 */
class CreateRecipesTable extends Migration
{
    /**
     * Create the recipes table with all the fields we need
     */
    public function up()
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            
            // Basic recipe info
            $table->string('name');                              
            $table->text('description')->nullable();             
            $table->integer('servings')->default(1);             
            
            // Time tracking in minutes
            $table->integer('prep_time')->nullable();            
            $table->integer('cook_time')->nullable();            
            
            // Nutrition totals - calculated from ingredients
            $table->decimal('total_calories', 8, 2)->default(0); 
            $table->decimal('total_carbs', 8, 2)->default(0);    
            $table->decimal('total_fat', 8, 2)->default(0);      
            $table->decimal('total_protein', 8, 2)->default(0);  
            
            $table->timestamps();
        });
    }

    /**
     * Drop the recipes table when rolling back
     */
    public function down()
    {
        Schema::dropIfExists('recipes');
    }
}