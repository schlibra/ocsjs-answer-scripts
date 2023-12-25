CREATE TABLE IF NOT EXISTS `system`
(
    `id`             INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
    `gitee_login`    TEXT    NOT NULL,
    `gitee_id`       TEXT    NOT NULL,
    `gitee_secret`   TEXT    NOT NULL,
    `github_login`   TEXT    NOT NULL,
    `github_id`      TEXT    NOT NULL,
    `github_secret`  TEXT    NOT NULL,
    `gitlab_login`   TEXT    NOT NULL,
    `gitlab_id`      TEXT    NOT NULL,
    `gitlab_secret`  TEXT    NOT NULL,
    `jihulab_login`  TEXT    NOT NULL,
    `jihulab_id`     TEXT    NOT NULL,
    `jihulab_secret` TEXT    NOT NULL
);