<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NutritionApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * NutritionController
 * 
 * API controller for managing nutrition-related operations in the Recipe Nutrition Management system.
 * Acts as a bridge between the frontend and the external nutrition API service.
 * 
 * This controller handles:
 * - Retrieving all available ingredients from the nutrition API
 * - Searching for specific ingredients by name
 * - Adding new ingredients to the nutrition database
 * - Submitting required ingredients for the application
 * 
 * All operations are delegated to the NutritionApiService which handles the actual API communication.
 */
class NutritionController extends Controller
{
    /**
     * The nutrition API service instance.
     * Injected via dependency injection to handle all external API operations.
     *
     * @var \App\Services\NutritionApiService
     */
    protected $nutritionService;

    /**
     * Create a new controller instance.
     * 
     * The NutritionApiService is automatically injected by Laravel's service container
     * when this controller is instantiated.
     *
     * @param \App\Services\NutritionApiService $nutritionService Service for nutrition API operations
     */
    public function __construct(NutritionApiService $nutritionService)
    {
        $this->nutritionService = $nutritionService;
    }

    /**
     * Get all ingredients from the nutrition API.
     * 
     * Retrieves the complete list of ingredients available in the external
     * nutrition database, including their nutritional information per 100g.
     * This data can be used to populate ingredient selection dropdowns
     * or for searching purposes.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with all ingredients or empty array on failure
     */
    public function getAllIngredients()
    {
        // Delegate to the nutrition service to fetch all ingredients
        $ingredients = $this->nutritionService->getAllIngredients();
        
        // Check if there was an error in the response
        if (isset($ingredients['error'])) {
            return response()->json([
                'success' => false,
                'message' => $ingredients['error'],
                'data' => []
            ], 503); // Service Unavailable
        }
        
        // Return standardized JSON response
        return response()->json([
            'success' => true,
            'data' => $ingredients,
            'message' => is_array($ingredients) && count($ingredients) > 0 && 
                        isset($ingredients[0]['message']) && 
                        strpos($ingredients[0]['message'], 'mock') !== false 
                        ? 'Using mock data (external API unavailable)' 
                        : 'Data retrieved successfully'
        ]);
    }

    /**
     * Search for a specific ingredient by name.
     * 
     * Searches the nutrition API for an ingredient matching the provided name.
     * This is useful for finding nutritional information for specific ingredients
     * when creating or updating recipes.
     *
     * @param \Illuminate\Http\Request $request HTTP request containing the ingredient name to search
     * @return \Illuminate\Http\JsonResponse JSON response with ingredient data or not found error
     */
    public function searchIngredient(Request $request)
    {
        // Validate the search request
        $validator = Validator::make($request->all(), [
            'ingredient' => 'required|string|min:1'  // Ingredient name is required and must not be empty
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422); // Unprocessable Entity status code
        }

        // Search for the ingredient using the nutrition service
        $ingredient = $this->nutritionService->searchIngredient($request->ingredient);
        
        // Check if there was an error in the response
        if (is_array($ingredient) && isset($ingredient['error'])) {
            return response()->json([
                'success' => false,
                'message' => $ingredient['error']
            ], 503); // Service Unavailable
        }
        
        // Check if ingredient was found
        if ($ingredient === null) {
            return response()->json([
                'success' => false,
                'message' => 'Ingredient not found'
            ], 404); // Not Found status code
        }

        // Return the found ingredient data
        return response()->json([
            'success' => true,
            'data' => $ingredient
        ]);
    }

    /**
     * Add a new ingredient to the nutrition API.
     * 
     * Creates a new ingredient in the external nutrition database with the
     * provided nutritional information. All nutritional values should be
     * specified per 100g of the ingredient.
     *
     * @param \Illuminate\Http\Request $request HTTP request containing ingredient data
     * @return \Illuminate\Http\JsonResponse JSON response with created ingredient or validation errors
     */
    public function addIngredient(Request $request)
    {
        // Validate the ingredient data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',     // Ingredient name is required
            'carbs' => 'required|numeric|min:0',     // Carbohydrates per 100g (must be non-negative)
            'fat' => 'required|numeric|min:0',       // Fat per 100g (must be non-negative)
            'protein' => 'required|numeric|min:0'    // Protein per 100g (must be non-negative)
        ]);

        // Check if validation failed
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Attempt to add the ingredient via the nutrition service
        $result = $this->nutritionService->addIngredient(
            $request->name,     // Ingredient name
            $request->carbs,    // Carbohydrates per 100g
            $request->fat,      // Fat per 100g
            $request->protein   // Protein per 100g
        );

        // Check if there was an error in the response
        if (is_array($result) && isset($result['error'])) {
            return response()->json([
                'success' => false,
                'message' => $result['error']
            ], 503); // Service Unavailable
        }

        // Check if the addition failed
        if ($result === false) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add ingredient'
            ], 500); // Internal Server Error status code
        }

        // Return success response with the created ingredient data
        return response()->json([
            'success' => true,
            'data' => $result,
            'message' => 'Ingredient added successfully'
        ], 201); // Created status code
    }

    /**
     * Submit the required 2 ingredients as per application requirements.
     * 
     * This endpoint submits two predefined ingredients (Organic Quinoa and Greek Yogurt)
     * to the nutrition API as required for the Recipe Nutrition Management application.
     * This is typically used during application setup or initialization.
     *
     * @return \Illuminate\Http\JsonResponse JSON response with submission results
     */
    public function submitRequiredIngredients()
    {
        // Delegate to the nutrition service to submit the required ingredients
        $results = $this->nutritionService->submitRequiredIngredients();
        
        // Return success response with the submission results
        return response()->json([
            'success' => true,
            'data' => $results,
            'message' => 'Required ingredients submitted successfully'
        ]);
    }
}
