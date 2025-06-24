import { NgModule } from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { HttpClientModule } from '@angular/common/http';
import { ReactiveFormsModule, FormsModule } from '@angular/forms';

import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { HomeComponent } from './components/home/home.component';
import { RecipeFormComponent } from './components/recipe-form/recipe-form.component';
import { RecipeListComponent } from './components/recipe-list/recipe-list.component';
import { NavigationComponent } from './components/navigation/navigation.component';
import { MaterialModule } from './shared/material.module';

/**
 * Main app module - where I wire everything together
 * Got all the Angular stuff I need: Material Design, HTTP for API calls,
 * reactive forms for the recipe form, and all my components
 */
@NgModule({
  declarations: [
    AppComponent,
    HomeComponent,
    RecipeFormComponent,
    RecipeListComponent,
    NavigationComponent
  ],
  imports: [
    BrowserModule,
    BrowserAnimationsModule,
    AppRoutingModule,
    HttpClientModule,
    ReactiveFormsModule,
    FormsModule,
    MaterialModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
