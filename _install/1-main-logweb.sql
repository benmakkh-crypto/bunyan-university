-- القاعدة الرئيسية logweb (التسجيل، الأخبار، المكتبة، وثائق التخرج، حسابات البوابة)
-- جداول فارغة بلا بيانات — استوردها في phpMyAdmin بعد إنشاء القاعدة
SET NAMES utf8mb4;

CREATE TABLE `admins` (
  `id` int(11) UNSIGNED NOT NULL,
  `fname` varchar(30) NOT NULL,
  `username` varchar(90) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `arabic` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `arabicdec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `arabicms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `student_name` varchar(255) DEFAULT NULL,
  `instructor_name` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `grade` varchar(50) DEFAULT NULL,
  `issue_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE `ecodec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `ecoms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `econ` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `edu` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `edudec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `edums` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `faqh` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(200) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `faqhdec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(200) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `faqhms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(200) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `info` (
  `id` int(20) NOT NULL,
  `fname` varchar(20) NOT NULL,
  `lname` varchar(20) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `birth` varchar(20) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `id_iden` varchar(80) NOT NULL,
  `id_pass` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `country` varchar(70) NOT NULL,
  `stage` varchar(70) NOT NULL,
  `depart` varchar(70) NOT NULL,
  `img1` varchar(250) NOT NULL,
  `imgpass` varchar(255) NOT NULL,
  `imgpay` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `islamdec` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `islamic` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `islamms` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `lang` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `langdec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `langms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `law` (
  `id` int(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `lawdec` (
  `id` int(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `lawms` (
  `id` int(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `media` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `mediadec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `mediams` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `postevent` (
  `id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` mediumtext NOT NULL,
  `class` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `postgrad` (
  `id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` mediumtext NOT NULL,
  `class` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `date` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text NOT NULL,
  `content` mediumtext NOT NULL,
  `classification` varchar(200) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `prog` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `progdec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `progms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `image` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `quran` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(70) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(70) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `qurandec` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(70) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(70) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `quranms` (
  `id` int(70) NOT NULL,
  `date` varchar(100) NOT NULL,
  `title` varchar(70) NOT NULL,
  `teacher` varchar(100) NOT NULL,
  `link` varchar(70) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `rsch` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `rschdec` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `rschms` (
  `id` int(20) NOT NULL,
  `date` varchar(30) NOT NULL,
  `title` varchar(40) NOT NULL,
  `teacher` varchar(60) NOT NULL,
  `link` varchar(60) NOT NULL,
  `image` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_general_ci;

CREATE TABLE `search` (
  `id` int(70) NOT NULL,
  `name` varchar(100) NOT NULL,
  `Hyperlink` varchar(70) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `std` (
  `id` int(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `num` varchar(200) NOT NULL,
  `phone` varchar(200) NOT NULL,
  `pdf` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `std2` (
  `id_std` int(50) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(70) NOT NULL,
  `dob` varchar(100) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `id_iden` varchar(100) NOT NULL,
  `id_pass` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `level` varchar(100) NOT NULL,
  `depm` varchar(100) NOT NULL,
  `file` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_german2_ci;

CREATE TABLE `stdlb` (
  `id` int(11) UNSIGNED NOT NULL,
  `book_dep` varchar(64) NOT NULL,
  `book_name` varchar(64) NOT NULL,
  `pdf_file` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `student` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_ref` varchar(64) NOT NULL,
  `id_pass` varchar(64) NOT NULL,
  `pdf_file` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `phone` varchar(100) NOT NULL,
  `birth` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `id_iden` varchar(100) NOT NULL,
  `id_pass` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `stage` varchar(100) NOT NULL,
  `depart` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE `user` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `email` varchar(128) NOT NULL,
  `pass` varchar(256) NOT NULL,
  `code` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(128) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_adn` (
  `id` int(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `arabic`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `arabicdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `arabicms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ecodec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ecoms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `econ`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `edu`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `edudec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `edums`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `faqh`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `faqhdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `faqhms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `info`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `islamdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `islamic`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `islamms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lang`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `langdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `langms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `law`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lawdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `lawms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `media`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mediadec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `mediams`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `postevent`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `postgrad`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `prog`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `progdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `progms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `quran`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `qurandec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `quranms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rsch`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rschdec`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `rschms`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `search`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `std`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `std2`
  ADD PRIMARY KEY (`id_std`);

ALTER TABLE `stdlb`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `student`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`phone`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `user_adn`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `admins`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

ALTER TABLE `arabic`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `arabicdec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `arabicms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `ecodec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `ecoms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `econ`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `edu`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `edudec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `edums`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `faqh`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `faqhdec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `faqhms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `info`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

ALTER TABLE `islamdec`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `islamic`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `islamms`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `lang`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `langdec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `langms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `law`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `lawdec`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `lawms`
  MODIFY `id` int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `media`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `mediadec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `mediams`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `postevent`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

ALTER TABLE `postgrad`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

ALTER TABLE `prog`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `progdec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `progms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

ALTER TABLE `quran`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `qurandec`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `quranms`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

ALTER TABLE `rsch`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `rschdec`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `rschms`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

ALTER TABLE `search`
  MODIFY `id` int(70) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

ALTER TABLE `std`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

ALTER TABLE `std2`
  MODIFY `id_std` int(50) NOT NULL AUTO_INCREMENT;

ALTER TABLE `stdlb`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

ALTER TABLE `student`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

ALTER TABLE `user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

ALTER TABLE `user_adn`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
