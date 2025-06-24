import { Component } from '@angular/core';
import { Router } from '@angular/router';

/**
 * Home page component - just a landing page with some info about the app
 */
@Component({
  selector: 'app-home',
  templateUrl: './home.component.html',
  styleUrls: ['./home.component.css']
})
export class HomeComponent {
  
  /** 
   * Features I want to highlight on the home page
   */
  features = [
    {
      icon: 'restaurant_menu',
      title: 'Recipe Management',
      description: 'Create, edit, and organize your favorite recipes with ease'
    },
    {
      icon: 'analytics',
      title: 'Nutrition Tracking',
      description: 'Get detailed nutritional information for all your ingredients'
    },
    {
      icon: 'save',
      title: 'Auto-Save',
      description: 'Never lose your work with automatic form persistence'
    },
    {
      icon: 'responsive',
      title: 'Mobile Friendly',
      description: 'Access your recipes anywhere, on any device'
    }
  ];

  constructor(private router: Router) {}

  /**
   * Take them to the recipe creation page
   */
  navigateToCreateRecipe() {
    this.router.navigate(['/recipe/new']);
  }

  /**
   * Take them to see all recipes
   */
  navigateToRecipes() {
    this.router.navigate(['/recipes']);
  }
}
