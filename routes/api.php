<?php

use App\Http\Controllers\Api\RecipeController;
use App\Http\Controllers\Api\NutritionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| All my API endpoints for the recipe app. These get the '/api' prefix
| automatically, so '/recipes' becomes '/api/recipes' when called.
|
| The frontend Angular app hits these endpoints to manage recipes and
| get nutrition data.
|
*/

/*
|--------------------------------------------------------------------------
| Recipe Routes
|--------------------------------------------------------------------------
|
| Basic CRUD operations for recipes - create, read, update, delete.
| Plus some extra endpoints for nutrition calculations.
*/
Route::prefix('recipes')->group(function () {
    // Get all recipes
    Route::get('/', [RecipeController::class, 'index']);
    
    // Create a new recipe
    Route::post('/', [RecipeController::class, 'store']);
    
    // Get one specific recipe
    Route::get('/{id}', [RecipeController::class, 'show']);
    
    // Update a recipe
    Route::put('/{id}', [RecipeController::class, 'update']);
    
    // Delete a recipe
    Route::delete('/{id}', [RecipeController::class, 'destroy']);
    
    // Recalculate nutrition for all recipes
    Route::post('/recalculate-nutrition', [RecipeController::class, 'recalculateAllNutrition']);
    
    // Recalculate nutrition for one specific recipe
    Route::post('/{id}/recalculate-nutrition', [RecipeController::class, 'recalculateNutrition']);
});

/*
|--------------------------------------------------------------------------
| Nutrition API Routes
|--------------------------------------------------------------------------
|
| These routes talk to the external nutrition API to get ingredient data.
| Basically acting as a middleman between my frontend and their API.
|
*/
Route::prefix('nutrition')->group(function () {
    // Get all ingredients from nutrition API
    Route::get('/ingredients', [NutritionController::class, 'getAllIngredients']);
    
    // Search for a specific ingredient
    Route::get('/ingredients/search', [NutritionController::class, 'searchIngredient']);
    
    // Add a new ingredient to the nutrition API
    Route::post('/ingredients', [NutritionController::class, 'addIngredient']);
    
    // Submit the required ingredients (Quinoa & Greek Yogurt)
    Route::post('/ingredients/submit-required', [NutritionController::class, 'submitRequiredIngredients']);
});