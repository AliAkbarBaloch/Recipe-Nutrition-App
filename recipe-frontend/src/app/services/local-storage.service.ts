import { Injectable } from '@angular/core';
import { Recipe } from './recipe.service';

/**
 * Handles all my localStorage stuff - saving recipes, form drafts, etc.
 * Pretty handy for keeping things around when the user refreshes the page
 */
@Injectable({
  providedIn: 'root'
})
export class LocalStorageService {

  constructor() { }

  /**
   * Basic helper to save anything to localStorage
   */
  setItem<T>(key: string, value: T): boolean {
    try {
      const serializedValue = JSON.stringify(value);
      localStorage.setItem(key, serializedValue);
      return true;
    } catch (error) {
      console.error('Error saving to localStorage:', error);
      return false;
    }
  }

  /**
   * Basic helper to get anything from localStorage
   */
  getItem<T>(key: string): T | null {
    try {
      const item = localStorage.getItem(key);
      if (item === null) {
        return null;
      }
      return JSON.parse(item) as T;
    } catch (error) {
      console.error('Error retrieving from localStorage:', error);
      return null;
    }
  }

  /**
   * Remove stuff from localStorage
   */
  removeItem(key: string): boolean {
    try {
      localStorage.removeItem(key);
      return true;
    } catch (error) {
      console.error('Error removing from localStorage:', error);
      return false;
    }
  }

  /**
   * Check if something exists in localStorage
   */
  hasItem(key: string): boolean {
    return localStorage.getItem(key) !== null;
  }

  /**
   * Save recipe draft so users don't lose their work
   * Really helpful when they accidentally navigate away from the form
   */
  saveRecipeDraft(recipe: Partial<Recipe>): void {
    localStorage.setItem('recipeDraft', JSON.stringify(recipe));
  }

  /**
   * Get the saved recipe draft back
   */
  getRecipeDraft(): Partial<Recipe> | null {
    const draft = localStorage.getItem('recipeDraft');
    return draft ? JSON.parse(draft) : null;
  }

  /**
   * Clear the draft when they submit or don't want it anymore
   */
  clearRecipeDraft(): void {
    localStorage.removeItem('recipeDraft');
  }

  /**
   * Save all my recipes to localStorage
   */
  saveRecipes(recipes: Recipe[]): void {
    localStorage.setItem('recipes', JSON.stringify(recipes));
  }

  /**
   * Get all recipes I have saved
   */
  getRecipes(): Recipe[] {
    const recipes = localStorage.getItem('recipes');
    return recipes ? JSON.parse(recipes) : [];
  }

  /**
   * Add a new recipe to my collection
   * Just using a simple incrementing ID for now
   */
  addRecipe(recipe: Recipe): void {
    const recipes = this.getRecipes();
    recipe.id = recipes.length + 1; // Simple ID generation
    recipes.push(recipe);
    this.saveRecipes(recipes);
  }

  /**
   * Update an existing recipe
   */
  updateRecipe(recipe: Recipe): void {
    const recipes = this.getRecipes();
    const index = recipes.findIndex(r => r.id === recipe.id);
    if (index !== -1) {
      recipes[index] = recipe;
      this.saveRecipes(recipes);
    }
  }

  /**
   * Delete a recipe I don't want anymore
   */
  deleteRecipe(id: number): void {
    const recipes = this.getRecipes();
    const filtered = recipes.filter(r => r.id !== id);
    this.saveRecipes(filtered);
  }
}
