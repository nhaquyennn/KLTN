-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 15, 2026 lúc 10:54 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `merge_q`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `allowances_penalties`
--

CREATE TABLE `allowances_penalties` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `type` enum('bonus','penalty') NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reason` text DEFAULT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `allowances_penalties`
--

INSERT INTO `allowances_penalties` (`id`, `teacher_id`, `type`, `amount`, `reason`, `month`, `year`, `created_by`, `created_at`) VALUES
(14, 1, 'penalty', 50000.00, 'Đi trễ 15 phút buổi học ngày 27/10/2025 — class Scratch', 10, 2025, 1, '2026-05-14 09:41:34'),
(15, 1, 'penalty', 200000.00, 'Vắng không phép ngày 19/11/2025, không tìm được người thay', 11, 2025, 1, '2026-05-14 09:41:34'),
(16, 1, 'penalty', 100000.00, 'Đi trễ 20 phút buổi ngày 28/10/2025 — class Scratch', 10, 2025, 1, '2026-05-14 09:41:34'),
(17, 3, 'penalty', 50000.00, 'Đi trễ 10 phút buổi ngày 29/10/2025', 10, 2025, 1, '2026-05-14 09:41:34'),
(18, 2, 'penalty', 999999999.00, 'csadfasdfas', 4, 2026, 1, '2026-05-14 09:41:34'),
(19, 2, 'penalty', 50000.00, 'Đi trễ', 5, 2026, 1, '2026-05-14 09:41:34'),
(20, 3, 'penalty', 200000.00, 'Vắng không báo trước', 5, 2026, 1, '2026-05-14 09:41:34'),
(21, 1, 'penalty', 100000.00, 'Phạt đi trễ 31 phút (Tự động từ điểm danh)', 5, 2026, 1, '2026-05-14 09:41:34'),
(22, 1, 'bonus', 500000.00, 'Thưởng Tết Nguyên Đán 2026 — theo chính sách trung tâm', 1, 2026, 1, '2026-05-14 09:41:34'),
(23, 3, 'bonus', 500000.00, 'Thưởng Tết Nguyên Đán 2026 — theo chính sách trung tâm', 1, 2026, 1, '2026-05-14 09:41:34'),
(24, 2, 'bonus', 2000000.00, 'Thưởng hiệu suất Q1/2026 — hoàn thành xuất sắc KPI', 4, 2026, 1, '2026-05-14 09:41:34'),
(25, 1, 'bonus', 200000.00, 'Thưởng giảng viên mới hoàn thành tốt tháng đầu tiên', 4, 2026, 1, '2026-05-14 09:41:34');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `attendances`
--

CREATE TABLE `attendances` (
  `attendance_id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `attendances`
--

INSERT INTO `attendances` (`attendance_id`, `session_id`, `student_id`, `status`, `created_at`) VALUES
(1, 1, 3, 'present', '2026-05-06 13:08:59'),
(2, 20, 3, 'present', '2026-05-06 15:37:30'),
(3, 3, 3, 'present', '2026-05-06 15:37:32'),
(4, 2, 3, 'present', '2026-05-06 15:37:34'),
(5, 4, 3, 'present', '2026-05-06 15:37:37'),
(6, 5, 3, 'present', '2026-05-06 15:37:39'),
(7, 12, 3, 'present', '2026-05-06 15:37:43'),
(8, 6, 3, 'present', '2026-05-06 15:37:46'),
(9, 7, 3, 'present', '2026-05-06 15:37:48'),
(10, 8, 3, 'present', '2026-05-06 15:37:50'),
(11, 9, 3, 'present', '2026-05-06 15:37:52'),
(12, 10, 3, 'present', '2026-05-06 15:37:54'),
(13, 19, 3, 'present', '2026-05-06 15:37:56'),
(14, 13, 3, 'present', '2026-05-06 15:37:58'),
(15, 14, 3, 'late', '2026-05-06 15:38:02'),
(16, 11, 3, 'late', '2026-05-06 15:38:06'),
(17, 15, 3, 'present', '2026-05-06 15:38:08'),
(18, 16, 3, 'present', '2026-05-06 15:38:12'),
(19, 17, 3, 'present', '2026-05-06 15:38:14'),
(20, 18, 3, 'present', '2026-05-06 15:38:17'),
(21, 41, 4, 'present', '2026-05-08 14:29:05'),
(22, 3, 3, 'present', '2026-05-09 09:02:32');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `classes`
--

