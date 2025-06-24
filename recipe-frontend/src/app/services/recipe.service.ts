import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

/** What an ingredient looks like in my recipes */
export interface RecipeIngredient {
  name: string;
  quantity: number;
  unit: string;
}

/** Individual cooking steps */
export interface RecipeStep {
  instruction: string;
}

/** Full recipe with all the details */
export interface Recipe {
  id?: number;
  name: string;
  description?: string;
  servings?: number;
  prep_time?: number;
  cook_time?: number;
  ingredients: RecipeIngredient[];
  steps: RecipeStep[];
  total_calories?: number | string;
  total_carbs?: number | string;
  total_fat?: number | string;
  total_protein?: number | string;
}

/** What my API sends back */
export interface RecipeResponse {
  success: boolean;
  data?: Recipe | Recipe[] | any;
  message?: string;
}

/**
 * Handles all the recipe CRUD operations with my Laravel API
 * Pretty straightforward - just basic create, read, update, delete stuff
 */
@Injectable({
  providedIn: 'root'
})
export class RecipeService {
  // My Laravel API endpoint
  private baseUrl = 'http://localhost:8000/api/recipes';

  constructor(private http: HttpClient) { }

  /**
   * Get all my recipes
   */
  getAllRecipes(): Observable<RecipeResponse> {
    // Adding these headers because I had caching issues during development
    const headers = {
      'Cache-Control': 'no-cache, no-store, must-revalidate',
      'Pragma': 'no-cache',
      'Expires': '0'
    };
    return this.http.get<RecipeResponse>(this.baseUrl, { headers });
  }

  /**
   * Get one specific recipe
   */
  getRecipe(id: number): Observable<RecipeResponse> {
    return this.http.get<RecipeResponse>(`${this.baseUrl}/${id}`);
  }

  /**
   * Create a new recipe
   */
  createRecipe(recipe: Recipe): Observable<RecipeResponse> {
    return this.http.post<RecipeResponse>(this.baseUrl, recipe);
  }

  /**
   * Update an existing recipe
   */
  updateRecipe(id: number, recipe: Recipe): Observable<RecipeResponse> {
    return this.http.put<RecipeResponse>(`${this.baseUrl}/${id}`, recipe);
  }

  /**
   * Delete a recipe
   */
  deleteRecipe(id: number): Observable<RecipeResponse> {
    return this.http.delete<RecipeResponse>(`${this.baseUrl}/${id}`);
  }

  /**
   * Refresh nutrition data for all recipes
   * Useful when I add new ingredients to the database
   */
  recalculateAllNutrition(): Observable<RecipeResponse> {
    return this.http.post<RecipeResponse>(`${this.baseUrl}/recalculate-nutrition`, {});
  }

  /**
   * Refresh nutrition data for just one recipe
   */
  recalculateRecipeNutrition(id: number): Observable<RecipeResponse> {
    return this.http.post<RecipeResponse>(`${this.baseUrl}/${id}/recalculate-nutrition`, {});
  }
}
