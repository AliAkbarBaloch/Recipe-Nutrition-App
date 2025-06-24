<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * CreateRecipeStepsTable Migration
 * 
 * This migration creates the 'recipe_steps' table which stores the sequential
 * preparation instructions for each recipe.
 * 
 * Each step belongs to a specific recipe (many-to-one relationship) and has
 * a step number to maintain the correct order of instructions.
 * When a recipe is deleted, all its steps are automatically deleted (cascade).
 */
class CreateRecipeStepsTable extends Migration
{
    /**
     * Run the migration to create the recipe_steps table.
     * 
     * This method defines the structure for storing sequential preparation
     * instructions that guide users through cooking the recipe.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipe_steps', function (Blueprint $table) {
            // Primary key - auto-incrementing ID
            $table->id();
            
            // Foreign key relationship to recipes table
            // constrained() automatically references the 'recipes' table based on naming convention
            // onDelete('cascade') ensures steps are deleted when recipe is deleted
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            
            // Step sequencing and content
            $table->integer('step_number');                      // Sequential order (1, 2, 3, etc.)
            $table->text('instruction');                         // Detailed preparation instruction
            
            // Laravel's automatic timestamp columns (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration by dropping the recipe_steps table.
     * 
     * This method is called when rolling back the migration.
     * It safely removes the recipe_steps table if it exists.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipe_steps');
    }
}