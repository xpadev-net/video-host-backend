SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `video_list`
(
    `id`                int(11)                                                       NOT NULL,
    `master_series`     varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci           DEFAULT NULL,
    `series`            varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci  NOT NULL,
    `season`            int(11)                                                                DEFAULT NULL,
    `actual_episode`    int(11)                                                                DEFAULT NULL,
    `episode`           int(11)                                                                DEFAULT NULL,
    `url`               varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
    `title`             varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci          DEFAULT NULL,
    `ep_title`          varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci          DEFAULT NULL,
    `path`              varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci          DEFAULT NULL,
    `size`              bigint(20)                                                             DEFAULT NULL,
    `length`            int(11)                                                                DEFAULT NULL,
    `img`               blob                                                                   DEFAULT NULL,
    `resolution_width`  int(11)                                                                DEFAULT NULL,
    `resolution_height` int(11)                                                                DEFAULT NULL,
    `on_added`          int(11)                                                       NOT NULL DEFAULT 0
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

ALTER TABLE `video_list`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `url` (`url`),
    ADD KEY `series` (`series`);

ALTER TABLE `video_list`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
