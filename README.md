# Recipe Nutrition App

Hey there! This is my recipe management app that I built to help track recipes and their nutritional information. It's a full-stack application with a Laravel backend API and an Angular frontend.

## What's This All About?

I wanted to create something that would let me store my recipes and automatically calculate nutrition facts without having to manually enter everything. The app connects to an external nutrition API to get ingredient data, but I also built in a fallback system with mock data for when the API is down.

## How It's Built

### Backend (Laravel 11)
The backend is a REST API that handles all the recipe data and talks to the nutrition API. I'm using:
- Laravel for the API framework
- MySQL for storing recipes, ingredients, and steps
- Guzzle HTTP client for external API calls
- A mock mode that kicks in when the external API isn't available

### Frontend (Angular 15)
The frontend is built with Angular and Material Design components. Features include:
- Material Design UI components for a clean look
- Tailwind CSS for quick styling
- TypeScript for better code quality
- Responsive design that works on mobile and desktop

## Key Features

**For Recipes:**
- Add, edit, and delete recipes
- Manage ingredients with quantities and units
- Step-by-step cooking instructions
- Automatic nutrition calculation based on ingredients

**For Nutrition:**
- Pulls nutrition data from external API
- Shows calories, carbs, fat, and protein per recipe
- Calculates totals based on ingredient quantities
- Fallback to mock data when API is unavailable

**UI/UX:**
- Clean, modern interface using Material Design
- Recipe cards with expandable sections
- Statistics showing average calories and cooking time
- Mobile-friendly responsive design
- **Language**: TypeScript 4.9+
- **Build Tool**: Angular CLI with Webpack

## 📁 Project Structure

```
## Getting Started

### What You'll Need
- PHP 8.1 or higher
- Composer (for PHP packages)
- MySQL 8.0 or newer
- Node.js 16+ and npm
- A decent code editor

### Setting Up the Backend

1. **Get the code**
   ```bash
   git clone <your-repo-url>
   cd Recipe-Nutrition-Management
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Set up your environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your database**
   Edit the `.env` file with your database details:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=recipe_nutrition_app
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Create the database and tables**
   ```bash
   # Create the database first (using your MySQL client)
   mysql -u root -p
   CREATE DATABASE recipe_nutrition_app;
   exit
   
   # Then run the migrations
   php artisan migrate
   ```

6. **Start the backend server**
   ```bash
   php artisan serve
   ```
   Your API will be running at `http://localhost:8000`

### Setting Up the Frontend

1. **Go to the frontend folder**
   ```bash
   cd recipe-frontend
   ```

2. **Install Node.js dependencies**
   ```bash
   npm install
   ```

3. **Start the Angular development server**
   ```bash
   npm start
   ```
   Frontend application will be available at `http://localhost:4200`

### 🧪 Running Tests

#### Backend Tests
```bash
# Run Laravel tests
cd Recipe-Nutrition-Management
php artisan test
```

#### Frontend Tests
```bash
# Run Angular tests
cd recipe-frontend
npm test
```

## 📱 Frontend Features

### ✨ **Material Design UI**
- Modern, responsive interface using Angular Material components
- Material Design icons and typography
- Consistent design language across all components

### 🎨 **Tailwind CSS Integration**
- Utility-first CSS for rapid styling
- Custom color palette (primary/secondary variants)
   cd recipe-frontend
   ```

2. **Install the packages**
   ```bash
   npm install
   ```

3. **Start the development server**
   ```bash
   npm start
   ```
   The frontend will be available at `http://localhost:4200`

That's it! Now you should have both the backend running on port 8000 and the frontend on port 4200.

## How to Use

1. **Access the app** at `http://localhost:4200`
2. **Add a recipe** by clicking the "Add Recipe" button
3. **Enter ingredients** - the app will try to fetch nutrition data automatically
4. **Add cooking steps** in order
5. **Save your recipe** and view the calculated nutrition facts

## API Endpoints

If you want to use the API directly:

