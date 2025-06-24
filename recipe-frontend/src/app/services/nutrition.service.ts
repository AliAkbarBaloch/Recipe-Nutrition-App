import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Observable } from 'rxjs';

/**
 * Basic ingredient structure - just the nutrition info I care about
 */
export interface Ingredient {
  name: string;
  carbs: number;
  fat: number;
  protein: number;
}

/**
 * What the API sends back when I ask for nutrition data
 */
export interface NutritionResponse {
  success: boolean;
  data?: Ingredient;
  message?: string;
}

/**
 * Handles all the nutrition API calls to my Laravel backend
 * Mostly just searching for ingredients and getting their nutrition info
 */
@Injectable({
  providedIn: 'root'
})
export class NutritionService {
  // Pointing to my local Laravel API
  private baseUrl = 'http://localhost:8000/api/nutrition';

  constructor(private http: HttpClient) { }

  /**
   * Gets all ingredients I have stored
   */
  getAllIngredients(): Observable<any> {
    return this.http.get(`${this.baseUrl}/ingredients`);
  }

  /**
   * Search for an ingredient by name to get its nutrition info
   */
  searchIngredient(ingredientName: string): Observable<NutritionResponse> {
    return this.http.get<NutritionResponse>(`${this.baseUrl}/ingredients/search`, {
      params: { ingredient: ingredientName }
    });
  }

  /**
   * Add a new ingredient to the database if I find one that's not there
   */
  addIngredient(ingredient: Ingredient): Observable<NutritionResponse> {
    return this.http.post<NutritionResponse>(`${this.baseUrl}/ingredients`, ingredient);
  }
}
