# NURISK Database & Testing Conventions

This project strictly follows a **Database-First Development** architecture using SQL Frozen v37 as the single source of truth.

## Core Rules

1. **SQL Frozen v37 = Single Source of Truth**
   The production database schema is entirely defined by the SQL Frozen v37 script.

2. **No New Migrations for Existing Tables**
   You MUST NOT create new Laravel migrations (`database/migrations/...`) for any tables that already exist in the SQL Frozen v37 schema. Doing so creates a dangerous parallel source of truth that will eventually lead to schema drift in production.

3. **Testing Schema Setup**
   To support PHPUnit testing with `RefreshDatabase` and SQLite Memory, you MUST create the necessary testing schemas dynamically within the test environment.
   
   **How to do this:**
   Use `Schema::create()` inside the `setUp()` method of your tests or inside a dedicated Trait (e.g., `tests/Support/CreatesRelawanSchema.php`).

   ```php
   // Example in a test or trait
   protected function setUp(): void
   {
       parent::setUp();
       $this->createDomainSchema();
   }

   protected function createDomainSchema(): void
   {
       Schema::disableForeignKeyConstraints();
       
       if (!Schema::hasTable('table_name')) {
           Schema::create('table_name', function (Blueprint $table) {
               // Must exactly match SQL Frozen v37 physical structure
               $table->increments('id');
               // ...
           });
       }
       
       Schema::enableForeignKeyConstraints();
   }
   ```

4. **Future Domains (LOG, ASESMEN, POSKO, BANTUAN, dll)**
   For all future domains, continue following this pattern. Never create a new migration for a table that is already documented in the SQL Frozen v37 schema.
