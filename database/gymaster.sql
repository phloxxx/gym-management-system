-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 07:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gymaster`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetActiveMembersCount` ()   BEGIN
    SELECT COUNT(*) as count FROM member WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetActiveProgramsCount` ()   BEGIN
    SELECT COUNT(*) as count FROM program WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMembershipGrowthData` ()   BEGIN
    SELECT MONTH(JOINED_DATE) as month, COUNT(*) as count 
    FROM member 
    WHERE JOINED_DATE >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
    GROUP BY MONTH(JOINED_DATE)
    ORDER BY JOINED_DATE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMonthlyRevenue` ()   BEGIN
    SELECT SUM(s.PRICE) as revenue 
    FROM transaction t 
    JOIN subscription s ON t.SUB_ID = s.SUB_ID 
    WHERE MONTH(t.TRANSAC_DATE) = MONTH(CURRENT_DATE()) 
    AND YEAR(t.TRANSAC_DATE) = YEAR(CURRENT_DATE());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecentMembers` ()   BEGIN
    SELECT m.MEMBER_FNAME, m.MEMBER_LNAME, m.EMAIL, p.PROGRAM_NAME, 
           m.IS_ACTIVE, m.JOINED_DATE
    FROM member m
    LEFT JOIN program p ON m.PROGRAM_ID = p.PROGRAM_ID
    ORDER BY m.JOINED_DATE DESC 
    LIMIT 3;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRecentTransactions` ()   BEGIN
    SELECT t.TRANSACTION_ID, m.MEMBER_FNAME, m.MEMBER_LNAME,
           s.SUB_NAME, s.PRICE, t.TRANSAC_DATE, p.PAY_METHOD
    FROM transaction t
    JOIN member m ON t.MEMBER_ID = m.MEMBER_ID
    JOIN subscription s ON t.SUB_ID = s.SUB_ID
    JOIN payment p ON t.PAYMENT_ID = p.PAYMENT_ID
    ORDER BY t.TRANSAC_DATE DESC
    LIMIT 3;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStaffCount` ()   BEGIN
    SELECT COUNT(*) as count FROM user WHERE USER_TYPE = 'STAFF' AND IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetSubscriptionDistribution` ()   BEGIN
    SELECT s.SUB_NAME, COUNT(ms.MEMBER_ID) as count
    FROM subscription s
    LEFT JOIN member_subscription ms ON s.SUB_ID = ms.SUB_ID
    WHERE s.IS_ACTIVE = 1 AND (ms.IS_ACTIVE = 1 OR ms.IS_ACTIVE IS NULL)
    GROUP BY s.SUB_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddCoach` (IN `p_fname` VARCHAR(255), IN `p_lname` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_phone` VARCHAR(20), IN `p_gender` VARCHAR(10), IN `p_is_active` TINYINT)   BEGIN
    INSERT INTO coach (COACH_FNAME, COACH_LNAME, EMAIL, PHONE_NUMBER, GENDER, IS_ACTIVE)
    VALUES (p_fname, p_lname, p_email, p_phone, p_gender, p_is_active);
    SELECT LAST_INSERT_ID() as COACH_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddCoachProgram` (IN `p_coach_id` INT, IN `p_program_id` INT)   BEGIN
    INSERT INTO program_coach (PROGRAM_ID, COACH_ID)
    VALUES (p_program_id, p_coach_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddProgram` (IN `p_program_name` VARCHAR(255), IN `p_is_active` TINYINT)   BEGIN
    DECLARE EXIT HANDLER FOR 1062 
    BEGIN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Program name already exists';
    END;
    
    INSERT INTO program (PROGRAM_NAME, IS_ACTIVE) 
    VALUES (p_program_name, p_is_active);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AddSubscription` (IN `p_sub_name` VARCHAR(255), IN `p_duration` INT, IN `p_price` DECIMAL(10,2), IN `p_is_active` BOOLEAN)   BEGIN
    INSERT INTO subscription (SUB_NAME, DURATION, PRICE, IS_ACTIVE)
    VALUES (p_sub_name, p_duration, p_price, p_is_active);
    SELECT LAST_INSERT_ID() as SUB_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_add_user` (IN `p_fname` VARCHAR(255), IN `p_lname` VARCHAR(255), IN `p_username` VARCHAR(255), IN `p_password` VARCHAR(255), IN `p_user_type` ENUM('ADMINISTRATOR','STAFF'), IN `p_is_active` TINYINT)   BEGIN
    INSERT INTO user (USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE, IS_ACTIVE)
    VALUES (p_fname, p_lname, p_username, p_password, p_user_type, p_is_active);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CheckComorbidityExists` (IN `p_name` VARCHAR(255))   BEGIN
    SELECT COMOR_ID 
    FROM comorbidities 
    WHERE COMOR_NAME = p_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_CheckComorbidityExistsForUpdate` (IN `p_name` VARCHAR(255), IN `p_id` INT)   BEGIN
    SELECT COMOR_ID 
    FROM comorbidities 
    WHERE COMOR_NAME = p_name 
    AND COMOR_ID != p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_check_username` (IN `p_username` VARCHAR(255))   BEGIN
    SELECT USER_ID FROM user WHERE USERNAME = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_payment_method` (IN `p_pay_method` VARCHAR(20), IN `p_is_active` TINYINT)   BEGIN
    INSERT INTO payment (PAY_METHOD, IS_ACTIVE)
    VALUES (p_pay_method, p_is_active);
    
    SELECT LAST_INSERT_ID() as payment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteCoach` (IN `p_coach_id` INT)   BEGIN
    DELETE FROM coach WHERE COACH_ID = p_coach_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteCoachPrograms` (IN `p_coach_id` INT)   BEGIN
    DELETE FROM program_coach WHERE COACH_ID = p_coach_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteComorbidity` (IN `p_id` INT)   BEGIN
    DELETE FROM comorbidities 
    WHERE COMOR_ID = p_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteProgram` (IN `p_program_id` INT)   BEGIN
    DELETE FROM program WHERE PROGRAM_ID = p_program_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DeleteSubscription` (IN `p_sub_id` INT)   BEGIN
    DELETE FROM subscription WHERE SUB_ID = p_sub_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_delete_payment_method` (IN `p_payment_id` INT)   BEGIN
    DELETE FROM payment 
    WHERE PAYMENT_ID = p_payment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EditCoach` (IN `p_coach_id` INT, IN `p_fname` VARCHAR(255), IN `p_lname` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_phone` VARCHAR(20), IN `p_gender` VARCHAR(10), IN `p_is_active` TINYINT)   BEGIN
    UPDATE coach 
    SET COACH_FNAME = p_fname,
        COACH_LNAME = p_lname,
        EMAIL = p_email,
        PHONE_NUMBER = p_phone,
        GENDER = p_gender,
        IS_ACTIVE = p_is_active
    WHERE COACH_ID = p_coach_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_EditProgram` (IN `p_program_id` INT, IN `p_program_name` VARCHAR(255), IN `p_is_active` TINYINT)   BEGIN
    UPDATE program 
    SET PROGRAM_NAME = p_program_name,
        IS_ACTIVE = p_is_active 
    WHERE PROGRAM_ID = p_program_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllCoachesWithPrograms` ()   BEGIN
    SELECT c.*, 
           GROUP_CONCAT(p.PROGRAM_NAME) as PROGRAM_NAMES,
           GROUP_CONCAT(p.PROGRAM_ID) as PROGRAM_IDS
    FROM coach c 
    LEFT JOIN program_coach pc ON c.COACH_ID = pc.COACH_ID 
    LEFT JOIN program p ON pc.PROGRAM_ID = p.PROGRAM_ID 
    GROUP BY c.COACH_ID;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllComorbidities` ()   BEGIN
    SELECT COMOR_ID, COMOR_NAME, IS_ACTIVE 
    FROM comorbidities 
    ORDER BY COMOR_NAME ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllPrograms` ()   BEGIN
    SELECT * FROM program ORDER BY PROGRAM_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllSubscriptions` ()   BEGIN
    SELECT * FROM subscription ORDER BY SUB_ID DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllUsers` ()   BEGIN
    SELECT 
        u.USER_ID,
        u.USER_FNAME,
        u.USER_LNAME,
        u.USERNAME,
        u.USER_TYPE,
        u.IS_ACTIVE
    FROM user u
    ORDER BY u.USER_ID DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_getForgotUser` ()   BEGIN
    SELECT USER_ID, USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE
    FROM `USER`
    WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetSubscriptionById` (IN `p_sub_id` INT)   BEGIN
    SELECT * FROM subscription WHERE SUB_ID = p_sub_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetUsers` ()   BEGIN
    SELECT USER_ID, USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE
    FROM `USER`
    WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_payment_methods` ()   BEGIN
    SELECT PAYMENT_ID, PAY_METHOD, IS_ACTIVE 
    FROM payment 
    ORDER BY PAY_METHOD ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_payment_method_by_id` (IN `p_payment_id` INT)   BEGIN
    SELECT PAYMENT_ID, PAY_METHOD, IS_ACTIVE 
    FROM payment 
    WHERE PAYMENT_ID = p_payment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_ToggleSubscriptionStatus` (IN `p_sub_id` INT)   BEGIN
    UPDATE subscription 
    SET IS_ACTIVE = NOT IS_ACTIVE 
    WHERE SUB_ID = p_sub_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpdateSubscription` (IN `p_sub_id` INT, IN `p_sub_name` VARCHAR(255), IN `p_duration` INT, IN `p_price` DECIMAL(10,2), IN `p_is_active` BOOLEAN)   BEGIN
    UPDATE subscription 
    SET SUB_NAME = p_sub_name,
        DURATION = p_duration,
        PRICE = p_price,
        IS_ACTIVE = p_is_active
    WHERE SUB_ID = p_sub_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_payment_method` (IN `p_payment_id` INT, IN `p_pay_method` VARCHAR(20), IN `p_is_active` TINYINT)   BEGIN
    UPDATE payment 
    SET PAY_METHOD = p_pay_method,
        IS_ACTIVE = p_is_active
    WHERE PAYMENT_ID = p_payment_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertComorbidity` (IN `p_id` INT, IN `p_name` VARCHAR(255), IN `p_is_active` TINYINT)   BEGIN
    IF p_id IS NULL THEN
        INSERT INTO comorbidities (COMOR_NAME, IS_ACTIVE) 
        VALUES (p_name, p_is_active);
    ELSE
        UPDATE comorbidities 
        SET COMOR_NAME = p_name, 
            IS_ACTIVE = p_is_active 
        WHERE COMOR_ID = p_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertUser` (IN `p_user_id` SMALLINT, IN `p_fname` VARCHAR(50), IN `p_lname` VARCHAR(30), IN `p_username` VARCHAR(20), IN `p_password` VARCHAR(255), IN `p_user_type` ENUM('ADMINISTRATOR','STAFF'), IN `p_is_active` TINYINT(1))   BEGIN
    IF p_user_id IS NULL THEN
        INSERT INTO `USER` (USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE, IS_ACTIVE)
        VALUES (p_fname, p_lname, p_username, p_password, p_user_type, COALESCE(p_is_active, 1));
    ELSE
        UPDATE `USER`
        SET USER_FNAME = p_fname,
            USER_LNAME = p_lname,
            USERNAME = p_username,
            PASSWORD = p_password,
            USER_TYPE = p_user_type,
            IS_ACTIVE = COALESCE(p_is_active, IS_ACTIVE)
        WHERE USER_ID = p_user_id;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `coach`
--

CREATE TABLE `coach` (
  `COACH_ID` smallint(6) NOT NULL,
  `COACH_FNAME` varchar(50) NOT NULL,
  `COACH_LNAME` varchar(30) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE_NUMBER` varchar(15) NOT NULL,
  `GENDER` enum('MALE','FEMALE','OTHER') NOT NULL,
  `SPECIALIZATION` varchar(550) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coach`
--

INSERT INTO `coach` (`COACH_ID`, `COACH_FNAME`, `COACH_LNAME`, `EMAIL`, `PHONE_NUMBER`, `GENDER`, `SPECIALIZATION`, `IS_ACTIVE`) VALUES
(8, 'jeff', 'monreal', 'jeff@gmail.com', '0912312312312', 'MALE', '', 1),
(9, 'Nino', 'Ocliasa', 'ninzo@gmail.com', '2342412422', 'MALE', 'Strength Training,Padako', 1);

-- --------------------------------------------------------

--
-- Table structure for table `comorbidities`
--

CREATE TABLE `comorbidities` (
  `COMOR_ID` smallint(6) NOT NULL,
  `COMOR_NAME` varchar(50) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comorbidities`
--

INSERT INTO `comorbidities` (`COMOR_ID`, `COMOR_NAME`, `IS_ACTIVE`) VALUES
(2, 'Arthritis', 1),
(4, 'Cardiac Arrest', 1),
(6, 'Copia', 1);

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `MEMBER_FNAME` varchar(50) NOT NULL,
  `MEMBER_LNAME` varchar(30) NOT NULL,
  `EMAIL` varchar(50) NOT NULL,
  `PHONE_NUMBER` varchar(15) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1,
  `PROGRAM_ID` smallint(6) NOT NULL,
  `USER_ID` smallint(6) NOT NULL,
  `JOINED_DATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_comorbidities`
--

CREATE TABLE `member_comorbidities` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `COMOR_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `member_subscription`
--

CREATE TABLE `member_subscription` (
  `MEMBER_ID` smallint(6) NOT NULL,
  `SUB_ID` smallint(6) NOT NULL,
  `START_DATE` date NOT NULL DEFAULT curdate(),
  `END_DATE` date NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` smallint(6) NOT NULL,
  `PAY_METHOD` varchar(20) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PAYMENT_ID`, `PAY_METHOD`, `IS_ACTIVE`) VALUES
(8, 'Credit Card', 1),
(9, 'Bank Transfer', 1),
(10, 'BDO', 1),
(11, 'E-Wallet', 1);

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `PROGRAM_ID` smallint(6) NOT NULL,
  `PROGRAM_NAME` varchar(50) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program`
--

INSERT INTO `program` (`PROGRAM_ID`, `PROGRAM_NAME`, `IS_ACTIVE`) VALUES
(5, 'Strength Training', 1),
(16, 'Padako', 1);

-- --------------------------------------------------------

--
-- Table structure for table `program_coach`
--

CREATE TABLE `program_coach` (
  `PROGRAM_ID` smallint(6) NOT NULL,
  `COACH_ID` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_coach`
--

INSERT INTO `program_coach` (`PROGRAM_ID`, `COACH_ID`) VALUES
(5, 8),
(5, 9),
(16, 9);

-- --------------------------------------------------------

--
-- Table structure for table `subscription`
--

CREATE TABLE `subscription` (
  `SUB_ID` smallint(6) NOT NULL,
  `SUB_NAME` varchar(20) NOT NULL,
  `DURATION` varchar(10) NOT NULL,
  `PRICE` decimal(10,2) NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscription`
--

INSERT INTO `subscription` (`SUB_ID`, `SUB_NAME`, `DURATION`, `PRICE`, `IS_ACTIVE`) VALUES
(5, 'Annually', '365', 5999.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `TRANSACTION_ID` smallint(6) NOT NULL,
  `MEMBER_ID` smallint(6) NOT NULL,
  `SUB_ID` smallint(6) NOT NULL,
  `PAYMENT_ID` smallint(6) NOT NULL,
  `TRANSAC_DATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_log`
--

CREATE TABLE `transaction_log` (
  `LOG_ID` smallint(6) NOT NULL,
  `TRANSACTION_ID` int(11) NOT NULL,
  `OPERATION` varchar(15) NOT NULL,
  `MODIFIEDDATE` date NOT NULL DEFAULT curdate()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` smallint(6) NOT NULL,
  `USER_FNAME` varchar(50) NOT NULL,
  `USER_LNAME` varchar(30) NOT NULL,
  `USERNAME` varchar(20) NOT NULL,
  `PASSWORD` varchar(255) NOT NULL,
  `USER_TYPE` enum('ADMINISTRATOR','STAFF') NOT NULL,
  `IS_ACTIVE` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USER_FNAME`, `USER_LNAME`, `USERNAME`, `PASSWORD`, `USER_TYPE`, `IS_ACTIVE`) VALUES
(31, 'ninz', 'qweqwe', 'qweqwe', '$2y$10$USckYH.KDPe1A.yZ3RJEIOzHvkGH5WM6uUGLgWAu8evUTxacU/hvO', 'STAFF', 1),
(41, 'TRALALELO', 'TRALALA', 'asdasd', '$2y$10$PyRS12IiIzPWFGa6PEWhROaaXtWtrXycAbI2LR.WngnPh.AsRBZue', 'ADMINISTRATOR', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coach`
--
ALTER TABLE `coach`
  ADD PRIMARY KEY (`COACH_ID`),
  ADD KEY `IDX_COACH` (`COACH_FNAME`,`COACH_LNAME`);

--
-- Indexes for table `comorbidities`
--
ALTER TABLE `comorbidities`
  ADD PRIMARY KEY (`COMOR_ID`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`MEMBER_ID`),
  ADD KEY `PROGRAM_ID` (`PROGRAM_ID`),
  ADD KEY `USER_ID` (`USER_ID`),
  ADD KEY `IDX_MEM` (`MEMBER_FNAME`,`MEMBER_LNAME`);

--
-- Indexes for table `member_comorbidities`
--
ALTER TABLE `member_comorbidities`
  ADD PRIMARY KEY (`MEMBER_ID`,`COMOR_ID`),
  ADD KEY `COMOR_ID` (`COMOR_ID`);

--
-- Indexes for table `member_subscription`
--
ALTER TABLE `member_subscription`
  ADD PRIMARY KEY (`MEMBER_ID`,`SUB_ID`,`START_DATE`,`END_DATE`),
  ADD KEY `SUB_ID` (`SUB_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`PROGRAM_ID`);

--
-- Indexes for table `program_coach`
--
ALTER TABLE `program_coach`
  ADD PRIMARY KEY (`PROGRAM_ID`,`COACH_ID`),
  ADD KEY `COACH_ID` (`COACH_ID`);

--
-- Indexes for table `subscription`
--
ALTER TABLE `subscription`
  ADD PRIMARY KEY (`SUB_ID`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`TRANSACTION_ID`),
  ADD KEY `MEMBER_ID` (`MEMBER_ID`),
  ADD KEY `SUB_ID` (`SUB_ID`),
  ADD KEY `PAYMENT_ID` (`PAYMENT_ID`);

--
-- Indexes for table `transaction_log`
--
ALTER TABLE `transaction_log`
  ADD PRIMARY KEY (`LOG_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USER_ID`),
  ADD UNIQUE KEY `USERNAME` (`USERNAME`),
  ADD KEY `IDX_USER` (`USER_FNAME`,`USER_LNAME`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coach`
--
ALTER TABLE `coach`
  MODIFY `COACH_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `comorbidities`
--
ALTER TABLE `comorbidities`
  MODIFY `COMOR_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MEMBER_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `PROGRAM_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `SUB_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `transaction`
--
ALTER TABLE `transaction`
  MODIFY `TRANSACTION_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_log`
--
ALTER TABLE `transaction_log`
  MODIFY `LOG_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `USER_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `member`
--
ALTER TABLE `member`
  ADD CONSTRAINT `member_ibfk_1` FOREIGN KEY (`PROGRAM_ID`) REFERENCES `program` (`PROGRAM_ID`),
  ADD CONSTRAINT `member_ibfk_2` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);

--
-- Constraints for table `member_comorbidities`
--
ALTER TABLE `member_comorbidities`
  ADD CONSTRAINT `member_comorbidities_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `member_comorbidities_ibfk_2` FOREIGN KEY (`COMOR_ID`) REFERENCES `comorbidities` (`COMOR_ID`);

--
-- Constraints for table `member_subscription`
--
ALTER TABLE `member_subscription`
  ADD CONSTRAINT `member_subscription_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `member_subscription_ibfk_2` FOREIGN KEY (`SUB_ID`) REFERENCES `subscription` (`SUB_ID`);

--
-- Constraints for table `program_coach`
--
ALTER TABLE `program_coach`
  ADD CONSTRAINT `program_coach_ibfk_1` FOREIGN KEY (`PROGRAM_ID`) REFERENCES `program` (`PROGRAM_ID`),
  ADD CONSTRAINT `program_coach_ibfk_2` FOREIGN KEY (`COACH_ID`) REFERENCES `coach` (`COACH_ID`);

--
-- Constraints for table `transaction`
--
ALTER TABLE `transaction`
  ADD CONSTRAINT `transaction_ibfk_1` FOREIGN KEY (`MEMBER_ID`) REFERENCES `member` (`MEMBER_ID`),
  ADD CONSTRAINT `transaction_ibfk_2` FOREIGN KEY (`SUB_ID`) REFERENCES `subscription` (`SUB_ID`),
  ADD CONSTRAINT `transaction_ibfk_3` FOREIGN KEY (`PAYMENT_ID`) REFERENCES `payment` (`PAYMENT_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
