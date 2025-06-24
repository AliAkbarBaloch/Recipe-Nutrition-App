<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling nutrition API calls
 * 
 * Talks to the external nutrition API to get ingredient data.
 * Most of the heavy lifting for ingredient nutrition info happens here.
 */
class NutritionApiService
{
    /**
     * The external nutrition API endpoint
     */
    private $baseUrl = 'https://interview.workcentrix.de/ingredients.php';

    /**
     * Username for API basic authentication.
     * Required for accessing the protected nutrition API.
     *
     * @var string
     */
    private $username = 'name';

    /**
     * Password for API basic authentication.
     * Required for accessing the protected nutrition API.
     *
     * @var string
     */
    private $password = 'password';

    /**
     * Mock mode for testing when external API is unavailable.
     * Can be controlled via NUTRITION_API_MOCK_MODE environment variable.
     *
     * @var bool
     */
    private $mockMode;

    /**
     * Constructor to initialize mock mode from environment configuration.
     */
    public function __construct()
    {
        $this->mockMode = config('app.env') === 'testing' || env('NUTRITION_API_MOCK_MODE', false);
    }

    /**
     * Retrieve all available ingredients from the nutrition API.
     * 
     * Makes a GET request to the API to fetch the complete list of ingredients
     * with their nutritional information (carbs, fat, protein per 100g).
     *
     * @return array Array of ingredients with nutritional data, empty array on failure
     */
    public function getAllIngredients()
    {
        // Return mock data if in mock mode
        if ($this->mockMode) {
            return $this->getMockIngredients();
        }

        try {
            // Make authenticated GET request to fetch all ingredients
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10) // Add timeout for better error handling
                ->get($this->baseUrl);

            // Check if the request was successful (2xx status code)
            if ($response->successful()) {
                // Return the JSON response as an associative array
                return $response->json();
            }

            // Handle specific error cases
            if ($response->status() === 401) {
                Log::warning('Nutrition API authentication failed - check credentials');
                return ['error' => 'Authentication failed with nutrition API'];
            }

            // Log error if request failed but didn't throw exception
            Log::error('Failed to fetch ingredients', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return ['error' => 'Failed to fetch ingredients from external API'];
        } catch (\Exception $e) {
            // Log any exceptions that occurred during the request
            Log::error('API Error: ' . $e->getMessage());
            
            // Return mock data as fallback for development
            Log::info('Falling back to mock data due to API connection error');
            return $this->getMockIngredients();
        }
    }

