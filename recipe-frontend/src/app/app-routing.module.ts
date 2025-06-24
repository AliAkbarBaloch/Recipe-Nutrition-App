import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { HomeComponent } from './components/home/home.component';
import { RecipeFormComponent } from './components/recipe-form/recipe-form.component';
import { RecipeListComponent } from './components/recipe-list/recipe-list.component';

/**
 * All my app routes - pretty straightforward setup
 */
const routes: Routes = [
  { path: '', component: HomeComponent },                    // Home page
  { path: 'recipes', component: RecipeListComponent },       // See all recipes
  { path: 'recipe/new', component: RecipeFormComponent },    // Add new recipe
  { path: 'recipe/edit/:id', component: RecipeFormComponent } // Edit recipe
];

/**
 * Router setup - nothing fancy, just basic navigation
 */
@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})
export class AppRoutingModule { }
