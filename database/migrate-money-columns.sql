-- Safe money-column migration for an existing FreeHosting MySQL database.
-- Back up the database before running this file in phpMyAdmin.
-- The UPDATE statements normalize existing values before changing the type.

START TRANSACTION;

UPDATE courses SET price = ROUND(price, 6);
UPDATE payments SET amount = ROUND(amount, 2), teacher_share = ROUND(teacher_share, 2);
UPDATE bookings SET fee = ROUND(fee, 2);
UPDATE booking_payments SET amount = ROUND(amount, 2);

ALTER TABLE courses
    MODIFY price DECIMAL(12,6) NOT NULL DEFAULT 0.000000;

ALTER TABLE payments
    MODIFY amount DECIMAL(10,2) NOT NULL,
    MODIFY teacher_share DECIMAL(10,2) NOT NULL DEFAULT 0.00;

ALTER TABLE bookings
    MODIFY fee DECIMAL(10,2) NOT NULL DEFAULT 0.00;

ALTER TABLE booking_payments
    MODIFY amount DECIMAL(10,2) NOT NULL;

COMMIT;
