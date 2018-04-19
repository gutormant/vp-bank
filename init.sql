-- 
-- Установка кодировки, с использованием которой клиент будет посылать запросы на сервер
--
SET NAMES 'utf8';

-- Создание БД
CREATE DATABASE vp_bank
CHARACTER SET utf8
COLLATE utf8_general_ci;

--
-- Установка базы данных по умолчанию
--
USE vp_bank;

--
-- Создать таблицу `operation`
--
CREATE TABLE operation (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'идентификатор операции',
  accountID int(11) NOT NULL COMMENT 'Счет',
  type tinyint(4) NOT NULL COMMENT 'Тип операции: 10 - пополнение, 20 - снятие, 30 - начисление процентов, 40 - комиссия',
  dateOfExecution datetime NOT NULL COMMENT 'Дата и время операции',
  sum decimal(18, 4) NOT NULL COMMENT 'Сумма',
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 126,
AVG_ROW_LENGTH = 131,
CHARACTER SET utf8,
COLLATE utf8_general_ci;

--
-- Создать индекс `IDX_operation_accountID` для объекта типа таблица `operation`
--
ALTER TABLE operation
ADD INDEX IDX_operation_accountID (accountID);

--
-- Создать таблицу `client`
--
CREATE TABLE client (
  id int(11) NOT NULL AUTO_INCREMENT COMMENT 'Идентификационный номер',
  name varchar(255) NOT NULL COMMENT 'Имя',
  surname varchar(255) NOT NULL COMMENT 'Фамилия',
  gender tinyint(4) NOT NULL COMMENT 'Пол: 1 - мужской, 2 - женский',
  dateOfBirth date NOT NULL COMMENT 'Дата рождения',
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 4,
AVG_ROW_LENGTH = 5461,
CHARACTER SET utf8,
COLLATE utf8_general_ci,
COMMENT = 'Клиенты';

--
-- Создать таблицу `account`
--
CREATE TABLE account (
  id int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Номер счета',
  clientID int(11) NOT NULL COMMENT 'Идентификационый номер клиента',
  dateOfCreation datetime NOT NULL COMMENT 'Дата создания счета',
  percent decimal(5, 2) NOT NULL COMMENT 'Годовой процент на депозит',
  PRIMARY KEY (id)
)
ENGINE = INNODB,
AUTO_INCREMENT = 6,
AVG_ROW_LENGTH = 3276,
CHARACTER SET utf8,
COLLATE utf8_general_ci,
COMMENT = 'Счета клиентов';

--
-- Создать индекс `IDX_account_clientID` для объекта типа таблица `account`
--
ALTER TABLE account
ADD INDEX IDX_account_clientID (clientID);

-- Пользователь для работы с БД
CREATE USER 'vpbank'@'localhost' IDENTIFIED BY 'Rgstd83ls';
GRANT ALL PRIVILEGES ON vp_bank.* TO  'vpbank'@'localhost';
FLUSH PRIVILEGES;
