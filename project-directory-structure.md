bookmark-manager/
├── .cursorrules           # AI behavior boundaries
├── sdd-core.md            # Backend & db specifications
├── sdd-ui-ux.md           # Interface & theme specifications
├── schema.sql             # DB setup scripts
├── config/
│   ├── db.php             # Git-ignored localized PDO instance
│   └── db.example.php     # Template configuration
├── includes/
│   ├── header.php            # Session initiation, security, design assets
│   └── footer.php            # Modals and global JS utilities
├── public/
│   ├── index.php             # Router/Entry node
│   ├── login.php             # Regular user login screen
│   ├── dashboard.php         # Primary system viewport
│   └── actions/
│       ├── auth_verify.php       # Processes dashboard user login
│       ├── admin_user_crud.php   # Handles account generation/deletion
│       ├── bookmark_crud.php     # Handles bookmark CRUD operations
│       ├── category_crud.php     # Handles categories/subcategories/pinning
│       └── toggle_edit.php       # Background AJAX endpoint for Edit Mode
└── admin/
    ├── index.php             # Admin authentication barrier (Password: Nego@2026)
    └── portal.php            # Admin configuration node (Create/Delete users)