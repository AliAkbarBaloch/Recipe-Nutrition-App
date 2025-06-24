import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, FormArray, Validators } from '@angular/forms';
import { ActivatedRoute, Router } from '@angular/router';
import { Recipe, RecipeService } from '../../services/recipe.service';
import { NutritionService } from '../../services/nutrition.service';
import { LocalStorageService } from '../../services/local-storage.service';
import { Subject, takeUntil, debounceTime } from 'rxjs';

/**
 * Recipe form component - handles creating and editing recipes
 * 
 * This is where all the magic happens:
 * - Basic recipe info (name, description, servings, times)
 * - Dynamic ingredients with nutrition lookup
 * - Step-by-step instructions
 * - Auto-saves drafts so you don't lose work
 * - Form validation to keep things clean
 */
@Component({
  selector: 'app-recipe-form',
  templateUrl: './recipe-form.component.html',
  styleUrls: ['./recipe-form.component.css']
})
export class RecipeFormComponent implements OnInit, OnDestroy {
  // Main form for all the recipe data
  recipeForm: FormGroup;
  
  // Are we editing an existing recipe or making a new one?
  editMode = false;
  
  // If editing, this is the recipe ID
  recipeId?: number;
  
  // Nutrition data I get back from the API
  nutritionInfo: any = {};
  
  // Show loading spinner when fetching data
  isLoading = false;
  
  // Any error messages to show the user
  error: string | null = null;
  
  // List of ingredients from the nutrition API
  availableIngredients: string[] = [];
  
  // Where I store draft data in localStorage
  private readonly FORM_STORAGE_KEY = 'recipe_form_draft';
  
  // For cleaning up subscriptions when component is destroyed
  private destroy$ = new Subject<void>();

  constructor(
    private fb: FormBuilder,
    private recipeService: RecipeService,
    private nutritionService: NutritionService,
    private localStorage: LocalStorageService,
    private route: ActivatedRoute,
    private router: Router
  ) {
    this.recipeForm = this.createForm();
  }

  /**
   * Initialize the component - check if we're editing or creating new
   */
  ngOnInit(): void {
    this.route.params.subscribe(params => {
      if (params['id']) {
        this.editMode = true;
        this.recipeId = +params['id'];
        this.loadRecipe(this.recipeId);
      } else {
        // Only restore draft data if not in edit mode
        this.restoreFormData();
      }
    });
    
    // Setup auto-save for form inputs
    this.setupAutoSave();
  }

  /**
   * Clean up when component is destroyed
   */
  ngOnDestroy(): void {
    this.destroy$.next();
    this.destroy$.complete();
  }

  /**
   * Auto-save form data to localStorage so users don't lose their work
   * Waits 1 second after they stop typing before saving
   */
  private setupAutoSave(): void {
    this.recipeForm.valueChanges
      .pipe(
        takeUntil(this.destroy$),
        debounceTime(1000) // Wait 1 second after user stops typing
      )
      .subscribe((formValue) => {
        // Only auto-save if not in edit mode and form has some data
        if (!this.editMode && this.hasFormData(formValue)) {
          this.localStorage.setItem(this.FORM_STORAGE_KEY, formValue);
          console.log('Form data auto-saved to localStorage');
        }
      });
  }

  /**
   * Check if form has meaningful data to save
   */
  private hasFormData(formValue: any): boolean {
    return formValue.name || 
           formValue.description || 
           (formValue.ingredients && formValue.ingredients.some((ing: any) => ing.name)) ||
           (formValue.steps && formValue.steps.some((step: any) => step.instruction));
  }

