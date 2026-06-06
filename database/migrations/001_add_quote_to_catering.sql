-- Migration: Add dedicated `quote` column to catering_requests
-- Run this against your MySQL database for the project.
-- Backup your DB first.

ALTER TABLE catering_requests
  ADD COLUMN `quote` TEXT NULL DEFAULT NULL AFTER `notes`,
  ADD COLUMN `quote_sent_at` DATETIME NULL DEFAULT NULL AFTER `quote`;

-- Optional: if you want to populate `quote` from existing notes where notes contain a QUOTE block,
-- you can run a manual update. This example assumes old quotes were appended with a line like 'QUOTE:'
-- and keeps the rest of the notes intact.
--
-- UPDATE catering_requests
-- SET quote = SUBSTRING_INDEX(notes, 'QUOTE:', -1),
--     notes = TRIM(SUBSTRING_INDEX(notes, 'QUOTE:', 1))
-- WHERE notes LIKE '%QUOTE:%';
