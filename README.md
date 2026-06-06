# Metora Bookmarks
Personalised Bookmark Manager

## Features
- 🔐 PIN-based login (4-digit PIN)
- 📁 Nested categories (main categories + subcategories)
- 📌 Pin favorite categories to the top
- ✏️ Edit category names, bookmark titles, and URLs
- 🗑️ Delete categories and bookmarks
- 🎨 Cyber-glass theme with neon yellow accents
- 📱 Fully responsive design

## How to Use
1. Set up the database using `schema.sql`
2. Configure database connection in `config/db.php`
3. Go to `/admin` to create a user (password: Nego@2026)
4. Go to `/public` and log in with your PIN
5. Click "Edit Mode" to start adding categories and bookmarks
6. Click category names to expand/collapse them

## Stack
- Core PHP (no frameworks)
- MySQL (PDO)
- Vanilla JavaScript
- Tailwind CSS
- Montserrat Font (Google Fonts)
