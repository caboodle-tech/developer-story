DELETE FROM `user_profile` WHERE `id` IN (SELECT `id` FROM `user` WHERE `email` != 'cdkeers@gmail.com');
DELETE FROM `user_connections` WHERE `id` IN (SELECT `id` FROM `user` WHERE `email` != 'cdkeers@gmail.com');
DELETE FROM `user` WHERE `email` != 'cdkeers@gmail.com';