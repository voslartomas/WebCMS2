# initial import
INSERT IGNORE INTO `Role` (`id`, `name`) VALUES
(1, 'superadmin');

INSERT IGNORE INTO `User` (`id`, `username`, `email`, `password`, `role_id`, `name`) VALUES
(1, 'admin', 'info@webcook.cz', 'd081fcfd0ce68f005e0d52a17dde168e', 1, 'Webcook');

INSERT IGNORE INTO `Language` (`id`, `name`, `abbr`, `defaultFrontend`, `defaultBackend`) VALUES
(1, 'Čeština', 'cs', 1, 1);

INSERT INTO `Thumbnail` (`id`, `key`, `x`, `y`, `watermark`, `system`) VALUES
(1, 'system', 180, 0, 0, 1);

INSERT INTO `Module` (`id`, `name`, `presenters`, `active`) VALUES
(1, 'Page', 'a:5:{i:0;a:3:{s:4:"name";s:4:"Page";s:8:"frontend";b:1;s:10:"parameters";b:0;}i:1;a:2:{s:4:"name";s:12:"Photogallery";s:8:"frontend";b:0;}i:2;a:2:{s:4:"name";s:12:"Videogallery";s:8:"frontend";b:0;}i:3;a:2:{s:4:"name";s:7:"Contact";s:8:"frontend";b:0;}i:4', 1);


INSERT INTO `Page` (`id`, `parent_id`, `language_id`, `module_id`, `metaTitle`, `metaDescription`, `metaKeywords`, `title`, `description`, `slug`, `lft`, `rgt`, `root`, `lvl`, `created`, `updated`, `moduleName`, `presenter`, `path`, `visible`, `default`, `class`) VALUES
(1, NULL, 1, NULL, NULL, NULL, NULL, 'Main', NULL, 'main', 1, 4, 1, 0, '2013-10-21 12:05:11', '2013-10-21 12:05:11', NULL, '', '', 0, 0, ''),
(2, 1, 1, 1, NULL, NULL, NULL, 'Úvod', NULL, 'uvod', 2, 3, 1, 1, '2013-10-21 12:08:14', '2013-10-21 12:08:15', 'Page', 'Page', 'uvod', 1, 1, '');
