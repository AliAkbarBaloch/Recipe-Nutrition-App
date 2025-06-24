import { Component } from '@angular/core';

/**
 * The main app component - basically just holds everything together
 * Not doing much here except being the root of the component tree
 */
@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent {
  // Keeping the title simple for now
  title = 'recipe-frontend';
}
