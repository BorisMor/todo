-- 
-- Отключение внешних ключей
-- 
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

-- 
-- Установить режим SQL (SQL mode)
-- 
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 
-- Установка кодировки, с использованием которой клиент будет посылать запросы на сервер
--
SET NAMES 'utf8';

-- 
-- Установка базы данных по умолчанию
--
USE todo;

--
-- Описание для таблицы todo_items
--
DROP TABLE IF EXISTS todo_items;
CREATE TABLE todo_items (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL COMMENT 'Кто создал',
  title VARCHAR(255) NOT NULL,
  completed TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  CONSTRAINT FK_todo_items_user_id FOREIGN KEY (user_id)
    REFERENCES users(id) ON DELETE NO ACTION ON UPDATE RESTRICT
)
ENGINE = INNODB
AUTO_INCREMENT = 13
AVG_ROW_LENGTH = 5461
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Описание для таблицы users
--
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  login VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  token VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE INDEX UK_users_login (login),
  UNIQUE INDEX UK_users_token (token)
)
ENGINE = INNODB
AUTO_INCREMENT = 34
AVG_ROW_LENGTH = 16384
CHARACTER SET utf8
COLLATE utf8_general_ci
COMMENT = 'Пользователь';

-- 
-- Вывод данных для таблицы todo_items
--
INSERT INTO todo_items VALUES
(2, 1, 'test1', 0),
(3, 1, 'test2', 0),
(4, 1, 'test3', 0);

-- 
-- Вывод данных для таблицы users
--
INSERT INTO users VALUES
(1, 'test', 'test', NULL);

-- 
-- Восстановить предыдущий режим SQL (SQL mode)
-- 
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

-- 
-- Включение внешних ключей
-- 
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;