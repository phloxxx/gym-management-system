-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 03:31 AM
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
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_AuthenticateUser` (IN `p_username` VARCHAR(20), IN `p_password` VARCHAR(15))   BEGIN
    SELECT USER_ID, USER_FNAME, USER_LNAME, USER_TYPE 
    FROM `USER`
    WHERE USERNAME = p_username 
    AND PASSWORD = p_password
    AND IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetActiveMembers` ()   BEGIN
    SELECT m.MEMBER_ID, m.MEMBER_FNAME, m.MEMBER_LNAME, 
           m.EMAIL, m.PHONE_NUMBER, p.PROGRAM_NAME
    FROM `MEMBER` m
    JOIN PROGRAM p ON m.PROGRAM_ID = p.PROGRAM_ID
    WHERE m.IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetActivePrograms` ()   BEGIN
    SELECT p.PROGRAM_NAME, 
           GROUP_CONCAT(CONCAT(c.COACH_FNAME, ' ', c.COACH_LNAME)) as Coaches
    FROM PROGRAM p
    LEFT JOIN PROGRAM_COACH pc ON p.PROGRAM_ID = pc.PROGRAM_ID
    LEFT JOIN COACH c ON pc.COACH_ID = c.COACH_ID
    WHERE p.IS_ACTIVE = 1
    GROUP BY p.PROGRAM_ID, p.PROGRAM_NAME;
END$$

DROP PROCEDURE IF EXISTS `sp_GetComorbidities`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetComorbidities`()
BEGIN
    SELECT COMOR_ID, COMOR_NAME, IS_ACTIVE
    FROM `comorbidities`
    ORDER BY COMOR_NAME ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetMembersByMonth` (IN `p_year` INT, IN `p_month` INT)   BEGIN
    SELECT COUNT(*) as MemberCount
    FROM `MEMBER`
    WHERE YEAR(JOINED_DATE) = p_year
    AND MONTH(JOINED_DATE) = p_month;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetMembersBySubscription` (IN `p_sub_id` SMALLINT)   BEGIN
    SELECT m.MEMBER_FNAME, m.MEMBER_LNAME, s.SUB_NAME,
           ms.START_DATE, ms.END_DATE
    FROM `MEMBER` m
    JOIN MEMBER_SUBSCRIPTION ms ON m.MEMBER_ID = ms.MEMBER_ID
    JOIN SUBSCRIPTION s ON ms.SUB_ID = s.SUB_ID
    WHERE ms.SUB_ID = p_sub_id
    AND ms.IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetMonthlyRevenue` (IN `p_year` INT, IN `p_month` INT)   BEGIN
    SELECT SUM(s.PRICE) as MonthlyRevenue
    FROM `TRANSACTION` t
    JOIN SUBSCRIPTION s ON t.SUB_ID = s.SUB_ID
    WHERE YEAR(t.TRANSAC_DATE) = p_year
    AND MONTH(t.TRANSAC_DATE) = p_month;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetPaymentMethods` ()   BEGIN
    SELECT PAY_METHOD
    FROM PAYMENT
    WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetStaffMembers` ()   BEGIN
    SELECT USER_ID, USER_FNAME, USER_LNAME, USERNAME, USER_TYPE
    FROM `USER`
    WHERE USER_TYPE = 'STAFF'
    AND IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetSubscriptions` ()   BEGIN
    SELECT SUB_NAME, DURATION, PRICE
    FROM SUBSCRIPTION
    WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetUsers` ()   BEGIN
    SELECT USER_ID, USER_FNAME, USER_LNAME, USERNAME, USER_TYPE
    FROM `USER`
    WHERE IS_ACTIVE = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertComorbidity` (IN `p_comor_id` SMALLINT, IN `p_comor_name` VARCHAR(50))   BEGIN
    IF p_comor_id IS NULL THEN
        INSERT INTO COMORBIDITIES (COMOR_NAME)
        VALUES (p_comor_name);
    ELSE
        UPDATE COMORBIDITIES
        SET COMOR_NAME = p_comor_name
        WHERE COMOR_ID = p_comor_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertPayment` (IN `p_payment_id` SMALLINT, IN `p_pay_method` VARCHAR(20))   BEGIN
    IF p_payment_id IS NULL THEN
        INSERT INTO PAYMENT (PAY_METHOD)
        VALUES (p_pay_method);
    ELSE
        UPDATE PAYMENT
        SET PAY_METHOD = p_pay_method
        WHERE PAYMENT_ID = p_payment_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertProgram` (IN `p_program_id` SMALLINT, IN `p_program_name` VARCHAR(50))   BEGIN
    IF p_program_id IS NULL THEN
        INSERT INTO PROGRAM (PROGRAM_NAME)
        VALUES (p_program_name);
    ELSE
        UPDATE PROGRAM
        SET PROGRAM_NAME = p_program_name
        WHERE PROGRAM_ID = p_program_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertSubscription` (IN `p_sub_id` SMALLINT, IN `p_sub_name` VARCHAR(20), IN `p_duration` VARCHAR(10), IN `p_price` DECIMAL(10,2))   BEGIN
    IF p_sub_id IS NULL THEN
        INSERT INTO SUBSCRIPTION (SUB_NAME, DURATION, PRICE)
        VALUES (p_sub_name, p_duration, p_price);
    ELSE
        UPDATE SUBSCRIPTION
        SET SUB_NAME = p_sub_name,
            DURATION = p_duration,
            PRICE = p_price
        WHERE SUB_ID = p_sub_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_UpsertUser` (IN `p_user_id` SMALLINT, IN `p_fname` VARCHAR(50), IN `p_lname` VARCHAR(30), IN `p_username` VARCHAR(20), IN `p_password` VARCHAR(15), IN `p_user_type` ENUM('ADMINISTRATOR','STAFF'))   BEGIN
    IF p_user_id IS NULL THEN
        INSERT INTO `USER` (USER_FNAME, USER_LNAME, USERNAME, PASSWORD, USER_TYPE)
        VALUES (p_fname, p_lname, p_username, p_password, p_user_type);
    ELSE
        UPDATE `USER`
        SET USER_FNAME = p_fname,
            USER_LNAME = p_lname,
            USERNAME = p_username,
            PASSWORD = p_password,
            USER_TYPE = p_user_type
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
(7, 'jeff', 'monreal', 'jeff@gmail.com', '0912312312312', 'MALE', '', 1),
(8, 'jeff', 'monreal', 'jeff@gmail.com', '0912312312312', 'MALE', '', 1);

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
(2, 'Arthritis', 1);

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
(15, 'Pautog', 1);

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
(15, 7),
(15, 8);

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
(31, 'ninz', 'qweqwe', 'qweqwe', '$2y$10$ExGFPtPnrN3Db2mNhHrvH.QZNLZ3vijgVqrexWGGo1t1wP7iW5p4.', 'STAFF', 1),
(36, 'ninz', 'ocliasa', 'asdasd', '$2y$10$bNmJk3JVVVRh/crlQshckOlV07q.xpdqIUBKQ.byBDXUCW0PW4sFS', 'ADMINISTRATOR', 1);

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
  MODIFY `COACH_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `comorbidities`
--
ALTER TABLE `comorbidities`
  MODIFY `COMOR_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `MEMBER_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` smallint(6) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `PROGRAM_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `subscription`
--
ALTER TABLE `subscription`
  MODIFY `SUB_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `USER_ID` smallint(6) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

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
