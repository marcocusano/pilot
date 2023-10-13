SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `{prefix}_api` (
  `token` varchar(100) NOT NULL,
  `due_date` datetime NOT NULL,
  `active` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `{prefix}_api`
  ADD UNIQUE KEY `token` (`token`);
COMMIT;
