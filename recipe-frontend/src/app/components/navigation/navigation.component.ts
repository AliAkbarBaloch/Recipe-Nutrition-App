import { Component } from '@angular/core';

/**
 * Navigation bar component - handles the top menu and mobile hamburger menu
 */
@Component({
  selector: 'app-navigation',
  templateUrl: './navigation.component.html',
  styleUrls: ['./navigation.component.css']
})
export class NavigationComponent {
  // Track if mobile menu is open
  mobileMenuOpen = false;

  /**
   * Toggle mobile menu on/off
   */
  toggleMobileMenu() {
    this.mobileMenuOpen = !this.mobileMenuOpen;
  }

  /**
   * Close mobile menu (usually when user clicks a link)
   */
  closeMobileMenu() {
    this.mobileMenuOpen = false;
  }
}
