CREATE TABLE IF NOT EXISTS user
(
    `id`       INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `create`   TEXT    NOT NULL,
    `username` TEXT    NOT NULL,
    `password` TEXT    NOT NULL,
    `email`    TEXT    NOT NULL,
    `verify`   TEXT    NOT NULL,
    `github`   TEXT    NOT NULL,
    `gitee`    TEXT    NOT NULL,
    `gitlab`   TEXT    NOT NULL,
    `jihulab`  TEXT    NOT NULL,
    `admin`    TEXT    NOT NULL
);