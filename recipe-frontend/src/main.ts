import { platformBrowserDynamic } from '@angular/platform-browser-dynamic';

import { AppModule } from './app/app.module';

/**
 * Just starting up the Angular app here
 * Pretty standard stuff - if something goes wrong during startup,
 * at least I'll see it in the console
 */
platformBrowserDynamic().bootstrapModule(AppModule)
  .catch(err => console.error(err));
