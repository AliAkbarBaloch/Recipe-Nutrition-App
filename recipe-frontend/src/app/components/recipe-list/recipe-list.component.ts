import { Component, OnInit, OnDestroy } from '@angular/core';
import { Router } from '@angular/router';
import { Location } from '@angular/common';
import { Recipe, RecipeService } from '../../services/recipe.service';

/**
 * Recipe list component - shows all my recipes in a nice grid
 * 
 * Displays everything about each recipe:
 * - Name, description, servings
 * - Cook and prep times
 * - Ingredients (expandable list)
 * - Cooking steps (expandable)
 * - Nutrition facts
 * - Edit and delete buttons
 */
@Component({
  selector: 'app-recipe-list',
  templateUrl: './recipe-list.component.html',
  styleUrls: ['./recipe-list.component.css']
})
export class RecipeListComponent implements OnInit, OnDestroy {
  // All my recipes to show
  recipes: Recipe[] = [];
  
  // Show loading spinner when fetching recipes
  isLoading = false;
  
  // Error message if something goes wrong
  error: string | null = null;
  
  // Keep track of which recipe steps are expanded
  expandedSteps: Set<number> = new Set();
  
  // Handle browser back button
  private popstateListener: () => void;

  constructor(
    private recipeService: RecipeService,
    private router: Router,
    private location: Location
  ) { 
    // Handle browser back button so it goes to home page
    this.popstateListener = () => {
      // If we're on recipes page and user hits back, go home
      if (this.location.path().includes('/recipes')) {
        this.router.navigate(['/']);
      }
    };
    window.addEventListener('popstate', this.popstateListener);
  }

  /**
   * Component cleanup - remove event listeners to prevent memory leaks
   */
  ngOnDestroy(): void {
    // Clean up the event listener
    if (this.popstateListener) {
      window.removeEventListener('popstate', this.popstateListener);
    }
  }

  /**
   * Component initialization - load all recipes
   */
  ngOnInit(): void {
    this.loadRecipes();
  }

  /**
   * Fetch all recipes from the API and handle loading states
   */
  loadRecipes(): void {
    this.isLoading = true;
    this.error = null;
    
    this.recipeService.getAllRecipes().subscribe({
      next: (response) => {
        console.log('Recipe list API response:', response);
        if (response.success && Array.isArray(response.data)) {
          this.recipes = response.data;
          console.log('Loaded recipes:', this.recipes);
          this.recipes.forEach((recipe, index) => {
            console.log(`Recipe ${index + 1}: ${recipe.name} - Calories: ${recipe.total_calories}, Carbs: ${recipe.total_carbs}, Fat: ${recipe.total_fat}, Protein: ${recipe.total_protein}`);
          });
        } else {
          this.recipes = [];
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading recipes:', error);
        this.error = 'Failed to load recipes. Please try again.';
        this.recipes = [];
        this.isLoading = false;
      }
    });
  }

  /**
   * Navigate to the new recipe form
   */
  addNewRecipe(): void {
    this.router.navigate(['/recipe/new']);
  }

  /**
   * Navigate to edit form for the specified recipe
   * @param recipe The recipe to edit
   */
  editRecipe(recipe: Recipe): void {
    this.router.navigate(['/recipe/edit', recipe.id]);
  }

  /**
   * Delete a recipe after user confirmation
   * @param recipe The recipe to delete
   */
  deleteRecipe(recipe: Recipe): void {
    if (confirm(`Are you sure you want to delete "${recipe.name}"?`)) {
      this.recipeService.deleteRecipe(recipe.id!).subscribe({
        next: (response) => {
          if (response.success) {
            this.loadRecipes(); // Reload the list
          } else {
            this.error = response.message || 'Failed to delete recipe';
          }
        },
        error: (error) => {
          console.error('Error deleting recipe:', error);
          this.error = 'Failed to delete recipe. Please try again.';
        }
      });
    }
  }

  /**
   * Calculate total cooking time (prep + cook time) for a recipe
   * @param recipe The recipe to calculate time for
   * @returns Total time in minutes
   */
  getTotalTime(recipe: Recipe): number {
    return (recipe.prep_time || 0) + (recipe.cook_time || 0);
  }

  /**
   * Calculate and format the average cooking time across all recipes
   * @returns Formatted average time string (e.g., "45min")
   */
  getAverageTime(): string {
    if (this.recipes.length === 0) return '0min';
    
    const totalTime = this.recipes.reduce((sum, recipe) => {
      return sum + this.getTotalTime(recipe);
    }, 0);
    
    const average = Math.round(totalTime / this.recipes.length);
    return `${average}min`;
  }

  /**
   * Calculate and format the average calories across all recipes with nutrition data
   * @returns Formatted average calories string (e.g., "518cal")
   */
  getAverageCalories(): string {
    if (this.recipes.length === 0) return '0cal';
    
    const recipesWithCalories = this.recipes.filter(recipe => {
      const calories = recipe.total_calories ? parseFloat(recipe.total_calories.toString()) : 0;
      return calories > 0;
    });
    
    if (recipesWithCalories.length === 0) return '0cal';
    
    const totalCalories = recipesWithCalories.reduce((sum, recipe) => {
      const calories = recipe.total_calories ? parseFloat(recipe.total_calories.toString()) : 0;
      return sum + calories;
    }, 0);
    
    const average = Math.round(totalCalories / recipesWithCalories.length);
    return `${average}cal`;
  }

  /**
   * Share recipe via WhatsApp
   * @param recipe The recipe to share
   */
  shareToWhatsApp(recipe: Recipe): void {
    const recipeText = this.generateRecipeShareText(recipe);
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(recipeText)}`;
    window.open(whatsappUrl, '_blank');
  }

  /**
   * Share recipe via Facebook
   * @param recipe The recipe to share
   */
  shareToFacebook(recipe: Recipe): void {
    const recipeText = this.generateRecipeShareText(recipe);
    const facebookUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.origin)}&quote=${encodeURIComponent(recipeText)}`;
    window.open(facebookUrl, '_blank');
  }

