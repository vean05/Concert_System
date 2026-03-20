# Quick Start Testing Guide

## Getting Started

### Step 1: Prepare Your Environment

1. **Ensure MySQL is Running**
   - Start MySQL service
   - Create database: `concert_db`

2. **Navigate to Project**
   ```bash
   cd "d:\AWAD Concert\concert-system"
   ```

3. **Configure .env File**
   ```
   DB_DATABASE=concert_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

### Step 2: Run Migrations

```bash
php artisan migrate
```

This creates all necessary database tables with proper relationships.

### Step 3: Start Development

**Terminal 1:**
```bash
php artisan serve
```

**Terminal 2:**
```bash
npm run dev
```

Visit: `http://127.0.0.1:8000`

## Manual Testing Scenarios

### Scenario 1: User Registration & Authentication

**Steps:**
1. Click "Register" in navigation
2. Fill in:
   - Name: `Test User`
   - Email: `user@example.com`
   - Password: `password123`
   - Confirm: `password123`
3. Click "Register"
4. Verify redirected to dashboard

**Expected**: User created with role="user"

---

### Scenario 2: Admin Registration & Concert Creation

**Steps:**
1. Register another account:
   - Name: `Admin User`
   - Email: `admin@example.com`
   - Password: `password123`

2. **Make this user admin** (using database):
   ```php
   php artisan tinker
   User::where('email', 'admin@example.com')->update(['role' => 'admin']);
   ```

3. Login as admin
4. Click "Create Concert" button
5. Fill form:
   - Title: `Taylor Swift Eras Tour`
   - Artist: `Taylor Swift`
   - Venue: `Madison Square Garden`
   - Date: Select future date
   - Description: `The greatest concert ever`
   - Ticket Price: `250.00`
   - Total Tickets: `500`
6. Click "Create Concert"

**Expected**: Concert created successfully, flash message appears

---

### Scenario 3: Browse & Search Concerts

**Steps:**
1. (Any user) Click "Concerts" in navigation
2. View concert list with thumbnails
3. Try search: Type "Taylor" in search box
4. Click Filter or search button
5. Try date filter: Select concert date
6. Try venue filter: Type "Square"

**Expected**: Concerts filtered based on criteria

---

### Scenario 4: Book Tickets (User)

**Steps:**
1. Login as regular user (Test User)
2. Go to Concerts → Click "Book Ticket" on concert
3. Select quantity: `2` tickets
4. Total price updates automatically
5. Click "Confirm Booking"

**Expected**: Order created, redirect to orders page with success message

---

### Scenario 5: View Orders

**Steps:**
1. Login as user who booked
2. Click "My Orders" in navigation
3. View all orders with:
   - Concert title
   - Artist
   - Date
   - Quantity
   - Total price
   - Status (Confirmed/Cancelled)
4. Click "View Details" on order
5. See full order information

**Expected**: All order details display correctly

---

### Scenario 6: Write Review

**Steps:**
1. Login as user who booked the concert
2. Go to concert detail page
3. Scroll to reviews section
4. Click "Write a Review"
5. Fill review form:
   - Rating: Select 5 stars
   - Comment: "Amazing concert! Highly recommend."
6. Click "Submit Review"

**Expected**: Review appears on concert page from logged-in user

---

### Scenario 7: Edit Review

**Steps:**
1. View your review on concert page
2. Click "Edit" button
3. Change rating to 4 stars
4. Update comment
5. Click "Update Review"

**Expected**: Review updated, shows edit timestamp

---

### Scenario 8: Cancel Order

**Steps:**
1. Go to "My Orders"
2. Click "Cancel Order" button
3. Confirm cancellation
4. Status changes to "Cancelled"

**Expected**: Order marked as cancelled, button no longer shows

---

### Scenario 9: Admin Edit/Delete Concert

**Steps:**
1. Login as admin
2. Go to Concerts
3. Click "Edit" on concert you created
4. Change title to: `Taylor Swift Eras Tour 2024`
5. Click "Update Concert"
6. Verify updated