- `GET /api/recipes` - Get all recipes
- `POST /api/recipes` - Create a new recipe
- `GET /api/recipes/{id}` - Get a specific recipe
- `PUT /api/recipes/{id}` - Update a recipe
- `DELETE /api/recipes/{id}` - Delete a recipe

For nutrition data:
- `GET /api/nutrition/ingredients` - Get available ingredients
- `GET /api/nutrition/ingredients/search?ingredient=chicken` - Search for specific ingredient

## Troubleshooting

**External API not working?**
Set `NUTRITION_API_MOCK_MODE=true` in your `.env` file to use mock data instead.

**Frontend not connecting to backend?**
Make sure your Laravel server is running on port 8000 and check the console for CORS errors.

**Database issues?**
Double-check your database credentials in the `.env` file and make sure MySQL is running.

## What's Next?

Some ideas for future improvements:
- User accounts and authentication
- Recipe sharing between users
- Photo uploads for recipes
- Meal planning features
- Shopping list generation
- More detailed nutrition tracking

## Contributing

Feel free to fork this project and submit pull requests. The code is organized to be easy to understand and extend.

## Tech Stack Summary

**Backend:**
- Laravel 11 (PHP framework)
- MySQL (database)
- Guzzle (HTTP client for external API calls)

**Frontend:**
- Angular 15 (TypeScript framework)
- Angular Material (UI components)
- Tailwind CSS (styling)

**Development:**
- Composer (PHP dependencies)
- npm (Node.js dependencies)
- Laravel Artisan CLI
- Angular CLI
3. Set `NUTRITION_API_MOCK_MODE=false`
4. Configure proper database credentials
5. Run `composer install --optimize-autoloader --no-dev`
6. Set up proper web server configuration (Apache/Nginx)

### Frontend Deployment
1. **Build for production**
   ```bash
   cd recipe-frontend
   npm run build
   ```
2. **Deploy the `dist/` folder** to your web server
3. **Configure web server** to serve the Angular app with proper routing support

### 🔄 Full-Stack Integration
- Backend API serves at `/api/*` endpoints
- Frontend consumes the Laravel API for all data operations
- CORS configured for cross-origin requests during development
- Production deployment can use the same domain for both frontend and backend

## 📚 Documentation

- **Frontend Setup**: See `recipe-frontend/FRONTEND_SETUP.md` for detailed frontend configuration
- **Test Results**: See `recipe-frontend/TEST_RESULTS.md` for current test status
- **API Documentation**: All endpoints documented with examples below

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. **Backend**: Follow PSR-12 coding standards, add PHPDoc comments
4. **Frontend**: Follow Angular style guide, use TypeScript strictly
5. Add proper error handling and tests
6. Test all API endpoints and UI components
7. Commit your changes (`git commit -m 'Add amazing feature'`)
8. Push to the branch (`git push origin feature/amazing-feature`)
9. Open a Pull Request

### 📋 Development Guidelines
- **Backend**: Include comprehensive error handling for external dependencies
- **Frontend**: Use Material Design principles and Tailwind utilities
- **Testing**: Maintain test coverage for both frontend and backend
- **Documentation**: Update relevant documentation for new features

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

## 👨‍💻 Author

**Ali Akbar Baloch**
- GitHub: [@AliAkbarBaloch](https://github.com/AliAkbarBaloch)
- Repository: [Recipe-Nutrition-App](https://github.com/AliAkbarBaloch/Recipe-Nutrition-App)

---

### 🎯 **Quick Start Summary**

1. **Clone**: `git clone https://github.com/AliAkbarBaloch/Recipe-Nutrition-App.git`
2. **Backend**: `composer install` → configure `.env` → `php artisan migrate` → `php artisan serve`
3. **Frontend**: `cd recipe-frontend` → `npm install` → `npm start`
4. **Access**: Backend at `http://localhost:8000`, Frontend at `http://localhost:4200`

**Status**: ✅ **Ready for Development** - All dependencies installed, tests passing, full-stack integration complete!


