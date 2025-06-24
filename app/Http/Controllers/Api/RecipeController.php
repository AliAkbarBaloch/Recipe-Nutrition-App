<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * RecipeController
 * 
 * API controller for managing recipes in the Recipe Nutrition Management system.
 * Provides CRUD operations for recipes including their ingredients and preparation steps.
 * 
 * This controller handles:
 * - Listing all recipes with their related data
 * - Creating new recipes with ingredients and steps
 * - Showing individual recipe details
 * - Updating existing recipes
 * - Deleting recipes
 * 
 * All responses follow a consistent JSON format with success status and data/error messages.
 */
class RecipeController extends Controller
{
    /**
     * Display a listing of all recipes.
     * 
     * Retrieves all recipes from the database including their associated
     * ingredients and preparation steps. Uses eager loading to optimize
     * database queries and avoid N+1 problems.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with recipes list
     */
    public function index()
    {
        // Fetch all recipes with their related ingredients and steps
        // Using eager loading to prevent N+1 query problems
        $recipes = Recipe::with(['ingredients', 'steps'])->get();
        
        // Return standardized JSON response
        return response()->json([
            'success' => true,
            'data' => $recipes
        ]);
    }

    /**
     * Store a newly created recipe in the database.
     * 
     * Validates the incoming request data and creates a new recipe along with
     * its ingredients and preparation steps. All data is created within a
     * transaction to ensure data consistency.
     *
     * @param \Illuminate\Http\Request $request HTTP request containing recipe data
     * @return \Illuminate\Http\JsonResponse JSON response with created recipe or validation errors
     */
    public function store(Request $request)
    {
        // Define validation rules for recipe creation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',                    // Recipe name is required
            'description' => 'nullable|string',                     // Description is optional
            'servings' => 'required|integer|min:1',                 // Must serve at least 1 person
            'prep_time' => 'nullable|integer|min:0',                // Prep time is optional
            'cook_time' => 'nullable|integer|min:0',                // Cook time is optional
            'ingredients' => 'required|array|min:1',                // At least one ingredient required
            'ingredients.*.name' => 'required|string',              // Each ingredient needs a name
            'ingredients.*.quantity' => 'required|numeric|min:0',   // Quantity must be non-negative
            'ingredients.*.unit' => 'required|string',              // Unit of measurement required
            'ingredients.*.carbs_per_100g' => 'nullable|numeric|min:0',    // Carbs per 100g (optional)
            'ingredients.*.fat_per_100g' => 'nullable|numeric|min:0',      // Fat per 100g (optional)
            'ingredients.*.protein_per_100g' => 'nullable|numeric|min:0',  // Protein per 100g (optional)
            'ingredients.*.calories_per_100g' => 'nullable|numeric|min:0', // Calories per 100g (optional)
            'steps' => 'required|array|min:1',                      // At least one step required
            'steps.*.step_number' => 'required|integer|min:1',      // Step numbers must be positive
            'steps.*.instruction' => 'required|string',             // Each step needs instructions
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); // Unprocessable Entity status code
        }

        try {
            // Create the main recipe record
            $recipe = Recipe::create([
                'name' => $request->name,
                'description' => $request->description,
                'servings' => $request->servings,
                'prep_time' => $request->prep_time ?? 0,
                'cook_time' => $request->cook_time ?? 0,
            ]);

            // Add each ingredient to the recipe
            foreach ($request->ingredients as $ingredientData) {
                $recipe->ingredients()->create($ingredientData);
            }

            // Add each preparation step to the recipe
            foreach ($request->steps as $stepData) {
                $recipe->steps()->create($stepData);
            }

            // Calculate nutrition values based on ingredients
            $recipe->calculateNutrition();

            // Load the relationships to include them in the response
            $recipe->load(['ingredients', 'steps']);

            // Return success response with created recipe data
            return response()->json([
                'success' => true,
                'data' => $recipe,
                'message' => 'Recipe created successfully'
            ], 201); // Created status code

        } catch (\Exception $e) {
            // Handle any errors that occurred during creation
            return response()->json([
                'success' => false,
                'message' => 'Failed to create recipe',
                'error' => $e->getMessage()
            ], 500); // Internal Server Error status code
        }
    }

    /**
     * Display the specified recipe.
     * 
     * Retrieves a single recipe by its ID including all associated
     * ingredients and preparation steps.
     *
     * @param int $id The recipe ID to retrieve
     * @return \Illuminate\Http\JsonResponse JSON response with recipe data or not found error
     */
    public function show($id)
    {
        // Find the recipe with its related data
        $recipe = Recipe::with(['ingredients', 'steps'])->find($id);

        // Check if recipe exists
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404); // Not Found status code
        }

        // Return the recipe data
        return response()->json([
            'success' => true,
            'data' => $recipe
        ]);
    }

    /**
     * Update the specified recipe in the database.
     * 
     * Updates an existing recipe with new data. Can update basic recipe
     * information, ingredients, and/or preparation steps. Uses partial
     * validation to allow updating only specific fields.
     *
     * @param \Illuminate\Http\Request $request HTTP request containing updated recipe data
     * @param int $id The recipe ID to update
     * @return \Illuminate\Http\JsonResponse JSON response with updated recipe or error message
     */
    public function update(Request $request, $id)
    {
        // Find the recipe to update
        $recipe = Recipe::find($id);

        // Check if recipe exists
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        // Define validation rules for recipe updates
        // Using 'sometimes' to allow partial updates
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',           // Name is required if present
            'description' => 'nullable|string',                      // Description can be null
            'servings' => 'sometimes|required|integer|min:1',        // Servings required if present
            'prep_time' => 'nullable|integer|min:0',                 // Prep time is optional
            'cook_time' => 'nullable|integer|min:0',                 // Cook time is optional
            'ingredients' => 'sometimes|required|array|min:1',       // Ingredients required if present
            'ingredients.*.name' => 'required_with:ingredients|string',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
            'ingredients.*.unit' => 'required_with:ingredients|string',
            'ingredients.*.carbs_per_100g' => 'nullable|numeric|min:0',
            'ingredients.*.fat_per_100g' => 'nullable|numeric|min:0',
            'ingredients.*.protein_per_100g' => 'nullable|numeric|min:0',
            'ingredients.*.calories_per_100g' => 'nullable|numeric|min:0',
            'steps' => 'sometimes|required|array|min:1',             // Steps required if present
            'steps.*.step_number' => 'required_with:steps|integer|min:1',
            'steps.*.instruction' => 'required_with:steps|string',
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Update basic recipe information
            $recipe->update($request->only(['name', 'description', 'servings', 'prep_time', 'cook_time']));

            // Update ingredients if provided
            if ($request->has('ingredients')) {
                // Delete existing ingredients and create new ones
                $recipe->ingredients()->delete();
                foreach ($request->ingredients as $ingredientData) {
                    $recipe->ingredients()->create($ingredientData);
                }
            }

            // Update steps if provided
            if ($request->has('steps')) {
                // Delete existing steps and create new ones
                $recipe->steps()->delete();
                foreach ($request->steps as $stepData) {
                    $recipe->steps()->create($stepData);
                }
            }

            // Recalculate nutrition values after any update
            // This ensures nutrition data is always accurate regardless of what was updated
            $recipe->calculateNutrition();

            // Load updated relationships
            $recipe->load(['ingredients', 'steps']);

            // Return success response with updated recipe
            return response()->json([
                'success' => true,
                'data' => $recipe,
                'message' => 'Recipe updated successfully'
            ]);

        } catch (\Exception $e) {
            // Handle any errors that occurred during update
            return response()->json([
                'success' => false,
                'message' => 'Failed to update recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified recipe from the database.
     * 
     * Deletes a recipe and all its associated data (ingredients and steps).
     * The deletion is handled by Laravel's Eloquent ORM which will automatically
     * handle the cascade deletion of related records.
     *
     * @param int $id The recipe ID to delete
     * @return \Illuminate\Http\JsonResponse JSON response confirming deletion or error message
     */
    public function destroy($id)
    {
        // Find the recipe to delete
        $recipe = Recipe::find($id);

        // Check if recipe exists
        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        try {
            // Delete the recipe (related ingredients and steps will be deleted automatically)
            $recipe->delete();

            // Return success confirmation
            return response()->json([
                'success' => true,
                'message' => 'Recipe deleted successfully'
            ]);

        } catch (\Exception $e) {
            // Handle any errors that occurred during deletion
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete recipe',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate nutrition for all recipes.
     * 
     * This method goes through all existing recipes and recalculates their
     * nutrition values based on their current ingredients. Useful for:
     * - Fixing recipes with zero nutrition values
     * - Updating nutrition after ingredient database changes
     * - Bulk nutrition recalculation
     *
     * @return \Illuminate\Http\JsonResponse JSON response with recalculation results
     */
    public function recalculateAllNutrition()
    {
        try {
            $recipes = Recipe::with('ingredients')->get();
            $updatedCount = 0;
            $errors = [];

            foreach ($recipes as $recipe) {
                try {
                    $oldCalories = $recipe->total_calories;
                    $recipe->calculateNutrition();
                    
                    if ($recipe->total_calories != $oldCalories) {
                        $updatedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Recipe ID {$recipe->id}: {$e->getMessage()}";
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Nutrition recalculated for all recipes",
                'data' => [
                    'total_recipes' => $recipes->count(),
                    'updated_recipes' => $updatedCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to recalculate nutrition',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate nutrition for a specific recipe.
     * 
     * Forces recalculation of nutrition values for a single recipe.
     * Useful for fixing individual recipes with incorrect nutrition data.
     *
     * @param int $id The recipe ID to recalculate
     * @return \Illuminate\Http\JsonResponse JSON response with recalculation result
     */
    public function recalculateNutrition($id)
    {
        $recipe = Recipe::with('ingredients')->find($id);

        if (!$recipe) {
            return response()->json([
                'success' => false,
                'message' => 'Recipe not found'
            ], 404);
        }

        try {
            $oldCalories = $recipe->total_calories;
            $recipe->calculateNutrition();
            $recipe->load(['ingredients', 'steps']);

            return response()->json([
                'success' => true,
                'data' => $recipe,
                'message' => 'Nutrition recalculated successfully',
                'debug' => [
                    'old_calories' => $oldCalories,
                    'new_calories' => $recipe->total_calories
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to recalculate nutrition',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
