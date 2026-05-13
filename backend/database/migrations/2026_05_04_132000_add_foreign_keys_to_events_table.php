<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteEventsTable(true);
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->foreign('user_id', 'events_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('group_id', 'events_group_id_foreign')
                ->references('id')
                ->on('groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->rebuildSqliteEventsTable(false);
            return;
        }

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign('events_user_id_foreign');
            $table->dropForeign('events_group_id_foreign');
        });
    }

    private function rebuildSqliteEventsTable(bool $withForeignKeys): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');
        DB::transaction(function () use ($withForeignKeys) {
            DB::statement($this->createSqliteEventsTableSql($withForeignKeys));
            DB::statement(<<<'SQL'
                INSERT INTO events_new (
                    id, title, date, time, location, description, priority, color, done,
                    created_at, updated_at, user_id, group_id
                )
                SELECT
                    events.id,
                    events.title,
                    events.date,
                    events.time,
                    events.location,
                    events.description,
                    events.priority,
                    events.color,
                    events.done,
                    events.created_at,
                    events.updated_at,
                    CASE WHEN users.id IS NULL THEN NULL ELSE events.user_id END,
                    CASE WHEN groups.id IS NULL THEN NULL ELSE events.group_id END
                FROM events
                LEFT JOIN users ON users.id = events.user_id
                LEFT JOIN groups ON groups.id = events.group_id
            SQL);
            DB::statement('DROP TABLE events');
            DB::statement('ALTER TABLE events_new RENAME TO events');
        });
        DB::statement('PRAGMA foreign_keys = ON');
    }

    private function createSqliteEventsTableSql(bool $withForeignKeys): string
    {
        $foreignKeys = $withForeignKeys
            ? ',
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL'
            : '';

        return <<<SQL
            CREATE TABLE events_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                title varchar NOT NULL,
                date date NOT NULL,
                time time NULL,
                location varchar NULL,
                description TEXT NULL,
                priority varchar NOT NULL DEFAULT 'medium',
                color varchar NOT NULL DEFAULT '#ec4899',
                done tinyint(1) NOT NULL DEFAULT '0',
                created_at datetime NULL,
                updated_at datetime NULL,
                user_id INTEGER NULL,
                group_id INTEGER NULL
                {$foreignKeys}
            )
        SQL;
    }
};
