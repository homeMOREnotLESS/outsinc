OUTSINC Pathways — Developer Setup (minimal scaffold)

This workspace contains a starter scaffold based on the project README. It includes:

- SQL schema: sql/schema.sql
- PHP backend stubs: src/db.php, src/process_assessment.php
- Minimal public pages: public/index.html, public/registration.html, public/intake_wizard.html

Quick start (local MAMP / PHP + MySQL):

1. Create the database and tables
   - Import `sql/schema.sql` into your MySQL server (MAMP/phpMyAdmin or CLI).

2. Configure DB connection
   - Edit environment variables or `src/db.php` to match your DB credentials.
     Recommended env vars: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`.

3. Serve the `public` folder with PHP built-in server (for testing):

   php -S 0.0.0.0:8000 -t public

4. Open `http://localhost:8000` in your browser.

Notes and next steps
- This scaffold provides a minimal starting point. It does not implement full auth, security, or production-ready practices.
- Next work: implement secure registration (`src/register.php`), password hashing, session handling, and input validation.
- Consider adding Docker / docker-compose for reproducible development environment.

