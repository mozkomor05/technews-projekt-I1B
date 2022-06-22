SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

CREATE DATABASE `technews` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `technews`;

CREATE TABLE `comments`
(
    `comment_id`   bigint(20)                         NOT NULL AUTO_INCREMENT,
    `post_id`      bigint(20)                         NOT NULL,
    `reply`        bigint(20)                        DEFAULT NULL,
    `author_user`  varchar(40) COLLATE utf8_czech_ci DEFAULT NULL,
    `author_name`  varchar(60) COLLATE utf8_czech_ci  NOT NULL,
    `author_email` varchar(254) COLLATE utf8_czech_ci NOT NULL,
    `author_ip`    varchar(45) COLLATE utf8_czech_ci  NOT NULL,
    `content`      text COLLATE utf8_czech_ci         NOT NULL,
    `created`      datetime                           NOT NULL,
    PRIMARY KEY (`comment_id`),
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reply`) REFERENCES `comments` (`comment_id`) ON DELETE CASCADE,
    FOREIGN KEY (`author_user`) REFERENCES `users` (`user_name`) ON DELETE CASCADE
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


CREATE TABLE `karma`
(
    `id`     bigint(20)                        NOT NULL AUTO_INCREMENT,
    `obj_id` bigint(20)                        NOT NULL,
    `type`   varchar(20) COLLATE utf8_czech_ci NOT NULL,
    `value`  tinyint(4)                        NOT NULL,
    `ip`     varchar(45) COLLATE utf8_czech_ci NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`obj_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


CREATE TABLE `posts`
(
    `id`      bigint(20)                         NOT NULL AUTO_INCREMENT,
    `title`   varchar(256) COLLATE utf8_czech_ci NOT NULL,
    `slug`    varchar(256) COLLATE utf8_czech_ci NOT NULL,
    `content` longtext COLLATE utf8_czech_ci     NOT NULL,
    `date`    datetime                           NOT NULL,
    `image`   varchar(256) COLLATE utf8_czech_ci NOT NULL,
    PRIMARY KEY (`id`),
    FULLTEXT KEY `title` (`title`, `content`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


CREATE TABLE `tags`
(
    `id`          int(11)                            NOT NULL AUTO_INCREMENT,
    `name`        varchar(30) COLLATE utf8_czech_ci  NOT NULL,
    `slug`        varchar(30) COLLATE utf8_czech_ci  NOT NULL,
    `description` text COLLATE utf8_czech_ci         NOT NULL,
    `image`       varchar(256) COLLATE utf8_czech_ci NOT NULL,
    PRIMARY KEY (`id`),
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


CREATE TABLE `tags_relationships`
(
    `relationship_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `tag_id`          int(11)    NOT NULL,
    `post_id`         bigint(20) NOT NULL,
    PRIMARY KEY (`relationship_id`),
    FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


CREATE TABLE `users`
(
    `user_name`  varchar(40)  NOT NULL,
    `first_name` varchar(60)  NOT NULL,
    `last_name`  varchar(60)  NOT NULL,
    `email`      varchar(254) NOT NULL,
    `avatar`     varchar(300) DEFAULT NULL,
    `password`   varchar(255) NOT NULL,
    `created`    datetime     NOT NULL,
    `ip`         varchar(45)  NOT NULL,
    PRIMARY KEY (`user_name`),
    UNIQUE KEY `email` (`email`)
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8;