  /**
   * Copy recipe details to clipboard as text
   * @param recipe The recipe to copy
   */
  copyRecipeLink(recipe: Recipe): void {
    const recipeText = this.generateRecipeShareText(recipe);
    const shareUrl = `${window.location.origin}/recipes`;
    const fullText = `${recipeText}\n\nView more recipes at: ${shareUrl}`;
    
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(fullText).then(() => {
        this.showCopySuccess();
      }).catch(() => {
        this.fallbackCopyText(fullText);
      });
    } else {
      this.fallbackCopyText(fullText);
    }
  }

  /**
   * Generate formatted text for sharing a recipe
   * @param recipe The recipe to format
   * @returns Formatted recipe text with emojis and details
   */
  private generateRecipeShareText(recipe: Recipe): string {
    let text = `🍽️ Check out this amazing recipe: ${recipe.name}`;
    
    if (recipe.description) {
      text += `\n\n${recipe.description}`;
    }
    
    // Add basic recipe info
    const details = [];
    if (recipe.servings) details.push(`👥 Serves ${recipe.servings}`);
    if (this.getTotalTime(recipe) > 0) details.push(`⏱️ ${this.getTotalTime(recipe)} minutes`);
    if (recipe.total_calories) details.push(`🔥 ${recipe.total_calories} calories`);
    
    if (details.length > 0) {
      text += `\n\n${details.join(' • ')}`;
    }
    
    // Add a few ingredients if available
    if (recipe.ingredients && recipe.ingredients.length > 0) {
      text += '\n\n🛒 Ingredients:';
      const topIngredients = recipe.ingredients.slice(0, 3);
      topIngredients.forEach(ingredient => {
        text += `\n• ${ingredient.quantity}${ingredient.unit} ${ingredient.name}`;
      });
      if (recipe.ingredients.length > 3) {
        text += `\n• ... and ${recipe.ingredients.length - 3} more!`;
      }
    }
    
    return text;
  }

  /**
   * Show success message when text is copied
   */
  private showCopySuccess(): void {
    // You could use a snackbar or toast notification here
    alert('Recipe details copied to clipboard!');
  }

  /**
   * Fallback method to copy text for older browsers
   * @param text The text to copy
   */
  private fallbackCopyText(text: string): void {
    // Fallback method for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
      document.execCommand('copy');
      this.showCopySuccess();
    } catch (err) {
      console.error('Failed to copy text: ', err);
      alert('Failed to copy to clipboard. Please copy manually.');
    } finally {
      document.body.removeChild(textArea);
    }
  }

  /**
   * Check if recipe has any meaningful nutrition data
   * @param recipe The recipe to check
   * @returns True if recipe has nutrition data > 0
   */
  hasNutritionData(recipe: Recipe): boolean {
    const calories = recipe.total_calories ? parseFloat(recipe.total_calories.toString()) : 0;
    const carbs = recipe.total_carbs ? parseFloat(recipe.total_carbs.toString()) : 0;
    const fat = recipe.total_fat ? parseFloat(recipe.total_fat.toString()) : 0;
    const protein = recipe.total_protein ? parseFloat(recipe.total_protein.toString()) : 0;
    
    return calories > 0 || carbs > 0 || fat > 0 || protein > 0;
  }

  /**
   * Safely parse nutrition values from string or number
   * @param value The value to parse
   * @returns Parsed number or 0 if invalid
   */
  getNutritionValue(value: any): number {
    if (value === null || value === undefined || value === '') {
      return 0;
    }
    const parsed = parseFloat(value.toString());
    return isNaN(parsed) ? 0 : parsed;
  }

  /**
   * Toggle the expanded state of recipe steps
   * @param recipeId The ID of the recipe to toggle
   */
  toggleSteps(recipeId: number): void {
    if (this.expandedSteps.has(recipeId)) {
      this.expandedSteps.delete(recipeId);
    } else {
      this.expandedSteps.add(recipeId);
    }
  }

  /**
   * Check if recipe steps are currently expanded
   * @param recipeId The ID of the recipe to check
   * @returns True if steps are expanded
   */
  isStepsExpanded(recipeId: number): boolean {
    return this.expandedSteps.has(recipeId);
  }
}
