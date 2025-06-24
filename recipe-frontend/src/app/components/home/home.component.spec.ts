// Tests for the home page component
// This one has navigation logic so we need to spy on the router

import { ComponentFixture, TestBed } from '@angular/core/testing';
import { Router } from '@angular/router';
import { HomeComponent } from './home.component';
import { MaterialModule } from '../../shared/material.module';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';

describe('HomeComponent', () => {
  let component: HomeComponent;
  let fixture: ComponentFixture<HomeComponent>;
  let router: jasmine.SpyObj<Router>;

  beforeEach(async () => {
    const routerSpy = jasmine.createSpyObj('Router', ['navigate']);

    await TestBed.configureTestingModule({
      declarations: [ HomeComponent ],
      imports: [ MaterialModule, BrowserAnimationsModule ],
      providers: [
        { provide: Router, useValue: routerSpy }
      ]
    })
    .compileComponents();

    fixture = TestBed.createComponent(HomeComponent);
    component = fixture.componentInstance;
    router = TestBed.inject(Router) as jasmine.SpyObj<Router>;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });

  it('should navigate to create recipe', () => {
    component.navigateToCreateRecipe();
    expect(router.navigate).toHaveBeenCalledWith(['/recipe/new']);
  });

  it('should navigate to recipes', () => {
    component.navigateToRecipes();
    expect(router.navigate).toHaveBeenCalledWith(['/recipes']);
  });

  it('should render hero section', () => {
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.hero-section')).toBeTruthy();
    expect(compiled.querySelector('.hero-title')).toBeTruthy();
  });

  it('should render features section', () => {
    const compiled = fixture.nativeElement as HTMLElement;
    expect(compiled.querySelector('.features-section')).toBeTruthy();
    expect(component.features.length).toBe(4);
  });
});