  /**
   * Restore form data from localStorage
   */
  private restoreFormData(): void {
    const savedData = this.localStorage.getItem<any>(this.FORM_STORAGE_KEY);
    if (savedData) {
      try {
        // Clear existing form arrays
        this.clearFormArray(this.ingredients);
        this.clearFormArray(this.steps);
        
        // Restore basic fields
        this.recipeForm.patchValue({
          name: savedData.name || '',
          description: savedData.description || '',
          servings: savedData.servings || 1,
          prep_time: savedData.prep_time || 0,
          cook_time: savedData.cook_time || 0
        });
        
        // Restore ingredients
        if (savedData.ingredients && savedData.ingredients.length > 0) {
          savedData.ingredients.forEach((ingredient: any) => {
            this.ingredients.push(this.fb.group({
              name: [ingredient.name || '', Validators.required],
              quantity: [ingredient.quantity || 0, [Validators.required, Validators.min(0)]],
              unit: [ingredient.unit || 'g', Validators.required]
            }));
          });
        } else {
          this.ingredients.push(this.createIngredientForm());
        }
        
        // Restore steps
        if (savedData.steps && savedData.steps.length > 0) {
          savedData.steps.forEach((step: any) => {
            this.steps.push(this.fb.group({
              instruction: [step.instruction || '', Validators.required]
            }));
          });
        } else {
          this.steps.push(this.createStepForm());
        }
        
        console.log('Form data restored from localStorage');
      } catch (error) {
        console.error('Error restoring form data:', error);
        this.localStorage.removeItem(this.FORM_STORAGE_KEY);
      }
    }
  }

  /**
   * Clear all items from a FormArray
   */
  private clearFormArray(formArray: FormArray): void {
    while (formArray.length !== 0) {
      formArray.removeAt(0);
    }
  }

  /**
   * Clear saved form data
   */
  clearSavedData(): void {
    this.localStorage.removeItem(this.FORM_STORAGE_KEY);
    this.recipeForm.reset();
    this.recipeForm = this.createForm();
    console.log('Saved form data cleared');
  }

  createForm(): FormGroup {
    return this.fb.group({
      name: ['', Validators.required],
      description: [''],
      servings: [1, [Validators.required, Validators.min(1)]],
      prep_time: [0, Validators.min(0)],
      cook_time: [0, Validators.min(0)],
      ingredients: this.fb.array([this.createIngredientForm()]),
      steps: this.fb.array([this.createStepForm()])
    });
  }

  createIngredientForm(): FormGroup {
    return this.fb.group({
      name: ['', Validators.required],
      quantity: [0, [Validators.required, Validators.min(0)]],
      unit: ['g', Validators.required]
    });
  }

  createStepForm(): FormGroup {
    return this.fb.group({
      instruction: ['', Validators.required]
    });
  }

  get ingredients(): FormArray {
    return this.recipeForm.get('ingredients') as FormArray;
  }

  get steps(): FormArray {
    return this.recipeForm.get('steps') as FormArray;
  }

  addIngredient(): void {
    this.ingredients.push(this.createIngredientForm());
  }

  removeIngredient(index: number): void {
    this.ingredients.removeAt(index);
  }

  addStep(): void {
    this.steps.push(this.createStepForm());
  }

  removeStep(index: number): void {
    this.steps.removeAt(index);
  }

  onIngredientChange(index: number): void {
    const ingredient = this.ingredients.at(index);
    const ingredientName = ingredient.get('name')?.value;
    
    console.log(`Ingredient change at index ${index}: ${ingredientName}`);
    
    if (ingredientName && ingredientName.length > 2) {
      console.log(`Searching for nutrition info for: ${ingredientName}`);
      this.nutritionService.searchIngredient(ingredientName).subscribe(
        response => {
          console.log('Nutrition response:', response);
          if (response.success && response.data) {
            this.nutritionInfo[index] = response.data;
            console.log(`Nutrition info saved for index ${index}:`, this.nutritionInfo[index]);
          } else {
            this.nutritionInfo[index] = null;
            console.log(`No nutrition data found for: ${ingredientName}`);
          }
        },
        error => {
          console.error('Error fetching nutrition info:', error);
          this.nutritionInfo[index] = null;
        }
      );
    } else {
      console.log(`Ingredient name too short or empty: ${ingredientName}`);
    }
  }

