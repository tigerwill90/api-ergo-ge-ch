-- MySQL Script generated by MySQL Workbench
-- lun 20 mai 2019 13:27:08 CEST
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema pfak_ergo
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `pfak_ergo` ;

-- -----------------------------------------------------
-- Schema pfak_ergo
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `pfak_ergo` DEFAULT CHARACTER SET utf8 ;
USE `pfak_ergo` ;

-- -----------------------------------------------------
-- Table `pfak_ergo`.`offices`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`offices` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`offices` (
  `offices_id` INT NOT NULL AUTO_INCREMENT,
  `offices_name` VARCHAR(45) NOT NULL,
  `offices_email` VARCHAR(250) NOT NULL,
  PRIMARY KEY (`offices_id`),
  UNIQUE INDEX `offices_email_UNIQUE` (`offices_email` ASC),
  UNIQUE INDEX `offices_name_UNIQUE` (`offices_name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`therapists`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`therapists` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`therapists` (
  `therapists_id` INT NOT NULL AUTO_INCREMENT,
  `therapists_title` VARCHAR(10) NOT NULL,
  `therapists_firstname` VARCHAR(45) NOT NULL,
  `therapists_lastname` VARCHAR(45) NOT NULL,
  `therapists_home` TINYINT NOT NULL,
  `therapists_offices_id` INT NOT NULL,
  PRIMARY KEY (`therapists_id`, `therapists_offices_id`),
  INDEX `fk_therapists_offices1_idx` (`therapists_offices_id` ASC),
  CONSTRAINT `fk_therapists_offices1`
    FOREIGN KEY (`therapists_offices_id`)
    REFERENCES `pfak_ergo`.`offices` (`offices_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`phones`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`phones` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`phones` (
  `phones_id` INT NOT NULL AUTO_INCREMENT,
  `phones_type` VARCHAR(25) NOT NULL,
  `phones_number` VARCHAR(45) NOT NULL,
  `phones_therapists_id` INT NOT NULL,
  PRIMARY KEY (`phones_id`, `phones_therapists_id`),
  INDEX `fk_phones_therapist_idx` (`phones_therapists_id` ASC),
  CONSTRAINT `fk_phones_therapist`
    FOREIGN KEY (`phones_therapists_id`)
    REFERENCES `pfak_ergo`.`therapists` (`therapists_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`emails`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`emails` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`emails` (
  `emails_id` INT NOT NULL AUTO_INCREMENT,
  `emails_address` VARCHAR(250) NOT NULL,
  `emails_therapists_id` INT NOT NULL,
  PRIMARY KEY (`emails_id`, `emails_therapists_id`),
  INDEX `fk_emails_therapists1_idx` (`emails_therapists_id` ASC),
  CONSTRAINT `fk_emails_therapists1`
    FOREIGN KEY (`emails_therapists_id`)
    REFERENCES `pfak_ergo`.`therapists` (`therapists_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`categories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`categories` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`categories` (
  `categories_id` INT NOT NULL AUTO_INCREMENT,
  `categories_name` VARCHAR(45) NOT NULL,
  `categories_description` VARCHAR(255) NULL,
  PRIMARY KEY (`categories_id`),
  UNIQUE INDEX `categories_name_UNIQUE` (`categories_name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`therapistsCategories`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`therapistsCategories` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`therapistsCategories` (
  `therapistsCategories_therapists_id` INT NOT NULL,
  `therapistsCategories_categories_id` INT NOT NULL,
  PRIMARY KEY (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`),
  INDEX `fk_therapists_has_categories_categories1_idx` (`therapistsCategories_categories_id` ASC),
  CONSTRAINT `fk_therapists_has_categories_therapists1`
    FOREIGN KEY (`therapistsCategories_therapists_id`)
    REFERENCES `pfak_ergo`.`therapists` (`therapists_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_therapists_has_categories_categories1`
    FOREIGN KEY (`therapistsCategories_categories_id`)
    REFERENCES `pfak_ergo`.`categories` (`categories_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`contacts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`contacts` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`contacts` (
  `contacts_id` INT NOT NULL AUTO_INCREMENT,
  `contacts_street` VARCHAR(80) NOT NULL,
  `contacts_city` VARCHAR(45) NOT NULL,
  `contacts_npa` VARCHAR(10) NOT NULL,
  `contacts_cp` VARCHAR(10) NULL,
  `contacts_phone` VARCHAR(45) NULL,
  `contacts_fax` VARCHAR(45) NULL,
  `contacts_offices_id` INT NOT NULL,
  PRIMARY KEY (`contacts_id`, `contacts_offices_id`),
  INDEX `fk_addresses_offices1_idx` (`contacts_offices_id` ASC),
  CONSTRAINT `fk_addresses_offices1`
    FOREIGN KEY (`contacts_offices_id`)
    REFERENCES `pfak_ergo`.`offices` (`offices_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`users` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`users` (
  `users_id` INT NOT NULL AUTO_INCREMENT,
  `users_email` VARCHAR(250) NOT NULL,
  `users_hashed_password` VARCHAR(255) NOT NULL,
  `users_roles` VARCHAR(75) NOT NULL,
  `users_firstname` VARCHAR(45) NOT NULL,
  `users_lastname` VARCHAR(45) NOT NULL,
  `users_active` TINYINT(1) NOT NULL,
  PRIMARY KEY (`users_id`),
  UNIQUE INDEX `users_email_UNIQUE` (`users_email` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pfak_ergo`.`officesUsers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `pfak_ergo`.`officesUsers` ;

CREATE TABLE IF NOT EXISTS `pfak_ergo`.`officesUsers` (
  `officesUsers_users_id` INT NOT NULL,
  `officesUsers_offices_id` INT NOT NULL,
  PRIMARY KEY (`officesUsers_users_id`, `officesUsers_offices_id`),
  INDEX `fk_users_has_offices_offices1_idx` (`officesUsers_offices_id` ASC),
  INDEX `fk_users_has_offices_users1_idx` (`officesUsers_users_id` ASC),
  CONSTRAINT `fk_users_has_offices_users1`
    FOREIGN KEY (`officesUsers_users_id`)
    REFERENCES `pfak_ergo`.`users` (`users_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_has_offices_offices1`
    FOREIGN KEY (`officesUsers_offices_id`)
    REFERENCES `pfak_ergo`.`offices` (`offices_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`offices`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (1, 'Eget Dictum PC', 'netus@scelerisque.co.uk');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (2, 'Sagittis Semper Consulting', 'molestie.in@ligula.com');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (3, 'Non Institute', 'Suspendisse.aliquet@CurabiturmassaVestibulum.org');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (4, 'Ultricies Dignissim Ltd', 'vel@mi.net');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (5, 'Donec Nibh Quisque Industries', 'ut.dolor.dapibus@justoProinnon.co.uk');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (6, 'Sed Pharetra Incorporated', 'quam.elementum.at@sit.edu');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (7, 'Nisi Industries', 'Curabitur.vel@nequepellentesquemassa.edu');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (8, 'Cum Sociis LLP', 'arcu.vel.quam@netus.edu');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (9, 'A Feugiat Tellus LLP', 'Donec.tempus.lorem@Donec.com');
INSERT INTO `pfak_ergo`.`offices` (`offices_id`, `offices_name`, `offices_email`) VALUES (10, 'Tincidunt Foundation', 'Nam@Nullaeu.edu');

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`therapists`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (1, 'M.', 'Octavius', 'Reyes', 1, 1);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (2, 'Mme.', 'Abraham', 'Reilly', 0, 1);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (3, 'Mme.', 'Kamal', 'West', 1, 2);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (4, 'M.', 'Gil', 'Greer', 1, 3);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (5, 'Dr.', 'Arden', 'Griffith', 0, 3);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (6, 'Dr.', 'Russell', 'House', 1, 3);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (7, 'Mme.', 'Holmes', 'Mclaughlin', 0, 3);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (8, 'Dr.', 'Stuart', 'Higgins', 1, 4);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (9, 'Dr.', 'Oscar', 'Puckett', 0, 4);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (10, 'Dr.', 'Damon', 'Rowe', 1, 5);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (11, 'Mme.', 'Ferdinand', 'Drake', 1, 5);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (12, 'Dr.', 'Nissim', 'Pena', 1, 5);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (13, 'Dr.', 'Bert', 'Hayes', 1, 6);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (14, 'Mme.', 'Samuel', 'Meyer', 0, 6);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (15, 'Mme.', 'Vladimir', 'Gardner', 0, 6);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (16, 'M.', 'Axel', 'Koch', 1, 7);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (17, 'M.', 'Odysseus', 'Bryant', 1, 8);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (18, 'M.', 'Clayton', 'Cardenas', 1, 8);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (19, 'M.', 'Joel', 'Cote', 0, 8);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (20, 'Dr.', 'Arden', 'Velazquez', 1, 8);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (21, 'Mme.', 'Dieter', 'Browning', 1, 9);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (22, 'Dr.', 'Odysseus', 'Blankenship', 0, 9);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (23, 'M.', 'Joel', 'Chang', 0, 9);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (24, 'Dr.', 'Alexander', 'Myers', 1, 9);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (25, 'M.', 'Noah', 'Oconnor', 0, 9);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (26, 'Mme.', 'Nathaniel', 'Mccormick', 0, 10);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (27, 'Dr.', 'Sylvester', 'Mendez', 1, 10);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (28, 'Mme.', 'Kieran', 'Stephens', 0, 10);
INSERT INTO `pfak_ergo`.`therapists` (`therapists_id`, `therapists_title`, `therapists_firstname`, `therapists_lastname`, `therapists_home`, `therapists_offices_id`) VALUES (29, 'Dr.', 'Colt', 'Hays', 1, 10);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`phones`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (1, 'Tel.', '001 911 56 97', 1);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (2, 'Fax.', '079 052 68 19', 1);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (3, 'Tel.', '071 845 01 14', 2);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (4, 'Tel.', '069 579 60 09', 3);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (5, 'Tel.', '074 194 56 58', 4);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (6, 'Pro.', '013 150 15 27', 4);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (7, 'Pro.', '048 448 06 78', 5);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (8, 'Tel.', '035 373 37 67', 5);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (9, 'Pro.', '053 086 27 30', 6);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (10, 'Pro.', '043 212 79 91', 7);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (11, 'Pro.', '043 711 08 82', 8);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (12, 'Pro.', '074 471 33 12', 9);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (13, 'Pro.', '090 399 84 10', 10);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (14, 'Fax.', '005 377 65 95', 11);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (15, 'Tel.', '003 509 60 66', 11);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (16, 'Fax.', '057 604 70 36', 12);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (17, 'Pro.', '084 330 76 89', 12);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (18, 'Tel.', '040 153 24 98', 12);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (19, 'Fax.', '039 647 20 17', 13);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (20, 'Fax.', '005 676 65 51', 14);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (21, 'Fax.', '008 413 61 27', 15);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (22, 'Fax.', '077 249 86 25', 16);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (23, 'Pro.', '046 055 99 81', 17);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (24, 'Pro.', '076 633 20 58', 18);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (25, 'Fax.', '036 567 99 99', 19);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (26, 'Pro.', '060 177 33 27', 19);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (27, 'Tel.', '070 203 98 45', 20);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (28, 'Tel.', '051 257 29 64', 21);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (29, 'Tel.', '065 485 46 53', 22);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (30, 'Pro.', '018 319 46 09', 22);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (31, 'Fax.', '067 275 10 38', 22);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (32, 'Tel.', '089 571 55 65', 23);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (33, 'Tel.', '072 025 84 46', 24);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (34, 'Pro.', '019 786 68 85', 25);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (35, 'Fax.', '063 648 17 32', 25);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (36, 'Fax.', '000 248 27 06', 26);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (37, 'Fax.', '080 501 34 37', 27);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (38, 'Fax.', '061 040 23 20', 28);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (39, 'Fax.', '007 752 53 58', 29);
INSERT INTO `pfak_ergo`.`phones` (`phones_id`, `phones_type`, `phones_number`, `phones_therapists_id`) VALUES (40, 'Tel.', '055 266 80 74', 29);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`emails`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (1, 'elit.pharetra@parturientmontes.org', 1);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (2, 'Morbi.quis@magnaa.edu', 2);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (3, 'dui.Suspendisse@Aenean.org', 2);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (4, 'nulla@morbi.com', 3);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (5, 'non@arcu.com', 4);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (6, 'congue.a.aliquet@tempor.net', 5);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (7, 'Sed.congue@Nullamvitaediam.ca', 6);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (8, 'egestas.a@Ut.edu', 7);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (9, 'feugiat@penatibus.com', 8);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (10, 'nunc@malesuadavel.net', 8);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (11, 'eu.dolor@purus.com', 9);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (12, 'nisl@risusodio.org', 9);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (13, 'quam.Pellentesque.habitant@insodales.org', 9);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (14, 'mattis.ornare.lectus@posuerecubiliaCurae.ca', 10);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (15, 'sollicitudin.a.malesuada@arcuVivamus.org', 11);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (16, 'Etiam.gravida@ligulaelit.com', 12);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (17, 'imperdiet.erat.nonummy@dui.org', 13);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (18, 'egestas.Aliquam@Cras.net', 14);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (19, 'Fusce.feugiat@velnislQuisque.co.uk', 15);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (20, 'Nunc.lectus@urnanec.org', 16);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (21, 'dictum.Phasellus.in@enimmitempor.com', 16);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (22, 'ac.eleifend@augueidante.net', 17);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (23, 'a@nonenim.net', 18);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (24, 'mollis.vitae.posuere@montesnascetur.com', 19);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (25, 'dignissim.tempor.arcu@fringillaest.org', 20);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (26, 'natoque@cursusvestibulum.co.uk', 20);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (27, 'nisi@cursus.com', 20);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (28, 'auctor.quis.tristique@tinciduntaliquam.edu', 21);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (29, 'penatibus.et.magnis@turpisnecmauris.co.uk', 21);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (30, 'cursus.et.magna@posuerevulputatelacus.edu', 22);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (31, 'ac.facilisis.facilisis@placerat.edu', 23);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (32, 'tempor.arcu.Vestibulum@augueSedmolestie.net', 24);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (33, 'scelerisque.sed@sollicitudinorcisem.net', 25);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (34, 'sem.vitae.aliquam@duiquisaccumsan.edu', 26);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (35, 'sit@ametconsectetueradipiscing.edu', 27);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (36, 'vel.est.tempor@acsemut.com', 27);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (37, 'justo@vehicula.org', 28);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (38, 'molestie@egestasa.net', 29);
INSERT INTO `pfak_ergo`.`emails` (`emails_id`, `emails_address`, `emails_therapists_id`) VALUES (39, 'nec@blanditatnisi.ca', 29);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`categories`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`categories` (`categories_id`, `categories_name`, `categories_description`) VALUES (1, 'Pédiatrie', NULL);
INSERT INTO `pfak_ergo`.`categories` (`categories_id`, `categories_name`, `categories_description`) VALUES (2, 'Gériatrie', NULL);
INSERT INTO `pfak_ergo`.`categories` (`categories_id`, `categories_name`, `categories_description`) VALUES (3, 'Pathologie membre supérieur', NULL);
INSERT INTO `pfak_ergo`.`categories` (`categories_id`, `categories_name`, `categories_description`) VALUES (4, 'Médecine physique', NULL);
INSERT INTO `pfak_ergo`.`categories` (`categories_id`, `categories_name`, `categories_description`) VALUES (5, 'Psychiatrie', NULL);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`therapistsCategories`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (1, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (1, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (1, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (2, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (2, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (3, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (3, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (4, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (4, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (4, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (5, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (6, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (6, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (7, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (8, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (9, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (10, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (11, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (12, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (13, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (14, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (15, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (15, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (15, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (15, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (16, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (16, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (17, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (18, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (19, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (20, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (21, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (22, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (22, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (23, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (23, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (24, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (25, 1);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (26, 4);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (26, 5);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (27, 3);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (28, 2);
INSERT INTO `pfak_ergo`.`therapistsCategories` (`therapistsCategories_therapists_id`, `therapistsCategories_categories_id`) VALUES (29, 1);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`contacts`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (1, '837-652 Urna Road', 'Casacalenda', '30896', '3677', '022 662 83 28', '022 529 99 66', 1);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (2, '880-5765 Massa St.', 'Springfield', '84208', '8896', '022 805 11 29', '022 864 06 39', 2);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (3, '311-5516 Lorem. St.', 'Los Ángeles', '66760', '7750', '022 909 55 27', '022 536 85 71', 2);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (4, '6595 Tincidunt Av.', 'Mol', '88895', '8012', '022 698 07 17', '022 512 61 45', 3);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (5, 'Ap #604-6989 Varius St.', 'Fratta Todina', '09656', '7977', '022 241 75 46', '022 965 61 81', 4);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (6, '6682 Duis Avenue', 'Hof', '09107', '7599', '022 431 12 69', '022 787 58 93', 5);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (7, 'P.O. Box 676, 3631 At Av.', 'Rutland', '72349', '3325', '022 770 13 76', '022 884 32 35', 6);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (8, 'P.O. Box 231, 6348 Fringilla, Av.', 'Gbongan', '61499', '5221', '022 815 17 29', '022 896 82 64', 7);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (9, 'Ap #656-8805 Vitae Road', 'Bridgeport', '24051', '2984', '022 209 45 80', '022 775 59 52', 7);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (10, '4362 Pellentesque Road', 'Pratovecchio', '34806', '4541', '022 289 78 10', '022 557 45 95', 8);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (11, 'P.O. Box 641, 3179 Quisque Rd.', 'Gargazzone/Gargazon', '33906', '5396', '022 554 96 98', '022 736 51 59', 9);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (12, 'Ap #221-6119 Ut Rd.', 'Antakya', '29183', '6751', '022 794 80 07', '022 114 18 40', 9);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (13, '2374 Ut Road', 'Bearberry', '73475', '7410', '022 515 51 40', '022 351 64 19', 9);
INSERT INTO `pfak_ergo`.`contacts` (`contacts_id`, `contacts_street`, `contacts_city`, `contacts_npa`, `contacts_cp`, `contacts_phone`, `contacts_fax`, `contacts_offices_id`) VALUES (14, '559-1438 Rutrum Street', 'Castelseprio', '79915', '5245', '022 753 33 72', '022 126 58 51', 10);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`users`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`users` (`users_id`, `users_email`, `users_hashed_password`, `users_roles`, `users_firstname`, `users_lastname`, `users_active`) VALUES (1, 'sylvain.muller90@gmail.com', '$2y$10$tLVhvEYmCphDjCkGSJ52AeM07Lvy9PukdQJieS1OCZK7E3vjVEPnq', 'admin', 'Sylvain', 'Muller', 0);

COMMIT;


-- -----------------------------------------------------
-- Data for table `pfak_ergo`.`officesUsers`
-- -----------------------------------------------------
START TRANSACTION;
USE `pfak_ergo`;
INSERT INTO `pfak_ergo`.`officesUsers` (`officesUsers_users_id`, `officesUsers_offices_id`) VALUES (1, 1);
INSERT INTO `pfak_ergo`.`officesUsers` (`officesUsers_users_id`, `officesUsers_offices_id`) VALUES (1, 2);

COMMIT;