**Delete:**
7. Click "Delete" button
8. Confirm deletion
9. Concert removed from list

**Expected**: Concert edited/deleted successfully

---

### Scenario 10: Profile & History

**Steps:**
1. Login as user
2. Click username dropdown → "My Profile"
3. View profile card with:
   - Name
   - Email
   - Role badge
4. See recent orders
5. See recent reviews
6. Click "View All Orders" & "View All Reviews"

**Expected**: All order and review history displays

---

## Authorization Testing

### Test 1: Admin-Only Access

**Steps:**
1. Login as regular user
2. Try accessing: `http://localhost:8000/concerts/create`
3. Should show 403 Forbidden error

**Result**: ✅ Authorization policy works

---

### Test 2: Own Review Only

**Steps:**
1. Login as User A
2. Write review on concert
3. Logout
4. Login as User B
5. Try accessing User A's review edit URL
6. Should show 403 error

**Result**: ✅ Review policy works

---

### Test 3: Own Order Only

**Steps:**
1. User A books ticket
2. Logout
3. Login as User B
4. Try accessing User A's order detail URL
5. Should show 403 error

**Result**: ✅ Order policy works

---

## Database Verification

### Check Database Tables

```bash
php artisan tinker

# Verify users
User::all();

# Verify concerts
Concert::all();

# Verify orders with relationships
Order::with('user', 'concert')->get();

# Verify reviews
Review::with('user', 'concert')->get();
```

---

## Flash Message Testing

### Booking Success Message
- Book a ticket
- Should see: "Booking successful!"

### Review Success Message
- Write/edit/delete review
- Should see appropriate success message

### Error Message
- Try booking with invalid quantity
- Should see error message

---

## Filter & Search Testing

### Test Search
- Search "Taylor" → Should filter concerts
- Search non-existent → Should show empty
- Search partial match → Should find matches

### Test Date Filter
- Select specific date
- Only concerts on that date show

### Test Venue Filter
- Filter "Madison"
- Only matching venues show

---

## Responsive Design Testing

1. Open DevTools (F12)
2. Toggle device toolbar
3. Test on:
   - Mobile (375px)
   - Tablet (768px)
   - Desktop (1024px)

All pages should be responsive

---

## Common Test Data

### Test User Accounts
```
Admin Account:
Email: admin@example.com
Password: password123
Role: admin

Regular User:
Email: user@example.com
Password: password123
Role: user

Second User:
Email: user2@example.com
Password: password123
Role: user
```

### Test Concert Data
When creating test concerts, use:
- **Artists**: Taylor Swift, The Weeknd, Beyoncé, Ed Sheeran
- **Venues**: Madison Square Garden, Staples Center, United Center, MetLife Stadium
- **Dates**: Various future dates

---

## Success Criteria

- ✅ User registration works
- ✅ Admin can create/edit/delete concerts
- ✅ Users can book tickets
- ✅ Ticket availability decreases with bookings
- ✅ Users can write/edit/delete reviews
- ✅ Users can cancel orders
- ✅ Search and filter work correctly
- ✅ Authorization prevents unauthorized access
- ✅ Flash messages appear correctly
- ✅ UI is responsive
- ✅ Database relationships work
- ✅ All validation rules applied

---

## Troubleshooting

### Issue: Database connection fails
**Solution**: 
- Verify MySQL is running
- Check .env credentials
- Create concert_db database

### Issue: 500 error after migration
**Solution**:
- Check migration files for syntax errors
- Run: `php artisan migrate:rollback`
- Re-run: `php artisan migrate`

### Issue: Styles not loading
**Solution**:
- Run: `npm run dev`
- Clear browser cache
- Hard refresh (Ctrl+Shift+R)

### Issue: Can't login
**Solution**:
- Verify user exists in database
- Check password is correct
- Clear sessions: `php artisan session:table && php artisan migrate`

---

## Next Steps After Testing

1. **Documentation**: Review SYSTEM_GUIDE.md
2. **Production**: Set up production database
3. **Deployment**: Deploy to server
4. **Monitoring**: Set up error logging
5. **Enhancement**: Add features based on requirements

