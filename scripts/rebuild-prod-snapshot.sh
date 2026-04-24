#!/usr/bin/env bash
set -euo pipefail

# Regenerate database/prod-snapshot.sql from the current local SQLite DB.
#
# Strips drafts, rss_feed_items, sessions, cache, jobs, tokens, audit
# logs, and any slug rows that point at deleted posts — keeps schema +
# the data a fresh prod install needs (news_sources, rss_feeds,
# story_clusters, settings, admin user, published posts and their
# slugs/categories).
#
# Run when:
#   - you added a migration that altered a Botble plugin table
#   - you changed seed data that prod needs on first install
#   - admin user password / license bypass rows changed locally
#
# The output file is committed; the SQLite binary is not.

SRC="database/grimbanews.sqlite"
OUT="database/prod-snapshot.sql"
TMP="/tmp/grimba-snapshot-filtered-$$.sqlite"

if [ ! -f "$SRC" ]; then
    echo "ERROR: $SRC not found — run from project root"
    exit 1
fi

echo "=== Cloning local DB to scratch ==="
rm -f "$TMP"
sqlite3 "$SRC" ".dump" | sqlite3 "$TMP"

echo "=== Stripping ephemeral + draft data ==="
sqlite3 "$TMP" "
DELETE FROM rss_feed_items;
DELETE FROM posts WHERE status = 'draft';
DELETE FROM slugs
  WHERE reference_type = 'Botble\Blog\Models\Post'
  AND reference_id NOT IN (SELECT id FROM posts);
DELETE FROM post_categories WHERE post_id NOT IN (SELECT id FROM posts);
DELETE FROM post_tags WHERE post_id NOT IN (SELECT id FROM posts);
DELETE FROM sessions;
DELETE FROM cache;
DELETE FROM cache_locks;
DELETE FROM jobs;
DELETE FROM failed_jobs;
DELETE FROM password_reset_tokens;
DELETE FROM personal_access_tokens;
DELETE FROM newsletter_subscriptions;
DELETE FROM audit_histories;
DELETE FROM request_logs;
DELETE FROM contacts;
VACUUM;
"

echo "=== Counts that will ship ==="
sqlite3 "$TMP" "
SELECT 'posts:          ' || COUNT(*) FROM posts;
SELECT 'slugs:          ' || COUNT(*) FROM slugs;
SELECT 'news_sources:   ' || COUNT(*) FROM news_sources;
SELECT 'rss_feeds:      ' || COUNT(*) FROM rss_feeds;
SELECT 'story_clusters: ' || COUNT(*) FROM story_clusters;
SELECT 'users:          ' || COUNT(*) FROM users;
SELECT 'settings:       ' || COUNT(*) FROM settings;
"

echo ""
echo "=== Writing $OUT ==="
sqlite3 "$TMP" ".dump" > "$OUT"
rm -f "$TMP"
ls -lh "$OUT"

echo ""
echo "Commit with:"
echo "  git add $OUT && git commit -m 'chore: refresh prod-snapshot.sql'"
