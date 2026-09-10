-- Repair databases created before lessons.id was configured as AUTO_INCREMENT.
-- The zero-ID row must be moved before enabling auto-increment.
SET @next_lesson_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM lessons);
UPDATE lessons SET id = @next_lesson_id WHERE id = 0;
ALTER TABLE lessons MODIFY COLUMN id INT UNSIGNED NOT NULL AUTO_INCREMENT;