-- ============================================================
--  Migration: add the "page the visitor enquired from" to leads.
--  Import this ONCE in phpMyAdmin (select your database first).
--  Safe if the column does not already exist.
-- ============================================================
ALTER TABLE leads ADD COLUMN page_url VARCHAR(255) NULL AFTER source;
