# Laravel Concert Ticket Booking System

A complete web application for managing concert ticket bookings with user authentication, role-based authorization, and a comprehensive review system.

## Features

### User Authentication & Authorization
- **User Registration & Login** - Built with Laravel Breeze
- **Role-Based Access Control** - Admin and User roles
- **Session Management** - Flash messages and cookie storage

### Concert Management (Admin)
- **Create Concerts** - Add new concert events with details
- **Edit Concerts** - Update concert information
- **Delete Concerts** - Remove concerts from system
- **View All Concerts** - Browse available concerts

### Ticket Booking System (Users)
- **Browse Concerts** - View all available concerts
- **Check Availability** - See ticket availability in real-time
- **Book Tickets** - Purchase tickets for concerts
- **Cancel Orders** - Cancel bookings with status tracking
- **Order History** - View all past and current bookings

### Review System
- **Write Reviews** - Add ratings and comments for attended concerts
- **Edit Reviews** - Modify your own reviews
- **Delete Reviews** - Remove reviews
- **View Reviews** - See all reviews on concert details page

### Search & Filter
- **Search by Artist/Title** - Find concerts by artist or concert name
- **Filter by Date** - View concerts on specific dates
- **Filter by Venue** - Find concerts at specific venues

## Technology Stack

- **Framework**: Laravel 10
- **Database**: MySQL
- **Frontend**: Bootstrap 5
- **Authentication**: Laravel Breeze
- **ORM**: Eloquent

## Installation & Setup

### Prerequisites
- PHP 8.1+
- Composer
- MySQL 5.7+
- Node.js & npm

### Step 1: Clone/Navigate to Project
```bash
cd "d:\AWAD Concert\concert-system"
```

### Step 2: Install Dependencies
```bash
composer install
npm install
```

### Step 3: Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` file with your database credentials:
```
DB_DATABASE=concert_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### Step 4: Create Database
```bash
# Using MySQL
mysql -u root
CREATE DATABASE concert_db;
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Build Frontend Assets
```bash
npm run dev
```

### Step 7: Start Development Server
```bash
php artisan serve
```

Visit: `http://127.0.0.1:8000`

## Database Schema

### Users Table
```
- id (primary key)
- name
- email (unique)
- password (hashed)
- role (enum: admin, user)
- email_verified_at
- timestamps
```

### Concerts Table
```
- id (primary key)
- title (string)
- artist (string)
- venue (string)
- date (date)
- description (text)
- ticket_price (decimal)
- total_ticket (integer)
- created_by (foreign key → users)
- timestamps
```

### Orders Table
```
- id (primary key)
- user_id (foreign key → users)
- concert_id (foreign key → concerts)
- quantity (integer)
- total_price (decimal)
- status (enum: confirmed, cancelled)
- timestamps
```

### Reviews Table
```
- id (primary key)
- user_id (foreign key → users)
- concert_id (foreign key → concerts)
- rating (integer: 1-5)
- comment (text)
- timestamps
```

## Routing Structure

### Public Routes
```
GET  /                          - Home page
GET  /concerts                  - List all concerts
GET  /concerts/{id}             - Show concert details
GET  /concerts/search           - Search concerts
GET  /concerts/filter           - Filter concerts
```

### Authentication Routes
```
GET  /register                  - Registration form
POST /register                  - Create account
GET  /login                     - Login form
POST /login                     - Authenticate user
POST /logout                    - Logout user
```

### User Routes (Require Authentication)
```
GET  /orders                    - View user's orders
GET  /orders/{id}               - View order details
GET  /concerts/{id}/orders/create - Book concert
POST /concerts/{id}/orders      - Store booking
PUT  /orders/{id}/cancel        - Cancel booking
GET  /profile                   - View profile
GET  /profile/orders            - View order history
GET  /profile/reviews           - View review history
GET  /concerts/{id}/reviews/create - Create review
POST /concerts/{id}/reviews     - Store review
GET  /reviews/{id}/edit         - Edit review form
PUT  /reviews/{id}              - Update review
DELETE /reviews/{id}            - Delete review
```

### Admin Routes (Require Authentication + Admin Role)
```
GET  /concerts/create           - Create concert form
POST /concerts                  - Store concert
GET  /concerts/{id}/edit        - Edit concert form
PUT  /concerts/{id}             - Update concert
DELETE /concerts/{id}           - Delete concert
```

## Models & Relationships

### User Model
```php
// Relationships
hasMany(Order::class)
hasMany(Review::class)

// Attributes
protected $fillable = ['name', 'email', 'password', 'role']
```

### Concert Model
```php
// Relationships
belongsTo(User::class, 'created_by') - creator
hasMany(Order::class)
hasMany(Review::class)

// Attributes
protected $fillable = ['title', 'artist', 'venue', 'date', 'description', 'ticket_price', 'total_ticket', 'created_by']
```