CREATE TABLE `classes` (
  `class_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `class_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `classes`
--

INSERT INTO `classes` (`class_id`, `course_id`, `package_id`, `schedule_id`, `shift_id`, `start_date`, `is_active`, `class_code`) VALUES
(1, 1, 1, 1, 3, '2026-05-05', NULL, 'PYT-001'),
(2, 1, 2, 1, 3, '2026-05-05', NULL, 'PYT-002'),
(3, 3, 3, 9, 2, '2026-05-05', NULL, 'ROBO-001'),
(4, 6, 4, 11, 2, '2026-05-06', NULL, 'PYT-001'),
(5, 6, 4, 9, NULL, '2026-05-08', NULL, 'PYT-002'),
(7, 6, NULL, 7, NULL, '2026-05-14', NULL, 'PYT-003');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `courses`
--

INSERT INTO `courses` (`course_id`, `name`, `description`, `status`, `code`) VALUES
(1, 'Python cho người đi làm', '', 'active', 'PYT'),
(2, 'Scratch cơ bản', '', 'active', 'SCR'),
(3, 'Robotics cơ bản', '', 'active', 'ROBO'),
(4, 'Robotics nâng cao', '', 'active', 'ROBO'),
(5, 'Scratch nâng cao', '', 'active', 'SCR'),
(6, 'Python cho trẻ em', '', 'active', 'PYT');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `enrollments`
--

CREATE TABLE `enrollments` (
  `enrollment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `class_id` int(11) DEFAULT NULL,
  `enroll_date` date DEFAULT NULL,
  `status` enum('studying','completed','dropped','paused') DEFAULT NULL,
  `total_fee` decimal(10,2) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT NULL,
  `final_fee` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('unpaid','partial','paid') DEFAULT NULL,
  `attended_sessions` int(11) DEFAULT NULL,
  `remaining_sessions` int(11) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_code` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `enrollments`
--

INSERT INTO `enrollments` (`enrollment_id`, `student_id`, `class_id`, `enroll_date`, `status`, `total_fee`, `discount_percent`, `final_fee`, `paid_amount`, `payment_status`, `attended_sessions`, `remaining_sessions`, `note`, `created_at`, `transaction_code`, `payment_method`, `paid_at`) VALUES
(1, 3, 1, '2026-05-05', 'studying', 3000000.00, NULL, 3000000.00, 3000000.00, 'paid', 21, 20, NULL, '2026-05-04 20:10:12', NULL, NULL, NULL),
(2, NULL, 2, '2026-05-06', 'studying', 2000000.00, 0, 2000000.00, 0.00, 'unpaid', 0, 20, NULL, '2026-05-06 15:56:31', NULL, NULL, NULL),
(3, 4, 3, '2026-05-06', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 1, 20, NULL, '2026-05-06 15:59:51', NULL, NULL, NULL),
(6, 3, 3, '2026-05-08', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 0, 20, NULL, '2026-05-08 16:28:05', '15530584', 'VNPay', '2026-05-09 03:54:07'),
(7, 5, 4, '2026-05-09', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 0, 20, NULL, '2026-05-09 07:12:20', '15531141', 'VNPay', '2026-05-09 17:04:04'),
(8, 1, 4, '2026-05-09', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 0, 20, NULL, '2026-05-09 09:57:34', NULL, NULL, NULL),
(9, 2, 5, '2026-05-10', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 0, 20, NULL, '2026-05-10 13:00:31', NULL, NULL, NULL),
(10, 3, 5, '2026-05-10', 'studying', 2000000.00, 0, 2000000.00, 2000000.00, 'paid', 0, 20, NULL, '2026-05-10 13:10:34', '15532138', 'VNPay', '2026-05-10 20:12:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `face_data`
--

CREATE TABLE `face_data` (
  `face_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `face_embedding` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `face_data`
--

INSERT INTO `face_data` (`face_id`, `user_id`, `image_path`, `face_embedding`, `is_active`, `created_at`) VALUES
(1, 9, '', '[-0.036409666584625376, -0.010247395459068866, 0.004381897320951966, 0.04489362257600238, -0.04940831459324042, -0.027828672808916387, 0.04368140378648608, 0.008737553869698081, 0.07397834197710343, 0.04132118302748458, 0.0801218814118998, -0.07328999903717556, -0.016945103111995635, 0.015329009566038304, -0.05076876956809171, 0.020700238564465526, 0.023639345516616027, -0.005923863602178639, 0.011211166744602272, 0.033079571764240666, -0.031163663554558883, -0.03925301217389234, -0.043069877039269205, -0.04143585818528188, -0.019172354427502467, -0.03984862090966734, -0.004573810644842056, -0.014602249648296668, 0.028650684999228525, 0.053805813064092496, 0.017088607186398586, 0.05336717064583363, 0.01246071934621072, -0.037925501383488734, 0.03695265521458385, -0.006440504111444452, 0.0090301284085545, -0.014493545575377865, 0.020574567857169504, 0.032873050618862, 0.0036050903084667925, -0.007006460780667998, 0.014954149321510742, -0.02897702377765987, -0.04440721951894634, 0.008308009102774745, 0.1066288437638268, -0.0010495713039789273, 0.0069172428754606195, 0.011001528687810964, -0.02074694509127296, -0.014498856382062887, -0.010469293019662746, 0.011423896444169915, 0.022518537039228154, 0.1204290274857678, 0.041053399733087186, -0.03951811126491218, 0.04681303055598203, -0.034115611327567824, 0.005152528374692111, -0.03256482760928673, -0.0272912103473033, -0.11210199134796203, 0.0868639765597682, 0.009235674582406022, -0.03291925148740367, -0.01816244454804632, -0.059932150120453886, -0.038133633692649396, -0.024409652274082566, -0.02968541064679409, -0.01090235053096808, -0.03096133737463347, 0.07029208751628553, -0.06884336144253222, -0.051636825605828836, -0.021806830951618242, 0.005365925396624961, 0.03374062003769098, -0.012014833962636924, -0.05522479319977276, 0.006341922374031486, 0.05476217696281483, -0.030158593344534024, -0.006676763848038584, -0.038094683432376285, -0.03543577747139151, 0.0024202487786596006, 0.0325456178450757, -0.08068737813116503, 0.07331450065613865, 0.017025679143506455, -0.08355872721850774, 0.05143462519166308, 0.03529717454785929, -0.0111207497425117, -0.004993879784134928, -0.0468442807381503, 0.007383673054859752, 0.024645490987161133, -0.0179744006067679, 0.02932400962512655, -0.027157877356547747, 0.0039323124443911015, -0.0484764548259128, 0.05555793867157866, 0.04172068659152692, 0.03241208973148132, 0.048929511995385824, 0.048662994281197934, 0.07273695009058101, 0.0056956763740314284, 0.047610045830086815, -0.0015958298299548238, -0.002520800628243214, -0.005321856789892959, 0.05146696324761462, -0.005979886681867577, 0.03929185546034367, -0.07383884916391056, 0.03141506175423874, 0.041815483840797286, -0.04148205799898547, -0.02800219363818355, 0.05582207199147671, 0.025996162477918687, -0.046518117080210857, -0.0019473023712892668, -0.018027527956442087, -0.051805202488453383, -0.06600457872951832, 0.02877192111265776, -0.06344243191064462, -0.07553967610935723, -0.009367704139461825, -0.027543774496303436, -0.03591780492974418, -0.051802363497658085, -0.01672760987846515, -0.010119726924341424, 0.019912282171049584, -0.036380545286832246, 0.05750323955716959, 0.09997282794389668, -0.05904528632321709, 0.04853356417641672, 0.029587077786021706, -0.03975344111478902, 0.033400941954068265, 0.011018741754188422, 0.011286549868018485, -0.05832882153579279, 0.02367825370831463, -0.02256261962013512, -0.0035671946088556747, 0.0725939103939915, -0.0506438946913509, 0.010225862318282892, 0.01754826541376946, 0.08063804793451494, -0.009854641935896758, 0.008701956353803107, -0.004066566787760074, 0.007363044211764359, 0.04141413099076238, -0.05882485283910118, -0.0031151211653964294, 0.0011938207247311678, -0.008345275653136206, -0.02919645499656979, 0.08314620694291515, -0.03175908965332854, 0.03297572826569685, -0.016530495174824595, 0.015001447745060254, -0.06795465043002977, -0.06630762982591969, -0.05785627943633888, -0.03018847634870298, 0.009729696824869216, -0.041248097045379205, -0.008764774953929, -0.01318183268513491, -0.0335685848954259, -0.04824592736510411, 0.09441558619130695, 0.014905709405673822, 0.010404373605541431, 0.011135015484525855, 0.047936924536014947, -0.004329269108087637, -0.013764005776243439, 0.04139024203141205, -0.06209999502133214, 0.0374882329567451, 0.03861704040645776, -0.0847255812358159, 0.0406362113935688, 0.03972629243141713, 0.011095376747100726, -0.012262645930808797, 0.05511344886895528, 0.08326347681366167, 0.06369641523968587, 0.03521384440522053, -0.07058088027200886, 0.05034925257961341, 0.05974171230470899, -0.07004662883607116, -0.05049502483298383, -0.11483954630867682, -0.0026838952472470583, 0.10919489662340627, 0.009565601586721628, 0.0011084078063295275, -0.07544579393156693, -0.08637052543703577, 0.05397207905201981, -0.01303596919808125, 0.055819626296665745, 0.015726508742741542, -0.016637232379871768, -0.07326764554074876, -0.030704048063842058, 0.025650229722010875, -0.035153440319419735, -0.0022878529831483053, -0.045382422096812265, -0.028942373754613684, 0.0013296289950764973, -0.08420192077595508, 0.03827801086098017, 0.017308879406562623, 0.04171178272847232, 0.004661878810695333, 0.02972392569428179, -0.005647610191045532, -0.03921032858361472, 0.029306466587060043, -0.07018707806900915, -0.013635880873546141, -0.03759453581015918, -0.0105103328044834, 0.002700114390707208, -0.043913964991343285, -0.012798044787343466, 0.007778444061363465, 0.003600748319381046, -0.04916338941735508, 0.05039539090296245, 0.03632569029503819, 0.000795443547935178, -0.05581353068219371, 0.028634654344117993, 0.06782659773039253, 0.0031666890177852475, 0.013946867261219055, 0.0535554417449113, 0.0341857228936827, -0.05106740948447593, -0.08037279081461296, -0.03414744352932382, 0.0782540681095006, -0.00602674803265467, 0.044709081896764154, 0.01584987803287075, -0.026908782764929733, -0.010412661413729657, 0.0026885543642310764, 0.005306240256322902, -0.046279260325582335, -0.011369377894530842, -0.05639207847797714, -0.0199987881162025, 0.0288738274400216, -0.08700366882956842, 0.05263868127047467, 0.06446863770496007, 0.03388407393320397, 0.0652290636605571, -0.0503357295163901, 0.0624419086470664, -0.009474478320908048, 0.008427734501181977, 0.0297974043929712, 0.10786307105781347, -0.06527067285447126, 0.014517123332353837, 0.0002604377018940002, -0.030750833927415337, 0.025073355543970083, -0.0021181836360890383, 0.01199836944338151, -0.02437357706387983, -0.031039570405342314, 0.049017400683722664, 0.013975405009547729, -0.08301789254835464, 0.04140428667511625, -0.0025411344262965692, 0.014720244076433334, 0.03842485416634677, -0.0625459621220429, -0.09731480309185361, 0.0407629748113073, -0.018191613106380428, -0.044748795621033906, 0.00023537051817804273, -0.04235874544372687, -0.038350182667670665, -0.02070162096676633, 0.025873370908014535, 0.07754477361911055, -0.013734348162088605, 0.07959555165887082, -0.04042930714223152, -0.029618882575205722, 0.01122878600430943, -0.026652332522583224, -0.013424367498086957, 0.031903167447310865, -0.04965214268178222, 0.07613690080988303, -0.07143410549245437, 0.02635204375414625, -0.05264991117504507, 0.047138005970866516, 0.0011199676565413976, -0.06666498147307816, -0.11359453509566562, -0.00560897416898248, 0.027784947358862496, -0.06473690445777538, -0.08461055912249346, -0.06005807741626238, 0.02662417776821824, 0.043789355788654685, -0.0421007836486886, 0.03899680817787613, -0.0255800195947138, -0.005614934749530195, 0.0072160540403057374, -0.02155945217106104, -0.015896137951149917, -0.017061102805127223, 0.006478661201623084, -0.0054978042729876275, -0.027389037168433366, 0.01387884128917983, 0.08259254232111266, 0.007251306940344061, 0.044930763206777934, 0.019873596192483037, -0.01927433828142135, -0.08490151369975585, -0.021958867162990572, 0.017080946142710135, -0.003235428670389929, 0.018380681736861235, -0.02470855212947607, -0.08819949985710492, 0.06632239487914769, 0.019347812279693854, 0.009360274844476666, -0.059389003726968245, -0.0755268654311301, 0.04409262093311232, 0.041054548153821896, 0.031006627233131603, -0.08521300782104703, -0.021860164413019828, -0.04777831537104372, -0.05111849751891005, -0.025017151173069425, -0.04550957816879861, -0.06361674107272258, -0.0505623597583012, -0.0545939674298004, 0.09135143924351705, -0.0377482049637395, -0.029251948482756487, 0.03037980532041589, 0.020379296490528383, -0.02713423877799717, 0.002026197854269802, 0.04440075776180705, -0.04141654092848093, 0.013168095878529065, 0.03890668379690036, -0.08210777792417201, 0.009422168247962966, 0.0033394366259340527, -0.016398282538272135, 0.02011108098826975, 0.015738275356260524, -0.01385175814318463, 0.016082814378377375, -0.00849418873132837, 0.04364563948737604, -0.056036093379381854, -0.0013829197968654213, -0.03548854069325953, 0.0031536572355389874, 0.00701447185877442, 0.10543548683027973, 0.005162877573368376, -0.019340833363838357, -0.049049087391215544, -0.12785735583602365, 0.021278424187277243, 0.003670194609490257, 0.04040159583121405, -0.038995615855471615, -0.027514781998914054, -0.030681256308605492, 0.05408070262250427, 0.004706133855262212, 0.012289760290838479, 0.08340481594690027, -0.03016044556026193, -0.03881274266520003, -0.046533501124406296, 0.0861665792214574, 0.06500052743161741, 0.02655169602371908, -0.05013724228271415, 0.0483406716736701, -0.018696866564477756, 0.038294385066076395, -0.02876726964043497, -0.034850398780802753, -0.049793501879185846, -0.015329484396312496, 0.007476956236235827, -0.04747454455598508, 0.02474425731987727, 0.020146865536265816, -0.0696359158412474, -0.030891780503992047, 0.0417296599877, -0.009024949331995014, -0.015300772093408896, -0.025205897319982993, 0.07272009369504505, -0.0633193091885231, -0.07136040001148793, -0.08028081204925804, -0.01839104573188073, -0.028182524798864128, 0.023631316418662132, 0.020815883721182094, -0.052012067543857614, -0.01116383402894463, -0.07639318850545078, -0.04271142276641443, 0.03681370818767642, -0.10387598896289758, -0.011012932157743783, -0.00015342261278826163, -0.028320187760573108, -0.015320463838206809, -0.0431683604365257, 0.06519880094736434, 0.007363585930718138, 0.06748266676967633, -0.03742235175522903, 0.05158099030691616, 0.007306256488251116, -0.03529276649729714, 0.01385020283399339, 0.022661627217240493, 0.033361489857637656, 0.05445688392856593, -0.0025784713159386643, -0.060958237679256046, -0.06158250309639767, -0.01484979992511635, 0.04446912717273764, -0.04210632609327546, -0.0034589931957329455, -0.03727903855923781, -0.005920706750992951, 0.045481681087191396, 0.02557650396993178, 0.006729512192469337, 0.023953048019280106, 0.04379231627512888, -0.05792496709547305, 0.007906697833702452, 0.00017621328925930183, 0.13505184575829576, 0.016723522321503065, 0.02493321907624819, -0.050064332955187626, 0.0609452002684838, -0.05752760390120314, -0.010125889826289624, -0.07959297929019299, 0.009673953973194436, 0.02256519399640164, 0.05438350539763403, -0.003626701661015455, 0.032071010858400036, -0.059965319900651536, 0.06312736704486714, 0.020984459990940764, -0.03242988494515878, 0.05168663733713225, -0.06164660123749385, -0.021107988987387794, -0.030533515221518755]', 1, '2026-05-11 19:02:51');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `packages`
--

CREATE TABLE `packages` (
  `package_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `total_sessions` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `packages`
--

INSERT INTO `packages` (`package_id`, `course_id`, `name`, `total_sessions`, `price`, `status`) VALUES
(1, 1, 'Nền tảng', 20, 3000000.00, 'active'),
(2, 1, 'Thực chiến', 20, 2000000.00, 'active'),
(3, 3, 'Level 1 ', 20, 2000000.00, 'active'),
(4, 6, 'Level   1', 20, 2000000.00, 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `parents`
--

CREATE TABLE `parents` (
  `parent_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `parent_student`
--

CREATE TABLE `parent_student` (
  `id` int(11) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `payroll_current_month`
--

CREATE TABLE `payroll_current_month` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `salary_type` enum('per_session','fixed') NOT NULL,
  `level_name` varchar(50) DEFAULT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_sessions` int(11) DEFAULT 0,
  `late_sessions` int(11) DEFAULT 0,
  `absent_sessions` int(11) DEFAULT 0,
  `base_salary` decimal(12,2) DEFAULT 0.00,
  `total_bonus` decimal(12,2) DEFAULT 0.00,
  `total_penalty` decimal(12,2) DEFAULT 0.00,
  `final_salary` decimal(12,2) DEFAULT 0.00,
  `status` enum('draft','confirmed','paid') DEFAULT 'draft',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `rooms`
--

INSERT INTO `rooms` (`room_id`, `name`, `capacity`, `status`, `created_at`) VALUES
(1, 'Phòng 3.01', 15, 'inactive', '2026-05-04 20:01:11'),
(2, 'Phòng 2.02', 8, 'active', '2026-05-04 20:01:11'),
(3, 'Phòng 2.01', 8, 'inactive', '2026-05-04 20:01:11'),
(6, 'Phòng 1.03', 10, 'active', '2026-05-04 20:01:11'),
(9, 'Phòng 1.02', 10, 'active', '2026-05-04 20:01:11'),
(10, 'Phòng 1.01', 10, 'active', '2026-05-04 20:01:11'),
(11, 'Phòng 3.02', 8, 'active', '2026-05-08 12:13:16');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `salary_levels`
--

CREATE TABLE `salary_levels` (
  `id` int(11) NOT NULL,
  `type` enum('per_session','monthly') NOT NULL,
  `level` int(11) NOT NULL,
  `level_name` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `requirement_sessions` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `effective_from` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `salary_levels`
--

INSERT INTO `salary_levels` (`id`, `type`, `level`, `level_name`, `amount`, `requirement_sessions`, `is_active`, `effective_from`, `created_at`) VALUES
(1, 'per_session', 1, 'bậc 1 - cơ bản', 50000.00, 0, 1, '0000-00-00', '2026-05-14 10:46:35'),
(2, 'per_session', 2, 'bậc 2 - trung cấp', 100000.00, 30, 1, '0000-00-00', '2026-05-14 10:46:35'),
(3, 'per_session', 3, 'bậc 3 - khá', 120000.00, 60, 1, '0000-00-00', '2026-05-14 10:46:35'),
(4, 'per_session', 4, 'bậc 4 - thành thạo', 140000.00, 90, 1, '0000-00-00', '2026-05-14 10:46:35'),
(5, 'per_session', 5, 'bậc 5 - già dặn', 160000.00, 120, 1, '0000-00-00', '2026-05-14 10:46:35'),
(6, 'monthly', 6, 'kiểm toán', 8000000.00, 0, 1, '2026-04-20', '2026-05-14 10:46:35');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `schedules`
--

CREATE TABLE `schedules` (
  `schedule_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `schedules`
--

INSERT INTO `schedules` (`schedule_id`, `name`, `code`, `status`) VALUES
(1, 'T2-T4', '2-4', 'active'),
(2, 'T3-T5', '3-5', 'active'),
(3, 'T2-T5', '2-5', 'active'),
(4, 'T3-T4-T6', '3-4-6', 'active'),
(5, 'T7-CN', '7-1', 'active'),
(6, 'T6-T7-CN', '6-7-1', 'active'),
(7, 'T2-T3', '2-3', 'active'),
(8, 'T2-T6', '2-6', 'active'),
(9, 'T2-T7', '2,7', 'active'),
(11, 'T3-T7', '3,7', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `schedule_days`
--

CREATE TABLE `schedule_days` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `day_of_week` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `schedule_days`
--

INSERT INTO `schedule_days` (`id`, `schedule_id`, `day_of_week`) VALUES
(1, 1, 2),
(2, 1, 4),
(3, 2, 3),
(4, 2, 5),
(5, 3, 2),
(6, 3, 5),
(7, 4, 3),
(8, 4, 4),
(9, 4, 6),
(10, 5, 7),
(11, 5, 1),
(12, 6, 6),
(13, 6, 7),
(14, 6, 1),
(15, 7, 2),
(16, 7, 3),
(17, 8, 2),
(18, 8, 6),
(19, 9, 2),
(20, 9, 7),
(23, 11, 3),
(24, 11, 7);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `session_date` date DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `sessions`
--

INSERT INTO `sessions` (`session_id`, `class_id`, `session_date`, `shift_id`, `status`, `note`, `room_id`) VALUES
(1, 1, '2026-05-06', 3, 'done', NULL, 10),
(2, 1, '2026-05-11', 3, 'conflict', 'Room inactive', NULL),
(3, 1, '2026-05-13', 3, 'conflict', 'Room inactive', NULL),
(4, 1, '2026-05-18', 3, 'done', NULL, NULL),
(5, 1, '2026-05-20', 3, 'done', NULL, NULL),
(6, 1, '2026-05-25', 3, 'done', NULL, NULL),
(7, 1, '2026-05-27', 3, 'done', NULL, NULL),
(8, 1, '2026-06-01', 3, 'done', NULL, NULL),
(9, 1, '2026-06-03', 3, 'done', NULL, NULL),
(10, 1, '2026-06-08', 3, 'done', NULL, NULL),
(11, 1, '2026-06-10', 3, 'done', NULL, NULL),
(12, 1, '2026-06-15', 3, 'done', NULL, NULL),
(13, 1, '2026-06-17', 3, 'done', NULL, NULL),
(14, 1, '2026-06-22', 3, 'done', NULL, NULL),
(15, 1, '2026-06-24', 3, 'done', NULL, NULL),
(16, 1, '2026-06-29', 3, 'done', NULL, NULL),
(17, 1, '2026-07-01', 3, 'done', NULL, NULL),
(18, 1, '2026-07-06', 3, 'done', NULL, NULL),
(19, 1, '2026-07-08', 3, 'done', NULL, NULL),
(20, 1, '2026-07-13', 3, 'done', NULL, NULL),
(21, 2, '2026-05-04', 3, 'scheduled', NULL, NULL),
(22, 2, '2026-05-06', 3, 'scheduled', 'conflict', NULL),
(23, 2, '2026-05-11', 3, 'scheduled', 'conflict', NULL),
(24, 2, '2026-05-13', 3, 'scheduled', 'conflict', NULL),
(25, 2, '2026-05-18', 3, 'scheduled', 'conflict', NULL),
(26, 2, '2026-05-20', 3, 'scheduled', 'conflict', NULL),
(27, 2, '2026-05-25', 3, 'scheduled', 'conflict', NULL),
(28, 2, '2026-05-27', 3, 'scheduled', 'conflict', NULL),
(29, 2, '2026-06-01', 3, 'scheduled', 'conflict', NULL),
(30, 2, '2026-06-03', 3, 'scheduled', 'conflict', NULL),
(31, 2, '2026-06-08', 3, 'scheduled', 'conflict', NULL),
(32, 2, '2026-06-10', 3, 'scheduled', 'conflict', NULL),
(33, 2, '2026-06-15', 3, 'scheduled', 'conflict', NULL),
(34, 2, '2026-06-17', 3, 'scheduled', 'conflict', NULL),
(35, 2, '2026-06-22', 3, 'scheduled', 'conflict', NULL),
(36, 2, '2026-06-24', 3, 'scheduled', 'conflict', NULL),
(37, 2, '2026-06-29', 3, 'scheduled', 'conflict', NULL),
(38, 2, '2026-07-01', 3, 'scheduled', 'conflict', NULL),
(39, 2, '2026-07-06', 3, 'scheduled', 'conflict', NULL),
(40, 2, '2026-07-08', 3, 'scheduled', 'conflict', NULL),
(41, 3, '2026-05-09', 2, 'done', NULL, 10),
(42, 3, '2026-05-11', 5, 'scheduled', NULL, NULL),
(43, 3, '2026-05-16', 2, 'scheduled', NULL, NULL),
(44, 3, '2026-05-18', 2, 'scheduled', NULL, NULL),
(45, 3, '2026-05-23', 2, 'scheduled', NULL, NULL),
(46, 5, '2026-05-11', 5, 'scheduled', NULL, 11),
(47, 5, '2026-05-16', 3, 'scheduled', NULL, 11),
(48, 5, '2026-05-18', 5, 'scheduled', NULL, 11);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `session_teachers`
--

CREATE TABLE `session_teachers` (
  `id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `role` enum('main','assistant') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `session_teachers`
--

INSERT INTO `session_teachers` (`id`, `session_id`, `teacher_id`, `role`) VALUES
(1, 2, 1, 'main'),
(2, 1, 1, 'main'),
(3, 41, 1, 'assistant'),
(4, 3, 1, 'main');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `shifts`
--

INSERT INTO `shifts` (`shift_id`, `name`, `start_time`, `end_time`) VALUES
(1, 'Sáng', '09:00:00', '10:30:00'),
(2, 'Chiều', '14:30:00', '16:00:00'),
(3, 'Tối 2', '18:30:00', '20:00:00'),
(5, 'Tối 1', '19:30:00', '21:00:00');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `specializations`
--

CREATE TABLE `specializations` (
  `specialization_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `specializations`
--

INSERT INTO `specializations` (`specialization_id`, `name`, `description`, `status`, `created_at`) VALUES
(1, 'Robotics', 'Lập trình và điều khiển robot cho học sinh', 'active', '2026-05-08 14:03:46'),
(2, 'Scratch', 'Lập trình Scratch cơ bản cho trẻ em', 'active', '2026-05-08 14:03:46'),
(3, 'Python', 'Lập trình Python từ cơ bản đến nâng cao', 'active', '2026-05-08 14:03:46'),
(5, 'STEM', 'Giảng dạy STEM tích hợp', 'active', '2026-05-08 14:03:46'),
(6, 'Arduino', 'Lập trình vi điều khiển Arduino', 'active', '2026-05-08 14:03:46'),
(7, 'Web Development', 'Thiết kế và lập trình website', 'active', '2026-05-08 14:03:46'),
(8, 'Game Development', 'Thiết kế và phát triển game', 'active', '2026-05-08 14:03:46'),
(9, 'IoT', 'Internet of Things và thiết bị thông minh', 'active', '2026-05-08 14:03:46'),
(13, 'C/C++', 'Lập trình C/C++ cho thuật toán và nhúng', 'active', '2026-05-08 14:03:46'),
(14, 'Java', 'Lập trình Java hướng đối tượng', 'active', '2026-05-08 14:03:46'),
(15, 'Tin học văn phòng', 'Word, Excel, PowerPoint cơ bản', 'active', '2026-05-08 14:03:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `parent_name` varchar(255) DEFAULT NULL,
  `parent_phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `parent_name`, `parent_phone`, `date_of_birth`, `status`) VALUES
(1, 4, 'Phạm Văn Hùng', '0912998877', '2010-05-20', 1),
(2, 5, 'Nguyễn Thị Tuyết', '0903445566', '2012-11-12', 1),
(3, 6, '', NULL, '2004-02-05', 1),
(4, 7, '', NULL, '2015-02-27', 1),
(5, 9, 'Trần Trúc Trân', NULL, '2018-05-09', 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teachers`
--

CREATE TABLE `teachers` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary_type` enum('per_session','fixed') DEFAULT NULL,
  `current_level_id` int(11) DEFAULT NULL,
  `salary_value` decimal(10,2) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `specialization_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `teachers`
--

INSERT INTO `teachers` (`teacher_id`, `user_id`, `hire_date`, `salary_type`, `current_level_id`, `salary_value`, `status`, `specialization_id`) VALUES
(1, 2, '2024-02-01', 'per_session', 6, 250000.00, 1, 3),
(2, 3, '2024-03-15', 'fixed', 5, 18000000.00, 1, 15),
(3, 8, '2026-05-08', 'per_session', 1, 100000.00, 1, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_attendance`
--

CREATE TABLE `teacher_attendance` (
  `attendance_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `session_date` date DEFAULT NULL,
  `check_in_time` datetime DEFAULT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('present','absent','late') DEFAULT 'present',
  `method` tinyint(1) DEFAULT 1,
  `face_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_level_history`
--

CREATE TABLE `teacher_level_history` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `from_level_id` int(11) DEFAULT NULL,
  `to_level_id` int(11) NOT NULL,
  `sessions_at_upgrade` int(11) DEFAULT 0,
  `upgraded_at` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `teacher_level_history`
--

INSERT INTO `teacher_level_history` (`id`, `teacher_id`, `from_level_id`, `to_level_id`, `sessions_at_upgrade`, `upgraded_at`, `created_at`) VALUES
(1, 1, 1, 2, 30, '2025-11-06', '2026-05-14 12:26:27');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `teacher_salary_logs`
--

CREATE TABLE `teacher_salary_logs` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `level_id` int(11) DEFAULT NULL,
  `total_sessions` int(11) DEFAULT 0,
  `late_sessions` int(11) DEFAULT 0,
  `absent_sessions` int(11) DEFAULT 0,
  `base_salary` decimal(12,2) DEFAULT 0.00,
  `total_bonus` decimal(12,2) DEFAULT 0.00,
  `total_penalty` decimal(12,2) DEFAULT 0.00,
  `final_salary` decimal(12,2) DEFAULT 0.00,
  `status` enum('draft','confirmed','paid') DEFAULT 'draft',
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `confirmed_at` datetime DEFAULT NULL,
  `confirmed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','teacher','parent','student') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `phone`, `status`, `created_at`) VALUES
(1, 'Nguyễn Minh Quân', 'quan.admin@center.com', '123456', 'admin', '0905123456', 1, '2026-05-04 20:03:35'),
(2, 'Trần Thị Thu Thảo', 'thao.teacher@center.com', '123456', 'teacher', '0914223344', 1, '2026-05-04 20:03:35'),
(3, 'Lê Hoàng Nam', 'nam.teacher@center.com', 'pass_secret_3', 'teacher', '0988556677', 1, '2026-05-04 20:03:35'),
(4, 'Phạm Anh Tuấn', 'tuan.student@center.com', '123456', 'student', '0355889900', 1, '2026-05-04 20:03:35'),
(5, 'Vũ Hải Yến', 'yen.student@center.com', 'pass_secret_5', 'student', '0700112233', 1, '2026-05-04 20:03:35'),
(6, 'Mai Trí Thức ', NULL, NULL, NULL, '025647898', NULL, '2026-05-04 20:09:53'),
(7, 'An Thuyên', NULL, NULL, NULL, '025698453', NULL, '2026-05-06 15:57:03'),
(8, 'Vũ Văn Trung', 'trung.teacher@center.com', NULL, 'teacher', NULL, NULL, '2026-05-08 14:30:48'),
(9, 'Trần Thị Tú', NULL, NULL, NULL, '0225566998', NULL, '2026-05-09 07:11:07');

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `vw_teacher_attendance_report`
-- (See below for the actual view)
--
CREATE TABLE `vw_teacher_attendance_report` (
`attendance_id` int(11)
,`teacher_id` int(11)
,`teacher_name` varchar(100)
,`session_id` int(11)
,`class_name` varchar(50)
,`check_in_time` datetime
,`check_out_time` datetime
,`session_date` date
,`start_time` time
,`end_time` time
,`shift_start_time` datetime
,`late_minutes_calculated` bigint(21)
,`status_calculated` varchar(7)
,`face_image` varchar(255)
,`created_at` timestamp
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_payroll_current_month`
-- (See below for the actual view)
--
CREATE TABLE `v_payroll_current_month` (
`id` int(11)
,`name` varchar(100)
,`salary_type` enum('per_session','fixed')
,`level_name` varchar(50)
,`month` int(11)
,`year` int(11)
,`total_sessions` int(11)
,`late_sessions` int(11)
,`absent_sessions` int(11)
,`base_salary` decimal(12,2)
,`total_bonus` decimal(12,2)
,`total_penalty` decimal(12,2)
,`final_salary` decimal(12,2)
,`status` enum('draft','confirmed','paid')
);

-- --------------------------------------------------------

--
-- Cấu trúc đóng vai cho view `v_teacher_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_teacher_summary` (
`teacher_id` int(11)
,`name` varchar(100)
,`salary_type` enum('per_session','fixed')
,`current_level` varchar(50)
,`current_rate` decimal(12,2)
,`total_present` bigint(21)
,`total_late` bigint(21)
,`total_absent` bigint(21)
,`sessions_to_next_level` bigint(22)
);

-- --------------------------------------------------------

--
-- Cấu trúc cho view `vw_teacher_attendance_report`
--
DROP TABLE IF EXISTS `vw_teacher_attendance_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_teacher_attendance_report`  AS SELECT `ta`.`attendance_id` AS `attendance_id`, `ta`.`teacher_id` AS `teacher_id`, `u`.`name` AS `teacher_name`, `ta`.`session_id` AS `session_id`, `c`.`class_code` AS `class_name`, `ta`.`check_in_time` AS `check_in_time`, `ta`.`check_out_time` AS `check_out_time`, `s`.`session_date` AS `session_date`, `sh`.`start_time` AS `start_time`, `sh`.`end_time` AS `end_time`, timestamp(`s`.`session_date`,`sh`.`start_time`) AS `shift_start_time`, CASE WHEN `ta`.`check_in_time` is null THEN NULL WHEN `ta`.`check_in_time` <= timestamp(`s`.`session_date`,`sh`.`start_time`) THEN 0 ELSE timestampdiff(MINUTE,timestamp(`s`.`session_date`,`sh`.`start_time`),`ta`.`check_in_time`) END AS `late_minutes_calculated`, CASE WHEN `ta`.`check_in_time` is null THEN 'absent' WHEN `ta`.`check_in_time` <= timestamp(`s`.`session_date`,`sh`.`start_time`) THEN 'present' ELSE 'late' END AS `status_calculated`, `ta`.`face_image` AS `face_image`, `ta`.`created_at` AS `created_at` FROM (((((`teacher_attendance` `ta` join `teachers` `t` on(`t`.`teacher_id` = `ta`.`teacher_id`)) join `users` `u` on(`u`.`user_id` = `t`.`user_id`)) join `sessions` `s` on(`s`.`session_id` = `ta`.`session_id`)) join `shifts` `sh` on(`sh`.`shift_id` = `s`.`shift_id`)) join `classes` `c` on(`c`.`class_id` = `ta`.`class_id`)) ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_payroll_current_month`
--
DROP TABLE IF EXISTS `v_payroll_current_month`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_payroll_current_month`  AS SELECT `tsl`.`id` AS `id`, `u`.`name` AS `name`, `t`.`salary_type` AS `salary_type`, `sl`.`level_name` AS `level_name`, `tsl`.`month` AS `month`, `tsl`.`year` AS `year`, `tsl`.`total_sessions` AS `total_sessions`, `tsl`.`late_sessions` AS `late_sessions`, `tsl`.`absent_sessions` AS `absent_sessions`, `tsl`.`base_salary` AS `base_salary`, `tsl`.`total_bonus` AS `total_bonus`, `tsl`.`total_penalty` AS `total_penalty`, `tsl`.`final_salary` AS `final_salary`, `tsl`.`status` AS `status` FROM (((`teacher_salary_logs` `tsl` join `teachers` `t` on(`t`.`teacher_id` = `tsl`.`teacher_id`)) join `users` `u` on(`u`.`user_id` = `t`.`user_id`)) join `salary_levels` `sl` on(`sl`.`id` = `tsl`.`level_id`)) WHERE `tsl`.`month` = month(curdate()) AND `tsl`.`year` = year(curdate()) ;

-- --------------------------------------------------------

--
-- Cấu trúc cho view `v_teacher_summary`
--
DROP TABLE IF EXISTS `v_teacher_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_teacher_summary`  AS SELECT `t`.`teacher_id` AS `teacher_id`, `u`.`name` AS `name`, `t`.`salary_type` AS `salary_type`, `sl`.`level_name` AS `current_level`, `sl`.`amount` AS `current_rate`, count(case when `ta`.`status` = 'present' then 1 end) AS `total_present`, count(case when `ta`.`status` = 'late' then 1 end) AS `total_late`, count(case when `ta`.`status` = 'absent' then 1 end) AS `total_absent`, coalesce(`next_lvl`.`requirement_sessions` - count(case when `ta`.`status` = 'present' then 1 end),0) AS `sessions_to_next_level` FROM ((((`teachers` `t` join `users` `u` on(`u`.`user_id` = `t`.`user_id`)) join `salary_levels` `sl` on(`sl`.`id` = `t`.`current_level_id`)) left join `teacher_attendance` `ta` on(`ta`.`teacher_id` = `t`.`teacher_id`)) left join `salary_levels` `next_lvl` on(`next_lvl`.`type` = `t`.`salary_type` and `next_lvl`.`level` = `sl`.`level` + 1 and `next_lvl`.`is_active` = 1)) GROUP BY `t`.`teacher_id`, `u`.`name`, `t`.`salary_type`, `sl`.`level_name`, `sl`.`amount`, `next_lvl`.`requirement_sessions` ;

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `allowances_penalties`
--
ALTER TABLE `allowances_penalties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_allowances_teacher` (`teacher_id`),
  ADD KEY `fk_allowances_created_by` (`created_by`);

--
-- Chỉ mục cho bảng `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `fk_attendances_sessions` (`session_id`),
  ADD KEY `fk_attendances_students` (`student_id`);

--
-- Chỉ mục cho bảng `classes`
--
ALTER TABLE `classes`
  ADD PRIMARY KEY (`class_id`),
  ADD KEY `fk_classes_courses` (`course_id`),
  ADD KEY `fk_classes_packages` (`package_id`),
  ADD KEY `fk_classes_schedules` (`schedule_id`),
  ADD KEY `fk_classes_shifts` (`shift_id`);

--
-- Chỉ mục cho bảng `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Chỉ mục cho bảng `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`enrollment_id`),
  ADD KEY `fk_enrollments_students` (`student_id`),
  ADD KEY `fk_enrollments_classes` (`class_id`);

--
-- Chỉ mục cho bảng `face_data`
--
ALTER TABLE `face_data`
  ADD PRIMARY KEY (`face_id`),
  ADD KEY `fk_face_data_user` (`user_id`);

--
-- Chỉ mục cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Chỉ mục cho bảng `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `fk_packages_courses` (`course_id`);

--
-- Chỉ mục cho bảng `parents`
--
ALTER TABLE `parents`
  ADD PRIMARY KEY (`parent_id`),
  ADD KEY `fk_parents_user` (`user_id`);

--
-- Chỉ mục cho bảng `parent_student`
--
ALTER TABLE `parent_student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_parent_student_parent` (`parent_id`),
  ADD KEY `idx_parent_student_student` (`student_id`);

--
-- Chỉ mục cho bảng `payroll_current_month`
--
ALTER TABLE `payroll_current_month`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payroll_current_teacher` (`teacher_id`,`month`,`year`);

--
-- Chỉ mục cho bảng `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Chỉ mục cho bảng `salary_levels`
--
ALTER TABLE `salary_levels`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`schedule_id`);

--
-- Chỉ mục cho bảng `schedule_days`
--
ALTER TABLE `schedule_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sd_schedules` (`schedule_id`);

--
-- Chỉ mục cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `fk_sessions_classes` (`class_id`),
  ADD KEY `fk_sessions_shifts` (`shift_id`),
  ADD KEY `fk_sessions_rooms` (`room_id`);

--
-- Chỉ mục cho bảng `session_teachers`
--
ALTER TABLE `session_teachers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_st_sessions` (`session_id`),
  ADD KEY `fk_st_teachers` (`teacher_id`);

--
-- Chỉ mục cho bảng `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`);

--
-- Chỉ mục cho bảng `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`specialization_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Chỉ mục cho bảng `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `fk_students_users` (`user_id`);

--
-- Chỉ mục cho bảng `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`teacher_id`),
  ADD KEY `fk_teachers_users` (`user_id`),
  ADD KEY `fk_teacher_specialization` (`specialization_id`),
  ADD KEY `fk_teachers_level` (`current_level_id`);

--
-- Chỉ mục cho bảng `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `fk_teacher_attendance_class` (`class_id`),
  ADD KEY `idx_teacher_attendance_teacher` (`teacher_id`),
  ADD KEY `idx_teacher_attendance_session` (`session_id`),
  ADD KEY `idx_teacher_attendance_checkin` (`check_in_time`);

--
-- Chỉ mục cho bảng `teacher_level_history`
--
ALTER TABLE `teacher_level_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teacher_level_history_teacher` (`teacher_id`),
  ADD KEY `fk_teacher_level_history_from` (`from_level_id`),
  ADD KEY `fk_teacher_level_history_to` (`to_level_id`);

--
-- Chỉ mục cho bảng `teacher_salary_logs`
--
ALTER TABLE `teacher_salary_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_teacher_salary_logs_level` (`level_id`),
  ADD KEY `fk_teacher_salary_logs_confirmed_by` (`confirmed_by`),
  ADD KEY `idx_teacher_salary_logs_month` (`teacher_id`,`month`,`year`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `allowances_penalties`
--
ALTER TABLE `allowances_penalties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `attendances`
--
ALTER TABLE `attendances`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `classes`
--
ALTER TABLE `classes`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `enrollment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `face_data`
--
ALTER TABLE `face_data`
  MODIFY `face_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `packages`
--
ALTER TABLE `packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `parents`
--
ALTER TABLE `parents`
  MODIFY `parent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `parent_student`
--
ALTER TABLE `parent_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `payroll_current_month`
--
ALTER TABLE `payroll_current_month`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `salary_levels`
--
ALTER TABLE `salary_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `schedules`
--
ALTER TABLE `schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `schedule_days`
--
ALTER TABLE `schedule_days`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT cho bảng `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT cho bảng `session_teachers`
--
ALTER TABLE `session_teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `specializations`
--
ALTER TABLE `specializations`
  MODIFY `specialization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT cho bảng `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `teachers`
--
ALTER TABLE `teachers`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `teacher_level_history`
--
ALTER TABLE `teacher_level_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `teacher_salary_logs`
--
ALTER TABLE `teacher_salary_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `allowances_penalties`
--
ALTER TABLE `allowances_penalties`
  ADD CONSTRAINT `fk_allowances_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_allowances_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `fk_attendances_sessions` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`),
  ADD CONSTRAINT `fk_attendances_students` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Các ràng buộc cho bảng `classes`
--
ALTER TABLE `classes`
  ADD CONSTRAINT `fk_classes_courses` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `fk_classes_packages` FOREIGN KEY (`package_id`) REFERENCES `packages` (`package_id`),
  ADD CONSTRAINT `fk_classes_schedules` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`),
  ADD CONSTRAINT `fk_classes_shifts` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`);

--
-- Các ràng buộc cho bảng `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enrollments_classes` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_enrollments_students` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Các ràng buộc cho bảng `face_data`
--
ALTER TABLE `face_data`
  ADD CONSTRAINT `fk_face_data_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `fk_packages_courses` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Các ràng buộc cho bảng `parents`
--
ALTER TABLE `parents`
  ADD CONSTRAINT `fk_parents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `parent_student`
--
ALTER TABLE `parent_student`
  ADD CONSTRAINT `fk_parent_student_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_parent_student_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `payroll_current_month`
--
ALTER TABLE `payroll_current_month`
  ADD CONSTRAINT `fk_payroll_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `schedule_days`
--
ALTER TABLE `schedule_days`
  ADD CONSTRAINT `fk_sd_schedules` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`schedule_id`);

--
-- Các ràng buộc cho bảng `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `fk_sessions_classes` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`),
  ADD CONSTRAINT `fk_sessions_rooms` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`),
  ADD CONSTRAINT `fk_sessions_shifts` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`);

--
-- Các ràng buộc cho bảng `session_teachers`
--
ALTER TABLE `session_teachers`
  ADD CONSTRAINT `fk_st_sessions` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`),
  ADD CONSTRAINT `fk_st_teachers` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`);

--
-- Các ràng buộc cho bảng `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `teachers`
--
ALTER TABLE `teachers`
  ADD CONSTRAINT `fk_teacher_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`specialization_id`),
  ADD CONSTRAINT `fk_teachers_level` FOREIGN KEY (`current_level_id`) REFERENCES `salary_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_teachers_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Các ràng buộc cho bảng `teacher_attendance`
--
ALTER TABLE `teacher_attendance`
  ADD CONSTRAINT `fk_teacher_attendance_class` FOREIGN KEY (`class_id`) REFERENCES `classes` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher_attendance_session` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher_attendance_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_level_history`
--
ALTER TABLE `teacher_level_history`
  ADD CONSTRAINT `fk_teacher_level_history_from` FOREIGN KEY (`from_level_id`) REFERENCES `salary_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_teacher_level_history_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_teacher_level_history_to` FOREIGN KEY (`to_level_id`) REFERENCES `salary_levels` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `teacher_salary_logs`
--
ALTER TABLE `teacher_salary_logs`
  ADD CONSTRAINT `fk_teacher_salary_logs_confirmed_by` FOREIGN KEY (`confirmed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_teacher_salary_logs_level` FOREIGN KEY (`level_id`) REFERENCES `salary_levels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_teacher_salary_logs_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`teacher_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
