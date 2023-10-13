SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE TABLE `{prefix}_api` (
  `token` varchar(100) NOT NULL,
  `due_date` datetime NOT NULL,
  `active` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `{prefix}_test_users` (
  `id` int NOT NULL,
  `fullname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `{prefix}_test_users` (`id`, `fullname`) VALUES
(1, 'Marco Cusano'),
(2, 'Mario Rossi'),
(3, 'Maria Bianchi'),
(4, 'John Doe');

CREATE TABLE `{prefix}_test_user_documents` (
  `user_id` int NOT NULL,
  `type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `due_date` datetime NOT NULL,
  `category` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `{prefix}_test_user_documents` (`user_id`, `type`, `due_date`, `category`) VALUES
(1, 'DRIVE', '2023-10-25 00:00:00', 'B'),
(2, 'IDENTITY', '2030-10-31 00:00:00', 'SOME'),
(1, 'IDENTITY', '2023-10-12 14:31:20', 'ELSE');

ALTER TABLE `{prefix}_api`
  ADD UNIQUE KEY `token` (`token`);

ALTER TABLE `{prefix}_test_users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `{prefix}_test_user_documents`
  ADD KEY `{prefix}_test_user_documents_users` (`user_id`);

ALTER TABLE `{prefix}_test_users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `{prefix}_test_user_documents`
  ADD CONSTRAINT `{prefix}_test_user_documents_users` FOREIGN KEY (`user_id`) REFERENCES `{prefix}_test_users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;
