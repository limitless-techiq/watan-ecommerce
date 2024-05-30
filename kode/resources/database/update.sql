
ALTER TABLE `general_settings` ADD `otp_configuration` LONGTEXT NULL AFTER `open_ai_setting`;
ALTER TABLE `users` ADD `otp_code` MEDIUMINT NULL AFTER `google_id`;
