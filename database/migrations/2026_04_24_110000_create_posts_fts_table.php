<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * SQLite FTS5 index for /search — external-content pattern keyed off
 * posts.id. Tokenizer is unicode61 remove_diacritics=2 so "reforme"
 * still matches "Réforme", "préparé", etc. without editor ceremony.
 *
 * The external-content form means posts_fts stores only the inverted
 * index, never the row text — posts remains the single source of
 * truth. Triggers below keep the two in lockstep.
 */
return new class extends Migration {
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            // FTS5 is SQLite-specific. For MySQL/Postgres we'd use
            // MATCH AGAINST or pg_trgm — out of scope for S75.
            return;
        }

        DB::statement('DROP TABLE IF EXISTS posts_fts;');

        DB::statement("
            CREATE VIRTUAL TABLE posts_fts USING fts5(
                name,
                description,
                content,
                source_name,
                content='posts',
                content_rowid='id',
                tokenize='unicode61 remove_diacritics 2'
            );
        ");

        // Keep FTS index in sync with posts.
        DB::statement('DROP TRIGGER IF EXISTS posts_ai;');
        DB::statement("
            CREATE TRIGGER posts_ai AFTER INSERT ON posts BEGIN
                INSERT INTO posts_fts(rowid, name, description, content, source_name)
                VALUES (new.id, new.name, new.description, new.content, new.source_name);
            END;
        ");

        DB::statement('DROP TRIGGER IF EXISTS posts_ad;');
        DB::statement("
            CREATE TRIGGER posts_ad AFTER DELETE ON posts BEGIN
                INSERT INTO posts_fts(posts_fts, rowid, name, description, content, source_name)
                VALUES ('delete', old.id, old.name, old.description, old.content, old.source_name);
            END;
        ");

        DB::statement('DROP TRIGGER IF EXISTS posts_au;');
        DB::statement("
            CREATE TRIGGER posts_au AFTER UPDATE ON posts BEGIN
                INSERT INTO posts_fts(posts_fts, rowid, name, description, content, source_name)
                VALUES ('delete', old.id, old.name, old.description, old.content, old.source_name);
                INSERT INTO posts_fts(rowid, name, description, content, source_name)
                VALUES (new.id, new.name, new.description, new.content, new.source_name);
            END;
        ");

        // Backfill every post currently in the DB.
        DB::statement("
            INSERT INTO posts_fts(rowid, name, description, content, source_name)
            SELECT id, name, description, content, source_name FROM posts;
        ");
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            return;
        }

        DB::statement('DROP TRIGGER IF EXISTS posts_ai;');
        DB::statement('DROP TRIGGER IF EXISTS posts_ad;');
        DB::statement('DROP TRIGGER IF EXISTS posts_au;');
        DB::statement('DROP TABLE IF EXISTS posts_fts;');
    }
};
