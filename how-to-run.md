Here's how to run the bookmark manager application:

## Step 1: Set up the database
1. Open phpMyAdmin (usually at http://localhost/phpmyadmin )
2. Create a new database named bookmark_manager
3. Import the schema.sql file into this database to create the necessary tables
## Step 2: Configure database connection
- The default configuration in config/db.php uses:
  - Host: localhost
  - Database name: bookmark_manager
  - Username: root
  - Password: (empty)
- If your MySQL setup uses different credentials, update config/db.php accordingly
## Step 3: Start XAMPP services
1. Open XAMPP Control Panel
2. Start Apache and MySQL modules
## Step 4: Create a user via admin portal
1. Open your browser and go to: http://localhost/code/gh/bookmark-manager/admin/
2. Login with password: 
3. Create a new user by entering a username and a 4-digit PIN
## Step 5: Login and use the application
1. Go to: http://localhost/code/gh/bookmark-manager/public/
2. Login with the username and PIN you just created
3. You're now in the dashboard and can start adding categories and bookmarks!