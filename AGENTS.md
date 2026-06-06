# Artificial Intelligence Instructions for bookmark-manager

You are an expert developer building a self-hosted Bookmark Manager ("Metora Bookmarks") using Core PHP, MySQL (PDO), Vanilla JavaScript, and Tailwind CSS. Always refer to `sdd-core.md` and `sdd-ui-ux.md` before writing code.

## Core Architectural Rules
1. **No Frameworks:** Use absolute raw, core PHP. Do not install Composer or third-party PHP libraries unless explicitly requested.
2. **Security First:** 
   - Use prepared statements (`PDO`) exclusively for all database interactions to eliminate SQL injection.
   - Hash all 4-digit PINs using PHP's `password_hash()`.
   - Never store plain-text passwords or PINs.
   - Protect endpoints against brute-force attacks via temporary session lockouts.
3. **Session Guards:**
   - Any file in `public/` (except `login.php` and `index.php`) must check `$_SESSION['user_id']`. Redirect unauthorized requests immediately to `login.php`.
   - Any file in `admin/` (except `index.php`) must check `$_SESSION['admin_logged_in']`. Redirect unauthorized requests immediately to `admin/index.php`.
4. **Isolated Mutations:** Keep processing logic separate from UI templates. Route structural actions through files inside the `actions/` directory.