### Order Model
```php
// Relationships
belongsTo(User::class)
belongsTo(Concert::class)

// Attributes
protected $fillable = ['user_id', 'concert_id', 'quantity', 'total_price', 'status']
```

### Review Model
```php
// Relationships
belongsTo(User::class)
belongsTo(Concert::class)

// Attributes
protected $fillable = ['user_id', 'concert_id', 'rating', 'comment']
```

## Authorization Policies

### ConcertPolicy
- **create**: Only admin users
- **update**: Only admin creator
- **delete**: Only admin creator

### OrderPolicy
- **view**: Only order owner
- **update**: Only order owner
- **delete**: Only order owner

### ReviewPolicy
- **update**: Only review author
- **delete**: Only review author

## Testing Workflow

### 1. Create Test Users
```php
// Admin User
User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'admin'
]);

// Regular User
User::create([
    'name' => 'Regular User',
    'email' => 'user@example.com',
    'password' => bcrypt('password'),
    'role' => 'user'
]);
```

### 2. Test Admin Functions
1. Login as admin
2. Navigate to "Create Concert"
3. Fill in concert details
4. Submit form
5. View created concert
6. Edit concert details
7. View concert show page

### 3. Test User Booking
1. Login as regular user
2. Browse concerts on home page
3. Click "Book Ticket" on available concert
4. Select number of tickets
5. Confirm booking
6. View order in "My Orders"
7. Cancel order
8. Write review for concert

### 4. Test Search & Filter
1. Use artist search to find concerts
2. Filter by concert date
3. Filter by venue
4. Verify results

### 5. Test Profile
1. View user profile
2. Check order history
3. Check review history
4. View individual reviews

## File Structure

```
concert-system/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Concert.php
│   │   ├── Order.php
│   │   └── Review.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ConcertController.php
│   │   │   ├── OrderController.php
│   │   │   ├── ReviewController.php
│   │   │   └── ProfileController.php
│   └── Policies/
│       ├── ConcertPolicy.php
│       ├── OrderPolicy.php
│       └── ReviewPolicy.php
├── database/
│   └── migrations/
│       ├── *_create_users_table.php
│       ├── *_add_role_to_users_table.php
│       ├── *_create_concerts_table.php
│       ├── *_create_orders_table.php
│       └── *_create_reviews_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php
│       │   └── navigation.blade.php
│       ├── concerts/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       ├── orders/
│       │   ├── index.blade.php
│       │   ├── create.blade.php
│       │   └── show.blade.php
│       ├── reviews/
│       │   ├── create.blade.php
│       │   └── edit.blade.php
│       └── profile/
│           ├── show.blade.php
│           ├── orders.blade.php
│           └── reviews.blade.php
└── routes/
    └── web.php
```

## Validation Rules

### Concert Validation
- `title`: required, string, max 255
- `artist`: required, string, max 255
- `venue`: required, string, max 255
- `date`: required, date, after today
- `description`: required, string
- `ticket_price`: required, numeric, min 0
- `total_ticket`: required, integer, min 1

### Order Validation
- `quantity`: required, integer, min 1, max 100

### Review Validation
- `rating`: required, integer, min 1, max 5
- `comment`: required, string, max 1000

## Security Features

- **CSRF Protection**: All forms protected with CSRF tokens
- **Authorization**: Policy-based access control
- **Input Validation**: Server-side validation on all inputs
- **Password Hashing**: Bcrypt password encryption
- **SQL Injection Prevention**: Eloquent ORM prevents SQL injection
- **XSS Prevention**: Blade template escaping

## Performance Considerations

- **Pagination**: Concert and order lists paginated (15 per page)
- **Eager Loading**: Using `with()` to prevent N+1 queries
- **Database Indexes**: Foreign keys indexed automatically
- **Asset Compilation**: CSS/JS compiled with Mix

## Future Enhancements

- Payment gateway integration
- Email notifications for bookings
- Refund system
- Advanced analytics dashboard
- Email verification
- Two-factor authentication
- API endpoints
- Real-time ticket availability
- Wishlist feature
- Social sharing

## Troubleshooting

### Database Connection Error
- Verify MySQL is running
- Check .env database credentials
- Ensure database exists

### Migration Failures
- Run `php artisan migrate:rollback`
- Check migration syntax errors
- Verify foreign key relationships

### View Errors
- Clear view cache: `php artisan view:clear`
- Check blade syntax
- Verify routes are correctly mapped

### Authentication Issues
- Clear all caches: `php artisan cache:clear`
- Reset sessions: `php artisan session:table` then migrate
- Verify email doesn't exist for registration

## Support & Documentation

- [Laravel Documentation](https://laravel.com/docs)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Blade Templates](https://laravel.com/docs/blade)
- [Policies & Authorization](https://laravel.com/docs/authorization)

## License

This project is open source and available for educational purposes.