  loadRecipe(id: number): void {
    this.isLoading = true;
    this.error = null;
    
    this.recipeService.getRecipe(id).subscribe({
      next: (response) => {
        if (response.success && response.data && !Array.isArray(response.data)) {
          this.patchFormWithRecipe(response.data);
        } else {
          this.error = 'Recipe not found';
        }
        this.isLoading = false;
      },
      error: (error) => {
        console.error('Error loading recipe:', error);
        this.error = 'Failed to load recipe. Please try again.';
        this.isLoading = false;
      }
    });
  }

  patchFormWithRecipe(recipe: Partial<Recipe>): void {
    // Clear arrays
    while (this.ingredients.length) {
      this.ingredients.removeAt(0);
    }
    while (this.steps.length) {
      this.steps.removeAt(0);
    }

    // Patch basic form data
    this.recipeForm.patchValue({
      name: recipe.name || '',
      description: recipe.description || '',
      servings: recipe.servings || 1,
      prep_time: recipe.prep_time || 0,
      cook_time: recipe.cook_time || 0
    });

    // Add ingredients
    if (recipe.ingredients) {
      recipe.ingredients.forEach(ingredient => {
        this.ingredients.push(this.fb.group({
          name: [ingredient.name, Validators.required],
          quantity: [ingredient.quantity, [Validators.required, Validators.min(0)]],
          unit: [ingredient.unit, Validators.required]
        }));
      });
    }

    // Add steps
    if (recipe.steps) {
      recipe.steps.forEach(step => {
        this.steps.push(this.fb.group({
          instruction: [step.instruction, Validators.required]
        }));
      });
    }
  }

  onSubmit(): void {
    if (this.recipeForm.valid) {
      this.isLoading = true;
      this.error = null;
      const formValue = this.recipeForm.value;

      // Add step numbers to steps
      if (formValue.steps) {
        formValue.steps = formValue.steps.map((step: any, index: number) => ({
          ...step,
          step_number: index + 1
        }));
      }

      // Add nutrition information to ingredients if available
      if (formValue.ingredients) {
        formValue.ingredients = formValue.ingredients.map((ingredient: any, index: number) => {
          const nutrition = this.nutritionInfo[index];
          return {
            ...ingredient,
            carbs_per_100g: nutrition?.carbs_per_100g || 0,
            fat_per_100g: nutrition?.fat_per_100g || 0,
            protein_per_100g: nutrition?.protein_per_100g || 0,
            calories_per_100g: nutrition?.calories_per_100g || 0
          };
        });
      }

      if (this.editMode && this.recipeId) {
        // Update existing recipe
        this.recipeService.updateRecipe(this.recipeId, formValue).subscribe({
          next: (response) => {
            if (response.success) {
              // Clear saved draft data on successful submission
              this.localStorage.removeItem(this.FORM_STORAGE_KEY);
              this.router.navigate(['/recipes']);
            } else {
              this.error = response.message || 'Failed to update recipe';
            }
            this.isLoading = false;
          },
          error: (error) => {
            console.error('Error updating recipe:', error);
            this.error = 'Failed to update recipe. Please try again.';
            this.isLoading = false;
          }
        });
      } else {
        // Create new recipe
        this.recipeService.createRecipe(formValue).subscribe({
          next: (response) => {
            if (response.success) {
              // Clear saved draft data on successful submission
              this.localStorage.removeItem(this.FORM_STORAGE_KEY);
              this.router.navigate(['/recipes']);
            } else {
              this.error = response.message || 'Failed to create recipe';
            }
            this.isLoading = false;
          },
          error: (error) => {
            console.error('Error creating recipe:', error);
            this.error = 'Failed to create recipe. Please try again.';
            this.isLoading = false;
          }
        });
      }
    } else {
      this.markFormGroupTouched();
    }
  }

  markFormGroupTouched(): void {
    Object.keys(this.recipeForm.controls).forEach(key => {
      const control = this.recipeForm.get(key);
      control?.markAsTouched();

      if (control instanceof FormArray) {
        control.controls.forEach(c => {
          Object.keys((c as FormGroup).controls).forEach(k => {
            (c as FormGroup).get(k)?.markAsTouched();
          });
        });
      }
    });
  }

  cancel(): void {
    this.router.navigate(['/recipes']);
  }
}
