CREATE TABLE IF NOT EXISTS "data"
(
    "id"     INTEGER PRIMARY KEY AUTOINCREMENT,
    "title"  TEXT NOT NULL,
    "answer" TEXT NOT NULL,
    "work"   TEXT NOT NULL,
    "course" TEXT NOT NULL,
    "create" TEXT NOT NULL,
    "update" TEXT NOT NULL
);