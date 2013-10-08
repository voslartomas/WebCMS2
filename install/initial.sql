# initial import
INSERT IGNORE INTO `Role` (`id`, `name`) VALUES
(1, 'superadmin');

INSERT IGNORE INTO `User` (`id`, `username`, `email`, `password`, `role_id`, `name`) VALUES
(1, 'admin', 'info@webcook.cz', 'd081fcfd0ce68f005e0d52a17dde168e', 1, 'Webcook');

INSERT IGNORE INTO `Language` (`id`, `name`, `abbr`, `defaultFrontend`, `defaultBackend`) VALUES
(1, 'Čeština', 'cs', 1, 1);

INSERT INTO `thumbnail` (`id`, `key`, `x`, `y`, `watermark`, `system`) VALUES
(1, 'system', 180, 0, 0, 1);