<?php
$role = $_SESSION['user']['role'] ?? null;
$userName = $_SESSION['user']['name'] ?? 'User';

$currentModule = $_GET['module'] ?? 'dashboard';
$currentAction = $_GET['action'] ?? 'index';
?>

<!-- ===== SIDEBAR ===== -->
<div id="sidebar" class="active">

    <div class="sidebar-wrapper active">

        <!-- HEADER -->
        <div class="sidebar-header">
            <div class="d-flex justify-content-between">

                <div class="logo">
                    <a href="index.php">
                        <img src="assets/images/logo/logo3c.png" alt="Logo">
                    </a>
                </div>

            </div>

            <div class="sidebar-user mt-3">
                <div class="small text-muted">Xin chào</div>
                <div class="fw-bold text-primary">
                    <?= htmlspecialchars($userName) ?>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <div class="sidebar-menu">

            <ul class="menu">

                <!-- ========================= -->
                <!-- TEACHER VIEW (CHỈ HIỂN THỊ 2-3 MENU) -->
                <!-- ========================= -->
                <?php if ($role === 'teacher'): ?>

                    <li class="sidebar-title">Giảng dạy</li>

                    <!-- LỚP HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'class' ? 'active' : '' ?>">
                        <a href="?module=class&action=index" class="sidebar-link">
                            <i class="bi bi-easel-fill"></i>
                            <span>Lớp học</span>
                        </a>
                    </li>

                    <!-- DANH SÁCH HỌC SINH -->
                    <li class="sidebar-item <?= $currentModule == 'student' ? 'active' : '' ?>">
                        <a href="?module=student&action=index" class="sidebar-link">
                            <i class="bi bi-people-fill"></i>
                            <span>Danh sách học sinh</span>
                        </a>
                    </li>

                    <!-- LỊCH SỬ DẠY -->
                    <li
                        class="sidebar-item <?= ($currentModule == 'teacher' && $currentAction == 'history') ? 'active' : '' ?>">
                        <a href="?module=teacher&action=history" class="sidebar-link">
                            <i class="bi bi-clock-history"></i>
                            <span>Lịch sử dạy</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Điểm danh</li>

                    <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'attendance') ? 'active' : '' ?>">
                        <a href="?module=face&action=attendance" class="sidebar-link">
                            <i class="bi bi-camera-video-fill"></i>
                            <span>Điểm danh gương mặt</span>
                        </a>
                    </li>

                    <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'enroll') ? 'active' : '' ?>">
                        <a href="?module=face&action=enroll" class="sidebar-link">
                            <i class="bi bi-person-bounding-box"></i>
                            <span>Đăng ký khuôn mặt</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Tài khoản</li>

                    <li
                        class="sidebar-item <?= ($currentModule == 'teacher' && $currentAction == 'my_salary') ? 'active' : '' ?>">
                        <a href="?module=teacher&action=my_salary" class="sidebar-link">
                            <i class="bi bi-cash-stack"></i>
                            <span>Lương của tôi</span>
                        </a>
                    </li>

                    <li class="sidebar-item <?= ($currentModule == 'auth' && $currentAction == 'changePassword') ? 'active' : '' ?>">
                        <a href="?module=auth&action=changePassword" class="sidebar-link">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?module=auth&action=logout" class="sidebar-link text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>

                <?php endif; ?>


                <!-- ========================= -->
                <!-- ADMIN FULL MENU (GIỮ NGUYÊN 100%) -->
                <!-- ========================= -->
                <?php if ($role !== 'teacher'): ?>

                    <li class="sidebar-title">Tổng quan</li>

                    <!-- DASHBOARD -->
                    <li class="sidebar-item <?= $currentModule == 'dashboard' ? 'active' : '' ?>">

                        <a href="index.php" class="sidebar-link">
                            <i class="bi bi-speedometer2"></i>
                            <span>Trang chủ</span>
                        </a>

                    </li>

                    <li class="sidebar-item <?= $currentModule == 'account' ? 'active' : '' ?>">
                        <a href="?module=account&action=index" class="sidebar-link">
                            <i class="bi bi-person-badge-fill"></i>
                            <span>Quản lý tài khoản</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Đào tạo</li>

                    <!-- LỚP HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'class' ? 'active' : '' ?>">
                        <a href="?module=class&action=index" class="sidebar-link">
                            <i class="bi bi-easel-fill"></i>
                            <span>Quản lý lớp học</span>
                        </a>
                    </li>

                    <!-- HỌC VIÊN -->
                    <li
                        class="sidebar-item has-sub <?= ($currentModule == 'student' || $currentModule == 'enrollment') ? 'active open' : '' ?>">

                        <a href="javascript:void(0)" class="sidebar-link">
                            <i class="bi bi-people-fill"></i>
                            <span>Quản lý học viên</span>
                        </a>

                        <ul class="submenu"
                            style="<?= ($currentModule == 'student' || $currentModule == 'enrollment') ? 'display:block;' : '' ?>">

                            <li class="submenu-item <?= $currentModule == 'student' ? 'active' : '' ?>">
                                <a href="?module=student&action=index">
                                    <i class="bi bi-person-lines-fill"></i>
                                    <span>Danh sách học viên</span>
                                </a>
                            </li>

                            <li class="submenu-item <?= $currentModule == 'enrollment' ? 'active' : '' ?>">
                                <a href="?module=enrollment&action=index">
                                    <i class="bi bi-person-plus-fill"></i>
                                    <span>Ghi danh</span>
                                </a>
                            </li>

                        </ul>
                    </li>

                    <!-- CA HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'shift' ? 'active' : '' ?>">
                        <a href="?module=shift&action=index" class="sidebar-link">
                            <i class="bi bi-clock-fill"></i>
                            <span>Quản lý ca học</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Nhân sự</li>

                    <!-- GIẢNG VIÊN -->
                    <li
                        class="sidebar-item has-sub <?= in_array($currentModule, ['teacher', 'specialization', 'salary']) ? 'active open' : '' ?>">

                        <a href="javascript:void(0)" class="sidebar-link">
                            <i class="bi bi-person-badge-fill"></i>
                            <span>Quản lý giảng viên</span>
                        </a>

                        <ul class="submenu"
                            style="<?= in_array($currentModule, ['teacher', 'specialization', 'salary']) ? 'display:block;' : '' ?>">

                            <li class="submenu-item <?= ($currentModule == 'teacher' && $currentAction == 'index') ? 'active' : '' ?>">
                                <a href="?module=teacher&action=index">
                                    <i class="bi bi-people-fill"></i>
                                    <span>Danh sách giảng viên</span>
                                </a>
                            </li>

                            <li class="submenu-item <?= $currentModule == 'specialization' ? 'active' : '' ?>">
                                <a href="?module=specialization&action=index">
                                    <i class="bi bi-award-fill"></i>
                                    <span>Chuyên môn</span>
                                </a>
                            </li>

                            <li class="submenu-item <?= ($currentModule == 'teacher' && $currentAction == 'salary_config') ? 'active' : '' ?>">
                                <a href="?module=teacher&action=salary_config">
                                    <i class="bi bi-sliders"></i>
                                    <span>Cấu hình bậc lương</span>
                                </a>
                            </li>

                            <li class="submenu-item <?= ($currentModule == 'teacher' && $currentAction == 'payroll') ? 'active' : '' ?>">
                                <a href="?module=teacher&action=payroll">
                                    <i class="bi bi-wallet2"></i>
                                    <span>Bảng lương tháng</span>
                                </a>
                            </li>

                            <li class="submenu-item <?= ($currentModule == 'teacher' && $currentAction == 'bonus_penalties') ? 'active' : '' ?>">
                                <a href="?module=teacher&action=bonus_penalties">
                                    <i class="bi bi-gift-fill"></i>
                                    <span>Quản lý thưởng / phạt</span>
                                </a>
                            </li>

                        </ul>
                    </li>

                    <li class="sidebar-title">Chương trình học</li>

                    <!-- KHÓA HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'course' ? 'active' : '' ?>">
                        <a href="?module=course&action=index" class="sidebar-link">
                            <i class="bi bi-book-fill"></i>
                            <span>Quản lý khóa học</span>
                        </a>
                    </li>

                    <!-- GÓI HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'package' ? 'active' : '' ?>">
                        <a href="?module=package&action=index" class="sidebar-link">
                            <i class="bi bi-layers-fill"></i>
                            <span>Quản lý gói học</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Lịch học & phòng</li>

                    <!-- LỊCH HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'daily_schedule' ? 'active' : '' ?>">
                        <a href="?module=daily_schedule&action=index" class="sidebar-link">
                            <i class="bi bi-calendar-check-fill"></i>
                            <span>Lịch trong ngày</span>
                        </a>
                    </li>

                    <li class="sidebar-item <?= $currentModule == 'schedule' ? 'active' : '' ?>">
                        <a href="?module=schedule&action=index" class="sidebar-link">
                            <i class="bi bi-calendar2-week-fill"></i>
                            <span>Mẫu lịch học</span>
                        </a>
                    </li>

                    <!-- PHÒNG HỌC -->
                    <li class="sidebar-item <?= $currentModule == 'room' ? 'active' : '' ?>">
                        <a href="?module=room&action=index" class="sidebar-link">
                            <i class="bi bi-building"></i>
                            <span>Phòng học</span>
                        </a>
                    </li>

                    <li class="sidebar-title">Điểm danh</li>

                    <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'attendance') ? 'active' : '' ?>">
                        <a href="?module=face&action=attendance" class="sidebar-link">
                            <i class="bi bi-camera-video-fill"></i>
                            <span>Điểm danh</span>
                        </a>
                    </li>

                    <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'enroll') ? 'active' : '' ?>">
                        <a href="?module=face&action=enroll" class="sidebar-link">
                            <i class="bi bi-person-bounding-box"></i>
                            <span>Đăng kí khuôn mặt</span>
                        </a>
                    </li>

                    <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'lateReport') ? 'active' : '' ?>">
                        <a href="?module=face&action=lateReport" class="sidebar-link">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i>
                            <span>Báo cáo đi trễ</span>
                        </a>
                    </li>

                    <?php if ($role === 'admin'): ?>
                        <li class="sidebar-item <?= ($currentModule == 'face' && $currentAction == 'wifiConfig') ? 'active' : '' ?>">
                            <a href="?module=face&action=wifiConfig" class="sidebar-link">
                                <i class="bi bi-wifi"></i>
                                <span>Cấu hình WiFi</span>
                            </a>
                        </li>
                    <?php endif; ?>

                    <li class="sidebar-title">Tài khoản</li>

                    <li class="sidebar-item <?= ($currentModule == 'auth' && $currentAction == 'changePassword') ? 'active' : '' ?>">
                        <a href="?module=auth&action=changePassword" class="sidebar-link">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>Đổi mật khẩu</span>
                        </a>
                    </li>

                    <li class="sidebar-item">
                        <a href="?module=auth&action=logout" class="sidebar-link text-danger">
                            <i class="bi bi-box-arrow-right"></i>
                            <span>Đăng xuất</span>
                        </a>
                    </li>
                <?php endif; ?>

            </ul>

        </div>

    </div>
</div>