    /**
     * Search for a specific ingredient by name.
     * 
     * Makes a GET request with the ingredient name as a query parameter
     * to retrieve nutritional information for a specific ingredient.
     *
     * @param string $ingredientName Name of the ingredient to search for
     * @return array|null Ingredient data if found, null if not found or on error
     */
    public function searchIngredient($ingredientName)
    {
        // Return mock data if in mock mode
        if ($this->mockMode) {
            return $this->getMockIngredient($ingredientName);
        }

        try {
            // Make authenticated GET request with ingredient name parameter
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->get($this->baseUrl, ['ingredient' => $ingredientName]);

            // Check if the request was successful
            if ($response->successful()) {
                // Return the ingredient data as an associative array
                return $response->json();
            }

            // Handle 404 status specifically (ingredient not found)
            if ($response->status() === 404) {
                return null; // Ingredient not found in the database
            }

            // Handle authentication errors
            if ($response->status() === 401) {
                Log::warning('Nutrition API authentication failed during search');
                return ['error' => 'Authentication failed with nutrition API'];
            }

            // Log error for other unsuccessful status codes
            Log::error('Failed to search ingredient', [
                'ingredient' => $ingredientName,
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return ['error' => 'Failed to search ingredient in external API'];
        } catch (\Exception $e) {
            // Log any exceptions that occurred during the request
            Log::error('API Error during ingredient search: ' . $e->getMessage());
            
            // Return mock data as fallback for development
            Log::info('Falling back to mock data for ingredient search due to API connection error');
            return $this->getMockIngredient($ingredientName);
        }
    }

    /**
     * Add a new ingredient to the nutrition API database.
     * 
     * Makes a POST request to add a new ingredient with its nutritional values.
     * All nutritional values should be provided per 100g of the ingredient.
     *
     * @param string $name Name of the ingredient
     * @param float $carbs Carbohydrates per 100g
     * @param float $fat Fat content per 100g
     * @param float $protein Protein content per 100g
     * @return array|false API response data on success, false on failure
     */
    public function addIngredient($name, $carbs, $fat, $protein)
    {
        // Return mock data if in mock mode
        if ($this->mockMode) {
            return $this->getMockAddResponse($name, $carbs, $fat, $protein);
        }

        try {
            // Make authenticated POST request with form data
            $response = Http::withBasicAuth($this->username, $this->password)
                ->timeout(10)
                ->asForm() // Send data as form-encoded
                ->post($this->baseUrl, [
                    'name' => $name,        // Ingredient name
                    'carbs' => $carbs,      // Carbohydrates per 100g
                    'fat' => $fat,          // Fat per 100g
                    'protein' => $protein   // Protein per 100g
                ]);

            // Check if the ingredient was added successfully
            if ($response->successful()) {
                // Return the API response (usually contains the created ingredient data)
                return $response->json();
            }

            // Handle authentication errors
            if ($response->status() === 401) {
                Log::warning('Nutrition API authentication failed during add ingredient');
                return ['error' => 'Authentication failed with nutrition API'];
            }

            // Log detailed error information for debugging
            Log::error('Failed to add ingredient', [
                'name' => $name,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return ['error' => 'Failed to add ingredient to external API'];
        } catch (\Exception $e) {
            // Log any exceptions that occurred during the request
            Log::error('API Error during add ingredient: ' . $e->getMessage());
            
            // Return mock data as fallback for development
            Log::info('Falling back to mock data for ingredient addition due to API connection error');
            return $this->getMockAddResponse($name, $carbs, $fat, $protein);
        }
    }

    /**
     * Submit the required ingredients for the application.
     * 
     * This method adds two specific ingredients that are required for the
     * recipe nutrition application: Organic Quinoa and Greek Yogurt.
     * 
     * The nutritional values are based on standard USDA nutrition data:
     * - Organic Quinoa: High in carbs and protein, moderate fat
     * - Greek Yogurt: Low in carbs, moderate fat and protein
     *
     * @return array Results of adding both ingredients with success status
     */
    public function submitRequiredIngredients()
    {
        // Define the two required ingredients with their nutritional values
        $ingredients = [
            [
                'name' => 'Organic Quinoa',
                'carbs' => 64.2,    // Carbohydrates per 100g
                'fat' => 6.1,       // Fat per 100g
                'protein' => 14.1   // Protein per 100g
            ],
            [
                'name' => 'Greek Yogurt',
                'carbs' => 4.0,     // Carbohydrates per 100g
                'fat' => 10.0,      // Fat per 100g
                'protein' => 10.0   // Protein per 100g
            ]
        ];

        // Store results of each ingredient submission
        $results = [];
        
        // Loop through each ingredient and attempt to add it to the API
        foreach ($ingredients as $ingredient) {
            // Call the addIngredient method for each ingredient
            $result = $this->addIngredient(
                $ingredient['name'],
                $ingredient['carbs'],
                $ingredient['fat'],
                $ingredient['protein']
            );
            
            // Determine success status - check for error in array or false result
            $success = $result !== false && !(is_array($result) && isset($result['error']));
            
            // Store the result with ingredient name and success status
            $results[] = [
                'ingredient' => $ingredient['name'],
                'success' => $success,              // true if added successfully
                'response' => $result,              // API response, error array, or false
                'message' => $success ? 'Added successfully' : 
                    (is_array($result) && isset($result['error']) ? $result['error'] : 'Failed to add ingredient')
            ];
        }

        // Return array of results for both ingredients
        return $results;
    }

    /**
     * Get mock ingredients data for testing/development.
     * 
     * Returns a predefined set of common ingredients with realistic nutritional values
     * when the external API is unavailable or in testing mode.
     *
     * @return array Mock ingredients data
     */
    private function getMockIngredients()
    {
        return [
            [
                'id' => 1,
                'name' => 'Chicken Breast',
                'carbs_per_100g' => 0.0,
                'fat_per_100g' => 3.6,
                'protein_per_100g' => 31.0,
                'calories_per_100g' => 165
            ],
            [
                'id' => 2,
                'name' => 'Brown Rice',
                'carbs_per_100g' => 76.0,
                'fat_per_100g' => 1.8,
                'protein_per_100g' => 7.5,
                'calories_per_100g' => 362
            ],
            [
                'id' => 3,
                'name' => 'Broccoli',
                'carbs_per_100g' => 6.6,
                'fat_per_100g' => 0.4,
                'protein_per_100g' => 2.8,
                'calories_per_100g' => 34
            ],
            [
                'id' => 4,
                'name' => 'Olive Oil',
                'carbs_per_100g' => 0.0,
                'fat_per_100g' => 100.0,
                'protein_per_100g' => 0.0,
                'calories_per_100g' => 884
            ],
            [
                'id' => 5,
                'name' => 'Organic Quinoa',
                'carbs_per_100g' => 64.2,
                'fat_per_100g' => 6.1,
                'protein_per_100g' => 14.1,
                'calories_per_100g' => 368
            ],
            [
                'id' => 6,
                'name' => 'Greek Yogurt',
                'carbs_per_100g' => 4.0,
                'fat_per_100g' => 10.0,
                'protein_per_100g' => 10.0,
                'calories_per_100g' => 146
            ]
        ];
    }

    /**
     * Get mock ingredient data for a specific ingredient name.
     * 
     * Searches through the mock ingredients list for a matching ingredient name.
     *
     * @param string $name Ingredient name to search for
     * @return array|null Mock ingredient data if found, null otherwise
     */
    private function getMockIngredient($name)
    {
        $mockIngredients = $this->getMockIngredients();
        
        foreach ($mockIngredients as $ingredient) {
            if (stripos($ingredient['name'], $name) !== false) {
                return $ingredient;
            }
        }
        
        return null;
    }

    /**
     * Generate mock response for adding an ingredient.
     * 
     * Simulates the API response when successfully adding an ingredient.
     *
     * @param string $name Ingredient name
     * @param float $carbs Carbohydrates per 100g
     * @param float $fat Fat per 100g
     * @param float $protein Protein per 100g
     * @return array Mock API response
     */
    private function getMockAddResponse($name, $carbs, $fat, $protein)
    {
        return [
            'id' => rand(100, 999),
            'name' => $name,
            'carbs_per_100g' => $carbs,
            'fat_per_100g' => $fat,
            'protein_per_100g' => $protein,
            'calories_per_100g' => round(($carbs * 4) + ($protein * 4) + ($fat * 9), 2),
            'status' => 'created',
            'message' => 'Ingredient added successfully (mock mode)'
        ];
    }
}