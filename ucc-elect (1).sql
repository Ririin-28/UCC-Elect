-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2025 at 05:16 PM
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
-- Database: `ucc-elect`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `accept_student_request` (IN `p_student_id` VARCHAR(10))   BEGIN
    DECLARE student_exists INT;

    SELECT COUNT(*) INTO student_exists
    FROM pending_list
    WHERE student_id = p_student_id;

    IF student_exists > 0 THEN
        INSERT INTO student (student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id)
        SELECT student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id
        FROM pending_list
        WHERE student_id = p_student_id;

        DELETE FROM pending_list
        WHERE student_id = p_student_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_coordinator` (IN `p_coord_id` VARCHAR(10), IN `p_last_name` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_middle_name` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_contact_number` VARCHAR(11), IN `p_coord_password` VARCHAR(255))   BEGIN
    INSERT INTO coordinator (
        coord_id, last_name, first_name, middle_name, email, contact_number, coord_password
    ) VALUES (
        p_coord_id, p_last_name, p_first_name, p_middle_name, p_email, p_contact_number, p_coord_password
    );
    
    INSERT INTO coordinator_audit (action, coord_id, timestamp)
    VALUES ('ADDED', p_coord_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_election_event` (IN `p_election_name` VARCHAR(255), IN `p_start_datetime` DATETIME, IN `p_end_datetime` DATETIME, IN `p_course_name` VARCHAR(255))   BEGIN
    DECLARE v_election_id INT;
    DECLARE v_course_id INT;

    SELECT election_id INTO v_election_id
    FROM elections_identification
    WHERE election_name = p_election_name;

    IF v_election_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Election name not found in database';
    END IF;

    SELECT course_id INTO v_course_id
    FROM course
    WHERE course_name = p_course_name;

    IF v_course_id IS NULL THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Course name not found in database';
    END IF;

    INSERT INTO election (election_id, course_id, start_datetime, end_datetime, status)
    VALUES (v_election_id, v_course_id, p_start_datetime, p_end_datetime, 'INACTIVE');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_facilitator` (IN `p_facilitator_id` VARCHAR(10), IN `p_last_name` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_middle_name` VARCHAR(50), IN `p_email` VARCHAR(100), IN `p_contact_number` VARCHAR(11), IN `p_faci_password` VARCHAR(255))   BEGIN
    INSERT INTO facilitator (
        facilitator_id, last_name, first_name, middle_name, email, contact_number, faci_password
    ) VALUES (
        p_facilitator_id, p_last_name, p_first_name, p_middle_name, p_email, p_contact_number, p_faci_password
    );
    
    INSERT INTO facilitator_audit (action, facilitator_id, timestamp)
    VALUES ('ADDED', p_facilitator_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_student` (IN `p_student_id` VARCHAR(50), IN `p_last_name` VARCHAR(100), IN `p_first_name` VARCHAR(100), IN `p_middle_name` VARCHAR(100), IN `p_gender` VARCHAR(10), IN `p_course_description` VARCHAR(255), IN `p_year_section` VARCHAR(10), IN `p_student_password` VARCHAR(255))   BEGIN
    DECLARE v_course_id INT;

    SELECT course_id INTO v_course_id
    FROM course
    WHERE course_description = p_course_description;

    IF v_course_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Invalid course description provided.';
    END IF;

    INSERT INTO pending_list (
        student_id,
        last_name,
        first_name,
        middle_name,
        gender,
        course_id,
        year_section,
        student_password
    )
    VALUES (
        p_student_id,
        p_last_name,
        p_first_name,
        p_middle_name,
        p_gender,
        v_course_id,
        p_year_section,
        p_student_password
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `admin_validate_login` (IN `adminID` VARCHAR(255), IN `inputPassword` VARCHAR(255))   BEGIN
  SELECT 1 FROM admin
  WHERE admin_id = adminID AND admin_password = inputPassword;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cast_vote` (IN `p_election_id` INT, IN `p_student_id` INT, IN `p_candidate_id` INT, IN `p_position_id` INT)   BEGIN
    INSERT INTO votes (election_id, student_id, candidate_id, position_id, timestamp)
    VALUES (p_election_id, p_student_id, p_candidate_id, p_position_id, NOW());

    INSERT INTO vote_totals (candidate_id, total_votes)
    VALUES (p_candidate_id, 1)
    ON DUPLICATE KEY UPDATE total_votes = total_votes + 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `change_student_password` (IN `p_student_id` VARCHAR(10), IN `p_old_password` VARCHAR(255), IN `p_new_password` VARCHAR(255), OUT `p_status` VARCHAR(255))   BEGIN
    DECLARE current_password VARCHAR(255);

    SELECT student_password INTO current_password
    FROM student
    WHERE student_id = p_student_id;

    IF current_password IS NULL THEN
        SET p_status = 'Student ID not found.';
    ELSEIF NOT (BINARY current_password = p_old_password) THEN
        SET p_status = 'The old password is incorrect.';
    ELSE
        UPDATE student 
        SET student_password = p_new_password
        WHERE student_id = p_student_id;

        SET p_status = 'Password updated successfully.';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `coord_validate_login` (IN `coordID` VARCHAR(10), IN `inputPassword` VARCHAR(255))   BEGIN
	SELECT 1 FROM coordinator
    WHERE coord_id = coordID AND coord_password = inputPassword;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `decline_student_request` (IN `p_student_id` VARCHAR(10))   BEGIN
    DELETE FROM pending_list WHERE student_id = p_student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_coordinator` (IN `p_coord_id` VARCHAR(10))   BEGIN
    INSERT INTO archive_coordinators (coord_id, last_name, first_name, middle_name, email, contact_number, coord_password)
    SELECT 
        coord_id, last_name, first_name, COALESCE(middle_name, ''), email, contact_number, coord_password
    FROM coordinator
    WHERE coord_id = p_coord_id;

    DELETE FROM coordinator
    WHERE coord_id = p_coord_id;

    INSERT INTO coordinator_audit (action, coord_id, timestamp)
    VALUES ('DELETED', p_coord_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_course` (IN `p_course_id` INT)   BEGIN

    DELETE FROM course
    WHERE course_id = p_course_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_facilitator` (IN `p_facilitator_id` VARCHAR(10))   BEGIN
    INSERT INTO archive_facilitators (facilitator_id, last_name, first_name, middle_name, email, contact_number, faci_password)
    SELECT 
        facilitator_id, last_name, first_name, middle_name, email, contact_number, faci_password
    FROM facilitator
    WHERE facilitator_id = p_facilitator_id;

    DELETE FROM facilitator
    WHERE facilitator_id = p_facilitator_id;

    INSERT INTO facilitator_audit (action, facilitator_id, timestamp)
    VALUES ('DELETED', p_facilitator_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `delete_student` (IN `p_student_id` VARCHAR(10))   BEGIN
    INSERT INTO archive_students (student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id)
    SELECT 
        student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id
    FROM student
    WHERE student_id = p_student_id;

    DELETE FROM student
    WHERE student_id = p_student_id;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `edit_coordinator` (IN `p_coord_id` VARCHAR(10), IN `p_last_name` VARCHAR(255), IN `p_first_name` VARCHAR(255), IN `p_middle_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_contact_number` VARCHAR(20))   BEGIN
    UPDATE coordinator
    SET 
        last_name = p_last_name,
        first_name = p_first_name,
        middle_name = p_middle_name,
        email = p_email,
        contact_number = p_contact_number
    WHERE coord_id = p_coord_id;

    INSERT INTO coordinator_audit (action, coord_id, timestamp)
    VALUES ('EDITED', p_coord_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `edit_facilitator` (IN `p_facilitator_id` VARCHAR(10), IN `p_last_name` VARCHAR(255), IN `p_first_name` VARCHAR(255), IN `p_middle_name` VARCHAR(255), IN `p_email` VARCHAR(255), IN `p_contact_number` VARCHAR(15))   BEGIN
    UPDATE facilitator
    SET 
        last_name = p_last_name,
        first_name = p_first_name,
        middle_name = p_middle_name,
        email = p_email,
        contact_number = p_contact_number
    WHERE facilitator_id = p_facilitator_id;

    INSERT INTO facilitator_audit (action, facilitator_id, timestamp)
    VALUES ('EDITED', p_facilitator_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `edit_student` (IN `p_student_id` VARCHAR(10), IN `p_last_name` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_middle_name` VARCHAR(50), IN `p_gender` ENUM('Male','Female'), IN `p_year_section` VARCHAR(20), IN `p_course_name` VARCHAR(100))   BEGIN
    DECLARE v_course_id INT;

    SELECT course_id INTO v_course_id
    FROM course
    WHERE course_name = p_course_name;

    IF v_course_id IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Course not found';
    ELSE

        UPDATE student
        SET 
        	last_name = p_last_name,
            first_name = p_first_name,
            middle_name = p_middle_name,
            gender = p_gender,
            year_section = p_year_section,
            course_id = v_course_id
        WHERE student_id = p_student_id;
        
        INSERT INTO students_audit (action, student_id, timestamp)
    	VALUES ('EDITED', p_student_id, NOW());
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ElectionHistoryUpdate` (IN `p_end_datetime` DATETIME)   BEGIN
    UPDATE election
    SET status = 'INACTIVE'
    WHERE end_datetime = p_end_datetime;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `facilitator_validate_login` (IN `facilitatorID` VARCHAR(255), IN `inputPassword` VARCHAR(255))   BEGIN
  SELECT 1 FROM facilitator 
  WHERE facilitator_id = facilitatorID AND faci_password = inputPassword;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_archived_coordinators` ()   BEGIN
    SELECT coord_id, 
           CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name, '')) AS name
    FROM archive_coordinators;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_archived_facilitators` ()   BEGIN
    SELECT facilitator_id, 
           CONCAT(last_name, ', ', first_name, ' ', COALESCE(middle_name, '')) AS name
    FROM archive_facilitators;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_coordinator_by_id` (IN `p_coord_id` VARCHAR(10))   BEGIN
    SELECT coord_id, 
           CONCAT(last_name, ' ', first_name, ' ', COALESCE(middle_name, '')) AS name,
           email,
           contact_number
    FROM coordinator
    WHERE coord_id = p_coord_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_facilitator_by_id` (IN `p_facilitator_id` VARCHAR(10))   BEGIN
    SELECT facilitator_id, 
           CONCAT(last_name, ' ', first_name, ' ', middle_name) AS name,
           email,
           contact_number
    FROM facilitator
    WHERE facilitator_id = p_facilitator_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_facilitator_dashboard` ()   BEGIN
    SELECT * FROM view_facilitator_overview;
    SELECT * FROM view_course_stats;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_student_by_id` (IN `student_id_param` VARCHAR(10))   BEGIN
    SELECT 
        s.student_id, 
        CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) AS name, 
        s.gender, 
        c.course_name, 
        s.year_section 
    FROM student s
    INNER JOIN course c 
        ON s.course_id = c.course_id
    WHERE s.student_id = student_id_param;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_student_course_info` ()   BEGIN
    SELECT 
        s.student_id,
        CONCAT(s.last_name, ', ', s.first_name, ' ', s.middle_name) AS name,
        s.gender,
        s.year_section,
        c.course_name
    FROM student s
    INNER JOIN course c ON s.course_id = c.course_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `get_student_details` (IN `p_student_id` VARCHAR(10))   BEGIN
    SELECT 
        s.student_id, 
        s.last_name, 
        s.first_name, 
        s.middle_name, 
        s.gender, 
        c.course_name,
        s.year_section
    FROM student s
    JOIN course c ON s.course_id = c.course_id 
    WHERE s.student_id = p_student_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `import_candidate_list` (IN `p_election_name` VARCHAR(255), IN `p_last_name` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_middle_name` VARCHAR(50), IN `p_position_name` VARCHAR(255), IN `p_course_name` VARCHAR(255), IN `p_year_section` VARCHAR(255))   BEGIN
    DECLARE v_course_id INT;
    DECLARE v_position_id INT;
    DECLARE v_election_id INT;

    SELECT course_id INTO v_course_id
    FROM course
    WHERE course_name = p_course_name
    LIMIT 1;

    SELECT position_id INTO v_position_id
    FROM positions
    WHERE position_name = p_position_name
    LIMIT 1;
    
    SELECT election_id INTO v_election_id
    FROM elections_identification
    WHERE election_name = p_election_name
    LIMIT 1;

    INSERT INTO candidate (
        election_id, last_name, first_name, middle_name, position_id, course_id, year_section, timestamp
    ) VALUES (
        v_election_id, p_last_name, p_first_name, p_middle_name, v_position_id, v_course_id, p_year_section, NOW()
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `import_class_list` (IN `p_student_id` VARCHAR(50), IN `p_last_name` VARCHAR(50), IN `p_first_name` VARCHAR(50), IN `p_middle_name` VARCHAR(50), IN `p_gender` VARCHAR(10), IN `p_course_name` VARCHAR(100), IN `p_year_section` VARCHAR(50), IN `p_student_password` VARCHAR(255))   BEGIN
    DECLARE v_course_id INT;

    SELECT course_id INTO v_course_id
    FROM course
    WHERE course_name = p_course_name;

    INSERT INTO student (
        student_id, last_name, first_name, middle_name, gender, course_id, year_section, student_password
    ) VALUES (
        p_student_id, p_last_name, p_first_name, p_middle_name, p_gender, v_course_id, p_year_section, p_student_password
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertVote` (IN `p_election_id` INT, IN `p_student_id` INT, IN `p_candidate_id` INT, IN `p_position_id` INT)   BEGIN
    INSERT INTO votes (election_id, student_id, candidate_id, position_id, timestamp) 
    VALUES (p_election_id, p_student_id, p_candidate_id, p_position_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `LiveElectionUpdate` (IN `p_end_datetime` DATETIME)   BEGIN
    UPDATE election
    SET status = 'INACTIVE'
    WHERE end_datetime = p_end_datetime;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `restore_coordinator` (IN `p_coord_id` VARCHAR(10))   BEGIN
    DECLARE coordinator_exists INT;

    SELECT COUNT(*) INTO coordinator_exists 
    FROM archive_coordinators 
    WHERE coord_id = p_coord_id;

    IF coordinator_exists > 0 THEN
        INSERT INTO coordinator (coord_id, last_name, first_name, middle_name, email, contact_number, coord_password)
        SELECT coord_id, last_name, first_name, middle_name, email, contact_number, coord_password
        FROM archive_coordinators 
        WHERE coord_id = p_coord_id;

        DELETE FROM archive_coordinators 
        WHERE coord_id = p_coord_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `restore_facilitator` (IN `p_facilitator_id` VARCHAR(10))   BEGIN
    DECLARE facilitator_exists INT;

    SELECT COUNT(*) INTO facilitator_exists 
    FROM archive_facilitators 
    WHERE facilitator_id = p_facilitator_id;

    IF facilitator_exists > 0 THEN
        INSERT INTO facilitator (facilitator_id, last_name, first_name, middle_name, email, contact_number, faci_password)
        SELECT facilitator_id, last_name, first_name, middle_name, email, contact_number, faci_password
        FROM archive_facilitators 
        WHERE facilitator_id = p_facilitator_id;

        DELETE FROM archive_facilitators 
        WHERE facilitator_id = p_facilitator_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `restore_student` (IN `p_student_id` VARCHAR(10))   BEGIN
    DECLARE student_exists INT;

    SELECT COUNT(*) INTO student_exists
    FROM archive_students
    WHERE student_id = p_student_id;
     IF student_exists > 0 THEN

        INSERT INTO student (student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id)
        SELECT student_id, last_name, first_name, middle_name, gender, year_section, student_password, course_id
        FROM archive_students
        WHERE student_id = p_student_id
        LIMIT 1;

        -- Delete the row from archive_students
        DELETE FROM archive_students
        WHERE student_id = p_student_id
        LIMIT 1;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `students_audit` (IN `student_id` INT, IN `action_type` VARCHAR(50))   BEGIN
    INSERT INTO students_audit (student_id, action, timestamp)
    VALUES (student_id, action_type, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `student_validate_login` (IN `studentID` VARCHAR(255), IN `inputPassword` VARCHAR(255))   BEGIN
  SELECT * FROM student 
  WHERE student_id = studentID AND student_password = inputPassword;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `submit_vote` (IN `election_id` INT, IN `student_id` INT, IN `candidate_id` INT, IN `position_id` INT(255))   BEGIN
    INSERT INTO votes (election_id, student_id, candidate_id, position_id, timestamp)
    VALUES (election_id, student_id, candidate_id, position_id, NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateVote` (IN `p_candidate_id` INT, IN `p_election_id` INT, IN `p_student_id` INT, IN `p_position_id` INT)   BEGIN
    UPDATE votes 
    SET candidate_id = p_candidate_id, timestamp = NOW()
    WHERE election_id = p_election_id AND student_id = p_student_id AND position_id = p_position_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_password` (IN `p_student_id` VARCHAR(50), IN `p_new_password` VARCHAR(255))   BEGIN
    UPDATE student
    SET student_password = SHA2(p_new_password, 256), last_password_update = NOW()
    WHERE student_id = p_student_id;

    INSERT INTO student_pw_management (student_id, student_password, last_pw_update)
    VALUES (p_student_id, SHA2(p_new_password, 256), NOW())
    ON DUPLICATE KEY UPDATE
        student_password = SHA2(p_new_password, 256),
        last_pw_update = NOW();
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` varchar(10) NOT NULL,
  `admin_password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `admin_password`) VALUES
('10110001-A', 'Administrator'),
('10110002-A', 'admin123');

-- --------------------------------------------------------

--
-- Table structure for table `archive_coordinators`
--

CREATE TABLE `archive_coordinators` (
  `coord_id` varchar(10) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `coord_password` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_coordinators`
--

INSERT INTO `archive_coordinators` (`coord_id`, `last_name`, `first_name`, `middle_name`, `email`, `contact_number`, `coord_password`, `timestamp`) VALUES
('40440008-C', 'Dela Pena', 'Juan', 'Cruz', 'juandelapena@gmail.com', NULL, '1', '2024-12-23 09:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `archive_facilitators`
--

CREATE TABLE `archive_facilitators` (
  `facilitator_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `faci_password` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_facilitators`
--

INSERT INTO `archive_facilitators` (`facilitator_id`, `last_name`, `first_name`, `middle_name`, `email`, `contact_number`, `faci_password`, `timestamp`) VALUES
('30330002-F', 'Batumbakal', 'James', 'Apostal', 'jamesbatumbakal@gmail.com', '09541748234', 'james123', '2024-12-19 08:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `archive_students`
--

CREATE TABLE `archive_students` (
  `student_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `year_section` varchar(3) NOT NULL,
  `student_password` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archive_students`
--

INSERT INTO `archive_students` (`student_id`, `last_name`, `first_name`, `middle_name`, `gender`, `year_section`, `student_password`, `course_id`, `timestamp`) VALUES
('20257777-N', 'Crisostomo', 'Joshua', 'Tuazon', 'Male', '1-A', 'tuazon', 104, '2024-12-23 01:33:06');

-- --------------------------------------------------------

--
-- Table structure for table `candidate`
--

CREATE TABLE `candidate` (
  `candidate_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `year_section` varchar(50) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidate`
--

INSERT INTO `candidate` (`candidate_id`, `election_id`, `course_id`, `position_id`, `last_name`, `first_name`, `middle_name`, `year_section`, `timestamp`) VALUES
(1, 4, 102, 1, 'Alonso', 'Rico', 'Mercado', '1-A', '2024-12-20 20:08:58'),
(2, 4, 102, 5, 'Gallego', 'Lucas', 'Trinidad', '3-B', '2024-12-20 20:08:58'),
(3, 4, 102, 4, 'Montes', 'Theo', 'Balagbagan', '4-A', '2024-12-20 20:08:58'),
(4, 4, 102, 11, 'Mariano', 'Joyce', 'Arellano', '4-A', '2024-12-20 20:08:58'),
(5, 4, 102, 7, 'Lazaro', 'Ryan Andrei', 'Mallari', '2-B', '2024-12-20 20:08:58'),
(6, 4, 102, 5, 'Atienza', 'Luca ', 'Santosidad', '3-A', '2024-12-20 20:08:58'),
(7, 4, 102, 9, 'Alonso', 'Ashley', 'Mercado', '2-B', '2024-12-20 20:08:58'),
(8, 4, 102, 1, 'Bustamante', 'Amelie', 'Aguinaldo', '4-B', '2024-12-20 20:08:58'),
(9, 4, 102, 8, 'Medina', 'Hazel', 'Malin', '1-B', '2024-12-20 20:08:58'),
(10, 4, 102, 7, 'Chua', 'Ryan', 'Sue', '3-A', '2024-12-20 20:08:58'),
(11, 4, 102, 2, 'Peralta', 'Eugene', 'Ortiz', '2-B', '2024-12-20 20:08:58'),
(12, 4, 102, 6, 'Magbanua', 'Ashley', 'Burce', '3-A', '2024-12-20 20:08:58'),
(13, 4, 102, 10, 'Legaspi', 'Lara', 'San Jose', '3-A', '2024-12-20 20:08:58'),
(14, 4, 102, 4, 'Aquino', 'Kyle Morgan', 'Munoz', '2-B', '2024-12-20 20:08:58'),
(15, 4, 102, 10, 'Cabrera', 'Gillian', 'Quezon', '3-A', '2024-12-20 20:08:58'),
(16, 4, 102, 3, 'Labrador', 'Angeline', 'Seropian', '2-B', '2024-12-20 20:08:58'),
(17, 4, 102, 3, 'Mohammad', 'Abdul', 'Abdul', '1-B', '2024-12-20 20:08:58'),
(18, 4, 102, 6, 'De Luna', 'Mark', 'Quipit', '4-A', '2024-12-20 20:08:58'),
(19, 4, 102, 2, 'Mendez', 'Rafael Miene', 'Aguinaldo', '4-A', '2024-12-20 20:08:58');

-- --------------------------------------------------------

--
-- Table structure for table `coordinator`
--

CREATE TABLE `coordinator` (
  `coord_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(11) NOT NULL,
  `coord_password` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coordinator`
--

INSERT INTO `coordinator` (`coord_id`, `last_name`, `first_name`, `middle_name`, `email`, `contact_number`, `coord_password`, `timestamp`) VALUES
('40440001-C', 'a', 'a', 'a', 'joserizal@gmail.com', '09125245743', 'C', '2024-12-23 02:19:29'),
('40440009-C', 'Nadura', 'Riana', 'Rapio', 'riananadura1@gmail.com', '09167794168', '40440009-C', '2024-12-23 02:19:42');

-- --------------------------------------------------------

--
-- Table structure for table `coordinator_audit`
--

CREATE TABLE `coordinator_audit` (
  `log_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `coord_id` varchar(10) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coordinator_audit`
--

INSERT INTO `coordinator_audit` (`log_id`, `action`, `coord_id`, `timestamp`) VALUES
(1, 'ADDED', '40440002-C', '2024-12-20 07:35:03'),
(2, 'DELETED', '', '2024-12-23 08:05:50'),
(3, 'DELETED', '', '2024-12-23 08:05:58'),
(4, 'ADDED', '40440009-C', '2024-12-23 08:08:20'),
(5, 'DELETED', '', '2024-12-23 08:08:31'),
(6, 'DELETED', '40440009-C', '2024-12-23 08:09:32'),
(7, 'EDITED', '40440001-C', '2024-12-23 08:35:42'),
(8, 'DELETED', '', '2024-12-23 08:39:35'),
(9, 'EDITED', '40440001-C', '2024-12-23 09:20:25'),
(10, 'EDITED', '40440001-C', '2024-12-23 09:32:08'),
(11, 'EDITED', '40440001-C', '2024-12-23 09:56:13'),
(12, 'DELETED', '40440001-C', '2024-12-23 09:56:18');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE `course` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `course_description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`course_id`, `course_name`, `course_description`) VALUES
(101, 'BSIT', 'Bachelor of Science in Information Technology'),
(102, 'BSIS', 'Bachelor of Science in Information Systems'),
(103, 'BSCS', 'Bachelor of Science in Computer Science'),
(104, 'BSEMC', 'Bachelor of Science in Entertainment and Multimedia Computing');

-- --------------------------------------------------------

--
-- Table structure for table `election`
--

CREATE TABLE `election` (
  `id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `election`
--

INSERT INTO `election` (`id`, `election_id`, `course_id`, `start_datetime`, `end_datetime`, `status`) VALUES
(2, 4, 102, '2024-12-22 03:55:00', '2024-12-22 11:09:57', 'INACTIVE');

--
-- Triggers `election`
--
DELIMITER $$
CREATE TRIGGER `after_election_status_update` AFTER UPDATE ON `election` FOR EACH ROW BEGIN
    -- Check if the status changed to INACTIVE
    IF OLD.status != 'INACTIVE' AND NEW.status = 'INACTIVE' THEN
        -- Insert the record into the election_history table
        INSERT INTO election_history (election_id, course_id, start_datetime, end_datetime, status)
        VALUES (NEW.election_id, NEW.course_id, NEW.start_datetime, NEW.end_datetime, NEW.status);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `elections_identification`
--

CREATE TABLE `elections_identification` (
  `election_id` int(11) NOT NULL,
  `election_name` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `elections_identification`
--

INSERT INTO `elections_identification` (`election_id`, `election_name`) VALUES
(1, 'IT-2024'),
(2, 'IT-2025'),
(3, 'IT-2026'),
(4, 'IS-2024'),
(5, 'IS-2025'),
(6, 'IS-2026'),
(7, 'CS-2024'),
(8, 'CS-2025'),
(9, 'CS-2026'),
(10, 'EMC-2024'),
(11, 'EMC-2025'),
(12, 'EMC-2026');

-- --------------------------------------------------------

--
-- Table structure for table `election_history`
--

CREATE TABLE `election_history` (
  `id` int(11) NOT NULL,
  `election_id` int(11) DEFAULT NULL,
  `course_id` int(11) DEFAULT NULL,
  `start_datetime` datetime DEFAULT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'INACTIVE',
  `moved_to_history_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `facilitator`
--

CREATE TABLE `facilitator` (
  `facilitator_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(11) DEFAULT NULL,
  `faci_password` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilitator`
--

INSERT INTO `facilitator` (`facilitator_id`, `last_name`, `first_name`, `middle_name`, `email`, `contact_number`, `faci_password`, `timestamp`) VALUES
('30330001-F', 'Dela Cruz', 'Juan', 'Reyes', 'juandelacruz@gmail.com', '09345671921', 'password123', '2024-12-23 01:54:05'),
('30330002-F', 'wan', 'wan', 'wan', 'ebernardo@gmail.com', '09154556486', 'bernardo', '2024-12-23 01:53:04'),
('30330003-F', 'Martinez', 'Roberto', 'Pejo', 'robertopmartinez@gmail.com', '09568352341', 'martinez', '2024-12-19 10:36:20'),
('30330412-F', 'Nadura', 'Riana', 'Rapio', 'riananadura1@gmail.com', '09167794168', '$2y$10$Q2E3/LAUMR2tz3c/xkocpub0K7TvlxpYouOLg6p3gO2uvLbSMWVZy', '2024-12-19 17:02:12');

-- --------------------------------------------------------

--
-- Table structure for table `facilitator_audit`
--

CREATE TABLE `facilitator_audit` (
  `log_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `facilitator_id` varchar(10) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilitator_audit`
--

INSERT INTO `facilitator_audit` (`log_id`, `action`, `facilitator_id`, `timestamp`) VALUES
(1, 'RESTORE', '30330046-F', '2024-12-14 16:35:52'),
(2, 'EDITED', '30330003-F', '2024-12-14 16:43:05'),
(3, 'DELETED', '30330046-F', '2024-12-14 16:55:03'),
(4, 'DELETED', '30330003-F', '2024-12-14 17:07:36'),
(5, 'INSERT', '30330046-F', '2024-12-17 12:02:17'),
(6, 'DELETED', '30330046-F', '2024-12-17 13:44:52'),
(7, 'DELETED', '30330046-F', '2024-12-17 14:11:13'),
(8, 'DELETED', '30330003-F', '2024-12-17 14:37:34'),
(9, 'DELETED', '30330046-F', '2024-12-17 15:16:05'),
(10, 'DELETED', '30330005-F', '2024-12-17 16:44:55'),
(11, 'DELETED', '30330005-F', '2024-12-17 16:47:19'),
(12, 'DELETED', '30330003-F', '2024-12-17 17:00:45'),
(13, 'DELETED', '30330003-F', '2024-12-17 17:02:06'),
(14, 'DELETED', '30330003-F', '2024-12-17 17:49:59'),
(15, 'DELETED', '30330002-F', '2024-12-17 17:56:12'),
(16, 'DELETED', '30330003-F', '2024-12-17 22:56:28'),
(17, 'DELETED', '30330001-F', '2024-12-19 10:49:42'),
(18, 'DELETED', '30330002-F', '2024-12-19 10:54:05'),
(19, 'DELETED', '30330003-F', '2024-12-19 18:36:06'),
(20, 'ADDED', '30330412-F', '2024-12-20 01:02:12'),
(21, 'DELETED', '30330001-F', '2024-12-23 07:58:17'),
(22, 'DELETED', '30330001-F', '2024-12-23 09:52:48'),
(23, 'EDITED', '30330002-F', '2024-12-23 09:53:04');

-- --------------------------------------------------------

--
-- Table structure for table `pending_list`
--

CREATE TABLE `pending_list` (
  `student_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `year_section` varchar(3) NOT NULL,
  `student_password` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pending_list`
--

INSERT INTO `pending_list` (`student_id`, `last_name`, `first_name`, `middle_name`, `gender`, `year_section`, `student_password`, `course_id`, `timestamp`) VALUES
('20219758-N', 'Valencio', 'Vince', NULL, 'Male', '4-A', '20219758-N', 104, '2024-12-19 08:03:01'),
('20230333-N', 'Morales', 'Sophia', 'Reymundo', 'Female', '2-A', '20230333-N', 101, '2024-12-19 08:03:01');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_name`) VALUES
(1, 'President'),
(2, 'Vice President'),
(3, 'Secretary'),
(4, 'Auditor'),
(5, 'Treasurer'),
(6, 'Business Manager'),
(7, 'Creative Committee'),
(8, 'First Year Representative'),
(9, 'Second Year Representative'),
(10, 'Third Year Representative'),
(11, 'Fourth Year Representative');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` varchar(10) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') NOT NULL,
  `year_section` varchar(3) NOT NULL,
  `student_password` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `last_name`, `first_name`, `middle_name`, `gender`, `year_section`, `student_password`, `course_id`) VALUES
('20213444-N', 'Lambert', 'Roman', 'Eve', 'Male', '1-A', 'roman', 102),
('20219758-N', 'Cruz', 'Armin', 'Dane', 'Male', '4-A', '20219758-N', 104),
('20220376-N', 'Gomez', 'John Edward', 'Lacaden', 'Male', '3-A', 'edgomez', 102),
('20220636-N', 'Nadura', 'Riana', 'Rapio', 'Female', '3-A', 'micromichimik', 102),
('20242345-N', 'Cabuquin', 'Henrei', 'Santiago', 'Male', '1-A', '20242345-N', 104);

-- --------------------------------------------------------

--
-- Table structure for table `students_audit`
--

CREATE TABLE `students_audit` (
  `log_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students_audit`
--

INSERT INTO `students_audit` (`log_id`, `action`, `student_id`, `timestamp`) VALUES
(1, 'UPDATE', 20220376, '2024-12-14 09:38:29'),
(2, 'UPDATE', 20220376, '2024-12-14 09:38:42'),
(3, 'UPDATE', 20220336, '2024-12-14 09:47:30'),
(4, 'UPDATE', 20220336, '2024-12-14 09:52:58'),
(5, 'DELETE', 20220336, '2024-12-14 10:01:49'),
(6, 'RESTORE', 20193443, '2024-12-14 10:36:32'),
(7, 'DELETE', 20193443, '2024-12-14 10:36:41'),
(8, 'UPDATE', 20220376, '2024-12-14 16:53:40'),
(9, 'UPDATE', 20220376, '2024-12-14 18:30:14'),
(12, 'UPDATE', 20220376, '2024-12-15 18:58:06'),
(13, 'UPDATE', 20220376, '2024-12-15 19:04:05'),
(14, 'UPDATE', 20220376, '2024-12-15 19:06:28'),
(15, 'UPDATE', 20220376, '2024-12-15 19:07:49'),
(16, 'UPDATE', 20220376, '2024-12-15 19:11:40'),
(42, 'EDITED', 20220376, '2024-12-23 01:53:45');

-- --------------------------------------------------------

--
-- Table structure for table `student_pw_audit`
--

CREATE TABLE `student_pw_audit` (
  `audit_id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_pw_audit`
--

INSERT INTO `student_pw_audit` (`audit_id`, `student_id`, `action`, `action_date`) VALUES
(1, '20220376-N', 'Password Changed', '2024-12-16 02:58:06'),
(2, '20220376-N', 'Password Changed', '2024-12-16 03:04:05'),
(3, '20220376-N', 'Password Changed', '2024-12-16 03:06:28'),
(4, '20220376-N', 'Password Changed', '2024-12-16 03:07:49'),
(5, '20220376-N', 'Password Changed', '2024-12-16 03:11:40');

-- --------------------------------------------------------

--
-- Table structure for table `student_pw_management`
--

CREATE TABLE `student_pw_management` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `student_password` varchar(255) NOT NULL,
  `last_pw_update` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_pw_management`
--

INSERT INTO `student_pw_management` (`id`, `student_id`, `student_password`, `last_pw_update`) VALUES
(1, '20220376-N', '$2y$10$UFX208JTEpOolxVXoT33PO6EqzL2v7wieTHmCt/JM/9IX46CLd6R2', '2024-12-16 02:58:06'),
(2, '20220376-N', 'edgomz', '2024-12-16 03:04:05'),
(3, '20220376-N', '$2y$10$Nfa9rZuPsu0MC6P4X09zhuREOUt9yLtjb222ETMmD1graFcWxMHC.', '2024-12-16 03:06:28'),
(4, '20220376-N', 'edgomz', '2024-12-16 03:07:49'),
(5, '20220376-N', 'edgomez', '2024-12-16 03:11:40');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_archive_coordinators`
-- (See below for the actual view)
--
CREATE TABLE `view_archive_coordinators` (
`coord_id` varchar(10)
,`name` text
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_archive_facilitators`
-- (See below for the actual view)
--
CREATE TABLE `view_archive_facilitators` (
`facilitator_id` varchar(10)
,`name` varchar(153)
,`email` varchar(100)
,`contact_number` varchar(11)
,`faci_password` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_archive_students`
-- (See below for the actual view)
--
CREATE TABLE `view_archive_students` (
`student_id` varchar(10)
,`name` varchar(153)
,`gender` enum('Male','Female')
,`year_section` varchar(3)
,`student_password` varchar(255)
,`course_id` int(11)
,`course_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_candidate_votes`
-- (See below for the actual view)
--
CREATE TABLE `view_candidate_votes` (
`candidate_id` int(11)
,`last_name` varchar(100)
,`first_name` varchar(100)
,`position_id` int(11)
,`total_votes` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_coordinator`
-- (See below for the actual view)
--
CREATE TABLE `view_coordinator` (
`coord_id` varchar(10)
,`name` varchar(153)
,`email` varchar(100)
,`contact_number` varchar(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_course`
-- (See below for the actual view)
--
CREATE TABLE `view_course` (
`course_id` int(11)
,`course_name` varchar(100)
,`course_description` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_course_stats`
-- (See below for the actual view)
--
CREATE TABLE `view_course_stats` (
`course_id` int(11)
,`course_name` varchar(100)
,`total_students` bigint(21)
,`total_candidates` bigint(21)
,`event_status` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_elections_identification`
-- (See below for the actual view)
--
CREATE TABLE `view_elections_identification` (
`election_id` int(11)
,`election_name` varchar(8)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_election_candidates`
-- (See below for the actual view)
--
CREATE TABLE `view_election_candidates` (
`candidate_id` int(11)
,`election_name` varchar(8)
,`course_name` varchar(100)
,`position_name` varchar(255)
,`name` varchar(303)
,`year_section` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_election_event`
-- (See below for the actual view)
--
CREATE TABLE `view_election_event` (
`election_id` int(11)
,`course_id` int(11)
,`start_datetime` datetime
,`end_datetime` datetime
,`status` varchar(50)
,`course_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_facilitator`
-- (See below for the actual view)
--
CREATE TABLE `view_facilitator` (
`facilitator_id` varchar(10)
,`name` varchar(153)
,`email` varchar(100)
,`contact_number` varchar(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_facilitator_audit`
-- (See below for the actual view)
--
CREATE TABLE `view_facilitator_audit` (
`log_id` int(11)
,`action` varchar(50)
,`facilitator_id` varchar(10)
,`timestamp` datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_facilitator_overview`
-- (See below for the actual view)
--
CREATE TABLE `view_facilitator_overview` (
`total_courses` bigint(21)
,`total_candidates` bigint(21)
,`total_students` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_for_eballot`
-- (See below for the actual view)
--
CREATE TABLE `view_for_eballot` (
`candidate_id` int(11)
,`name` varchar(303)
,`course_name` varchar(100)
,`year_section` varchar(50)
,`position_name` varchar(255)
,`election_id` int(11)
,`election_name` varchar(8)
,`status` varchar(50)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_pending_list`
-- (See below for the actual view)
--
CREATE TABLE `view_pending_list` (
`student_id` varchar(10)
,`last_name` varchar(50)
,`first_name` varchar(50)
,`middle_name` varchar(50)
,`gender` enum('Male','Female')
,`year_section` varchar(3)
,`course` varchar(255)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_pending_requests`
-- (See below for the actual view)
--
CREATE TABLE `view_pending_requests` (
`student_id` varchar(10)
,`last_name` varchar(50)
,`first_name` varchar(50)
,`middle_name` varchar(50)
,`gender` enum('Male','Female')
,`year_section` varchar(3)
,`student_password` varchar(255)
,`course_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_student`
-- (See below for the actual view)
--
CREATE TABLE `view_student` (
`student_id` varchar(10)
,`name` varchar(153)
,`gender` enum('Male','Female')
,`year_section` varchar(3)
,`course_id` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `vote_id` int(11) NOT NULL,
  `election_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `votes`
--

INSERT INTO `votes` (`vote_id`, `election_id`, `student_id`, `candidate_id`, `position_id`, `timestamp`) VALUES
(298, 4, 20213444, 1, 1, '2024-12-22 15:29:21'),
(299, 4, 20213444, 11, 2, '2024-12-22 15:29:21'),
(300, 4, 20213444, 17, 3, '2024-12-22 15:29:21'),
(301, 4, 20213444, 14, 4, '2024-12-22 15:29:21'),
(302, 4, 20213444, 6, 5, '2024-12-22 15:29:21'),
(303, 4, 20213444, 12, 6, '2024-12-22 15:29:21'),
(304, 4, 20213444, 10, 7, '2024-12-22 15:29:21'),
(305, 4, 20213444, 9, 8, '2024-12-22 15:29:21'),
(306, 4, 20213444, 7, 9, '2024-12-22 15:29:21'),
(307, 4, 20213444, 13, 10, '2024-12-22 15:29:21'),
(308, 4, 20213444, 4, 11, '2024-12-22 15:29:21'),
(309, 4, 20219758, 8, 1, '2024-12-22 15:29:56'),
(310, 4, 20219758, 11, 2, '2024-12-22 15:29:56'),
(311, 4, 20219758, 16, 3, '2024-12-22 15:29:56'),
(312, 4, 20219758, 3, 4, '2024-12-22 15:29:56'),
(313, 4, 20219758, 2, 5, '2024-12-22 15:29:56'),
(314, 4, 20219758, 18, 6, '2024-12-22 15:29:56'),
(315, 4, 20219758, 5, 7, '2024-12-22 15:29:56'),
(316, 4, 20219758, 9, 8, '2024-12-22 15:29:56'),
(317, 4, 20219758, 7, 9, '2024-12-22 15:29:56'),
(318, 4, 20219758, 15, 10, '2024-12-22 15:29:56'),
(319, 4, 20219758, 4, 11, '2024-12-22 15:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `vote_totals`
--

CREATE TABLE `vote_totals` (
  `candidate_id` int(11) NOT NULL,
  `total_votes` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure for view `view_archive_coordinators`
--
DROP TABLE IF EXISTS `view_archive_coordinators`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_archive_coordinators`  AS SELECT `archive_coordinators`.`coord_id` AS `coord_id`, concat(`archive_coordinators`.`last_name`,', ',`archive_coordinators`.`first_name`,' ',coalesce(`archive_coordinators`.`middle_name`,'')) AS `name` FROM `archive_coordinators` ;

-- --------------------------------------------------------

--
-- Structure for view `view_archive_facilitators`
--
DROP TABLE IF EXISTS `view_archive_facilitators`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_archive_facilitators`  AS SELECT `archive_facilitators`.`facilitator_id` AS `facilitator_id`, concat(`archive_facilitators`.`last_name`,', ',`archive_facilitators`.`first_name`,' ',`archive_facilitators`.`middle_name`) AS `name`, `archive_facilitators`.`email` AS `email`, `archive_facilitators`.`contact_number` AS `contact_number`, `archive_facilitators`.`faci_password` AS `faci_password` FROM `archive_facilitators` ;

-- --------------------------------------------------------

--
-- Structure for view `view_archive_students`
--
DROP TABLE IF EXISTS `view_archive_students`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_archive_students`  AS SELECT `s`.`student_id` AS `student_id`, concat(`s`.`last_name`,', ',`s`.`first_name`,' ',coalesce(`s`.`middle_name`,'')) AS `name`, `s`.`gender` AS `gender`, `s`.`year_section` AS `year_section`, `s`.`student_password` AS `student_password`, `s`.`course_id` AS `course_id`, `c`.`course_name` AS `course_name` FROM (`archive_students` `s` join `course` `c` on(`s`.`course_id` = `c`.`course_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_candidate_votes`
--
DROP TABLE IF EXISTS `view_candidate_votes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_candidate_votes`  AS SELECT `c`.`candidate_id` AS `candidate_id`, `c`.`last_name` AS `last_name`, `c`.`first_name` AS `first_name`, `c`.`position_id` AS `position_id`, count(`v`.`student_id`) AS `total_votes` FROM (`candidate` `c` left join `votes` `v` on(`c`.`candidate_id` = `v`.`candidate_id`)) GROUP BY `c`.`candidate_id` ;

-- --------------------------------------------------------

--
-- Structure for view `view_coordinator`
--
DROP TABLE IF EXISTS `view_coordinator`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_coordinator`  AS SELECT `coordinator`.`coord_id` AS `coord_id`, concat(`coordinator`.`last_name`,', ',`coordinator`.`first_name`,' ',coalesce(`coordinator`.`middle_name`,'')) AS `name`, `coordinator`.`email` AS `email`, `coordinator`.`contact_number` AS `contact_number` FROM `coordinator` ;

-- --------------------------------------------------------

--
-- Structure for view `view_course`
--
DROP TABLE IF EXISTS `view_course`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_course`  AS SELECT `course`.`course_id` AS `course_id`, `course`.`course_name` AS `course_name`, `course`.`course_description` AS `course_description` FROM `course` ;

-- --------------------------------------------------------

--
-- Structure for view `view_course_stats`
--
DROP TABLE IF EXISTS `view_course_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_course_stats`  AS SELECT `c`.`course_id` AS `course_id`, `c`.`course_name` AS `course_name`, count(distinct `s`.`student_id`) AS `total_students`, count(distinct `ca`.`candidate_id`) AS `total_candidates`, coalesce(max(`e`.`status`),'Inactive') AS `event_status` FROM (((`course` `c` left join `student` `s` on(`c`.`course_id` = `s`.`course_id`)) left join `candidate` `ca` on(`c`.`course_id` = `ca`.`course_id`)) left join `election` `e` on(`c`.`course_id` = `e`.`course_id`)) GROUP BY `c`.`course_id`, `c`.`course_name` ;

-- --------------------------------------------------------

--
-- Structure for view `view_elections_identification`
--
DROP TABLE IF EXISTS `view_elections_identification`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_elections_identification`  AS SELECT `elections_identification`.`election_id` AS `election_id`, `elections_identification`.`election_name` AS `election_name` FROM `elections_identification` ;

-- --------------------------------------------------------

--
-- Structure for view `view_election_candidates`
--
DROP TABLE IF EXISTS `view_election_candidates`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_election_candidates`  AS SELECT `c`.`candidate_id` AS `candidate_id`, `e`.`election_name` AS `election_name`, `co`.`course_name` AS `course_name`, `p`.`position_name` AS `position_name`, concat(`c`.`last_name`,', ',`c`.`first_name`,' ',coalesce(`c`.`middle_name`,'')) AS `name`, `c`.`year_section` AS `year_section` FROM (((`candidate` `c` join `elections_identification` `e` on(`c`.`election_id` = `e`.`election_id`)) join `positions` `p` on(`c`.`position_id` = `p`.`position_id`)) join `course` `co` on(`c`.`course_id` = `co`.`course_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_election_event`
--
DROP TABLE IF EXISTS `view_election_event`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_election_event`  AS SELECT `e`.`election_id` AS `election_id`, `e`.`course_id` AS `course_id`, `e`.`start_datetime` AS `start_datetime`, `e`.`end_datetime` AS `end_datetime`, `e`.`status` AS `status`, `c`.`course_name` AS `course_name` FROM (`election` `e` join `course` `c` on(`e`.`course_id` = `c`.`course_id`)) WHERE current_timestamp() between `e`.`start_datetime` and `e`.`end_datetime` AND `e`.`status` = 'Active' ;

-- --------------------------------------------------------

--
-- Structure for view `view_facilitator`
--
DROP TABLE IF EXISTS `view_facilitator`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_facilitator`  AS SELECT `facilitator`.`facilitator_id` AS `facilitator_id`, concat(`facilitator`.`last_name`,', ',`facilitator`.`first_name`,' ',`facilitator`.`middle_name`) AS `name`, `facilitator`.`email` AS `email`, `facilitator`.`contact_number` AS `contact_number` FROM `facilitator` ;

-- --------------------------------------------------------

--
-- Structure for view `view_facilitator_audit`
--
DROP TABLE IF EXISTS `view_facilitator_audit`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_facilitator_audit`  AS SELECT `facilitator_audit`.`log_id` AS `log_id`, `facilitator_audit`.`action` AS `action`, `facilitator_audit`.`facilitator_id` AS `facilitator_id`, `facilitator_audit`.`timestamp` AS `timestamp` FROM `facilitator_audit` ;

-- --------------------------------------------------------

--
-- Structure for view `view_facilitator_overview`
--
DROP TABLE IF EXISTS `view_facilitator_overview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_facilitator_overview`  AS SELECT (select count(0) from `course`) AS `total_courses`, (select count(0) from `candidate`) AS `total_candidates`, (select count(0) from `student`) AS `total_students` ;

-- --------------------------------------------------------

--
-- Structure for view `view_for_eballot`
--
DROP TABLE IF EXISTS `view_for_eballot`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_for_eballot`  AS SELECT `c`.`candidate_id` AS `candidate_id`, concat(`c`.`last_name`,', ',`c`.`first_name`,' ',coalesce(`c`.`middle_name`,'')) AS `name`, `co`.`course_name` AS `course_name`, `c`.`year_section` AS `year_section`, `p`.`position_name` AS `position_name`, `c`.`election_id` AS `election_id`, `ei`.`election_name` AS `election_name`, `e`.`status` AS `status` FROM ((((`candidate` `c` join `course` `co` on(`c`.`course_id` = `co`.`course_id`)) join `positions` `p` on(`c`.`position_id` = `p`.`position_id`)) join `elections_identification` `ei` on(`c`.`election_id` = `ei`.`election_id`)) join `election` `e` on(`c`.`election_id` = `e`.`election_id`)) WHERE `e`.`status` = 'ACTIVE' ;

-- --------------------------------------------------------

--
-- Structure for view `view_pending_list`
--
DROP TABLE IF EXISTS `view_pending_list`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pending_list`  AS SELECT `p`.`student_id` AS `student_id`, `p`.`last_name` AS `last_name`, `p`.`first_name` AS `first_name`, `p`.`middle_name` AS `middle_name`, `p`.`gender` AS `gender`, `p`.`year_section` AS `year_section`, `c`.`course_description` AS `course` FROM (`pending_list` `p` join `course` `c` on(`p`.`course_id` = `c`.`course_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_pending_requests`
--
DROP TABLE IF EXISTS `view_pending_requests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_pending_requests`  AS SELECT `p`.`student_id` AS `student_id`, `p`.`last_name` AS `last_name`, `p`.`first_name` AS `first_name`, coalesce(`p`.`middle_name`,'') AS `middle_name`, `p`.`gender` AS `gender`, `p`.`year_section` AS `year_section`, `p`.`student_password` AS `student_password`, `c`.`course_name` AS `course_name` FROM (`pending_list` `p` join `course` `c` on(`p`.`course_id` = `c`.`course_id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_student`
--
DROP TABLE IF EXISTS `view_student`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_student`  AS SELECT `student`.`student_id` AS `student_id`, concat(`student`.`last_name`,', ',`student`.`first_name`,' ',`student`.`middle_name`) AS `name`, `student`.`gender` AS `gender`, `student`.`year_section` AS `year_section`, `student`.`course_id` AS `course_id` FROM `student` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `archive_coordinators`
--
ALTER TABLE `archive_coordinators`
  ADD PRIMARY KEY (`coord_id`);

--
-- Indexes for table `archive_facilitators`
--
ALTER TABLE `archive_facilitators`
  ADD PRIMARY KEY (`facilitator_id`);

--
-- Indexes for table `archive_students`
--
ALTER TABLE `archive_students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `candidate`
--
ALTER TABLE `candidate`
  ADD PRIMARY KEY (`candidate_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `candidate_ibfk_1` (`election_id`);

--
-- Indexes for table `coordinator`
--
ALTER TABLE `coordinator`
  ADD PRIMARY KEY (`coord_id`);

--
-- Indexes for table `coordinator_audit`
--
ALTER TABLE `coordinator_audit`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `course`
--
ALTER TABLE `course`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `election`
--
ALTER TABLE `election`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_election_id` (`election_id`),
  ADD KEY `fk_course_id` (`course_id`);

--
-- Indexes for table `elections_identification`
--
ALTER TABLE `elections_identification`
  ADD PRIMARY KEY (`election_id`);

--
-- Indexes for table `election_history`
--
ALTER TABLE `election_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `election_id` (`election_id`);

--
-- Indexes for table `facilitator`
--
ALTER TABLE `facilitator`
  ADD PRIMARY KEY (`facilitator_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `facilitator_audit`
--
ALTER TABLE `facilitator_audit`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `pending_list`
--
ALTER TABLE `pending_list`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `students_audit`
--
ALTER TABLE `students_audit`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `student_pw_audit`
--
ALTER TABLE `student_pw_audit`
  ADD PRIMARY KEY (`audit_id`);

--
-- Indexes for table `student_pw_management`
--
ALTER TABLE `student_pw_management`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`vote_id`);

--
-- Indexes for table `vote_totals`
--
ALTER TABLE `vote_totals`
  ADD PRIMARY KEY (`candidate_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `candidate`
--
ALTER TABLE `candidate`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `coordinator_audit`
--
ALTER TABLE `coordinator_audit`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `election`
--
ALTER TABLE `election`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `elections_identification`
--
ALTER TABLE `elections_identification`
  MODIFY `election_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `election_history`
--
ALTER TABLE `election_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `facilitator_audit`
--
ALTER TABLE `facilitator_audit`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `students_audit`
--
ALTER TABLE `students_audit`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `student_pw_audit`
--
ALTER TABLE `student_pw_audit`
  MODIFY `audit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `student_pw_management`
--
ALTER TABLE `student_pw_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `vote_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=320;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archive_students`
--
ALTER TABLE `archive_students`
  ADD CONSTRAINT `archive_students_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `candidate`
--
ALTER TABLE `candidate`
  ADD CONSTRAINT `candidate_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `elections_identification` (`election_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `candidate_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `candidate_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `election`
--
ALTER TABLE `election`
  ADD CONSTRAINT `fk_course_id` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `fk_election_id` FOREIGN KEY (`election_id`) REFERENCES `elections_identification` (`election_id`);

--
-- Constraints for table `election_history`
--
ALTER TABLE `election_history`
  ADD CONSTRAINT `election_history_ibfk_1` FOREIGN KEY (`election_id`) REFERENCES `election` (`election_id`) ON DELETE CASCADE;

--
-- Constraints for table `pending_list`
--
ALTER TABLE `pending_list`
  ADD CONSTRAINT `pending_list_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`),
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `course` (`course_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_pw_management`
--
ALTER TABLE `student_pw_management`
  ADD CONSTRAINT `student_pw_management_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vote_totals`
--
ALTER TABLE `vote_totals`
  ADD CONSTRAINT `vote_totals_ibfk_1` FOREIGN KEY (`candidate_id`) REFERENCES `candidate` (`candidate_id`);

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `check_election_activation` ON SCHEDULE EVERY 1 MINUTE STARTS '2024-12-15 22:47:22' ON COMPLETION NOT PRESERVE ENABLE DO CALL ActivateScheduledElections()$$

CREATE DEFINER=`root`@`localhost` EVENT `notify_expired_passwords` ON SCHEDULE EVERY 1 DAY STARTS '2024-12-16 02:17:41' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    INSERT INTO notifications (student_id, message, notification_date)
    SELECT student_id, 'Your password has not been changed for over 90 days.', NOW()
    FROM student
    WHERE DATEDIFF(NOW(), last_password_update) >= 90;
END$$

CREATE DEFINER=`root`@`localhost` EVENT `activate_election` ON SCHEDULE EVERY 1 MINUTE STARTS '2024-12-22 03:53:21' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN

    UPDATE election
    SET status = 'ACTIVE'
    WHERE status = 'INACTIVE' AND start_datetime <= NOW();

    UPDATE election
    SET status = 'INACTIVE'
    WHERE status = 'ACTIVE' AND end_datetime <= NOW();

END$$

CREATE DEFINER=`root`@`localhost` EVENT `reset_votes` ON SCHEDULE AT '2025-12-22 08:17:05' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    DELETE FROM votes;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
