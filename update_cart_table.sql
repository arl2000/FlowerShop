-- Remove the foreign key constraint
ALTER TABLE cart DROP FOREIGN KEY cart_ibfk_2;

-- Add is_customized column if it doesn't exist
ALTER TABLE cart ADD COLUMN is_customized TINYINT(1) DEFAULT 0 AFTER product_id;

-- Add columns for customization details
ALTER TABLE cart ADD COLUMN ribbon_color_id INT AFTER product_image;
ALTER TABLE cart ADD COLUMN ribbon_color_name VARCHAR(255) AFTER ribbon_color_id;
ALTER TABLE cart ADD COLUMN ribbon_color_price DECIMAL(10,2) AFTER ribbon_color_name;
ALTER TABLE cart ADD COLUMN wrapper_color_id INT AFTER ribbon_color_price;
ALTER TABLE cart ADD COLUMN wrapper_color_name VARCHAR(255) AFTER wrapper_color_id;
ALTER TABLE cart ADD COLUMN wrapper_color_price DECIMAL(10,2) AFTER wrapper_color_name;
ALTER TABLE cart ADD COLUMN customer_message TEXT AFTER wrapper_color_price;
ALTER TABLE cart ADD COLUMN addons TEXT AFTER customer_message; 