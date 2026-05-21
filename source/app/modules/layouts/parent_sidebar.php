<div id="sidebar" class="active">
    <div class="sidebar-wrapper active">
        <div class="sidebar-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="logo">
                    <img src="<?= BASE_URL ?>assets/images/logo/logo.png">
                </div>
                <div class="toggler">
                    <a href="#" class="sidebar-hide d-xl-none d-block"><i class="bi bi-x bi-middle"></i></a>
                </div>
            </div>
        </div>

        <div class="sidebar-user-profile px-4 mb-3">
            <div class="d-flex align-items-center">
                <div class="avatar avatar-md me-3">
                    <div class="student-avatar bg-warning text-white d-flex align-items-center justify-content-center rounded-circle"
                        style="width: 40px; height: 40px; font-weight: bold;">
                        <?php echo $parentInitials; ?>
                    </div>
                </div>
                <div class="user-name">
                    <h6 class="mb-0 text-sm fw-bold"><?php echo $parentName; ?></h6>
                    <small class="text-muted">Phụ huynh</small>
                </div>
            </div>
        </div>

        <hr class="mx-4 opacity-05">

        <div class="sidebar-student-selector px-4 mb-4">
            <div class="sidebar-title mb-2">Học viên:</div>
            <div class="d-flex flex-column gap-2">
                <?php if (!empty($students)):
                    foreach ($students as $index => $student):
                        // Giả sử học sinh đầu tiên hoặc theo ID được chọn là active
                        $isActive = (isset($_GET['student_id']) && $_GET['student_id'] == $student['student_id']) || (!isset($_GET['student_id']) && $index == 0);
                        ?>
                        <a href="?module=parent&action=dashboard&student_id=<?php echo $student['student_id']; ?>"
                            class="student-item <?php echo $isActive ? 'active' : ''; ?>">
                            <div class="student-avatar <?php echo $isActive ? '' : 'bg-secondary'; ?>">
                                <?php echo $this->getInitials($student['student_name']); ?>
                            </div>
                            <span class="student-name"><?php echo $student['student_name']; ?></span>
                            <?php if ($isActive): ?>
                                <i class="bi bi-check-circle-fill ms-auto"></i>
                            <?php endif; ?>
                        </a>
                    <?php
                    endforeach;
                endif;
                ?>
            </div>
        </div>

        <div class="sidebar-menu">
            <ul class="menu">

                <li class="sidebar-item <?php echo ($_GET['action'] == 'dashboard') ? 'active' : ''; ?>">
                    <a href="?module=parent&action=dashboard&student_id=<?= (int)($selectedStudentId ?? ($_GET['student_id'] ?? 0)) ?>" class="sidebar-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tổng quan</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo ($_GET['action'] == 'calendar') ? 'active' : ''; ?>">
                    <a href="?module=parent&action=calendar&student_id=<?= (int)($selectedStudentId ?? ($_GET['student_id'] ?? 0)) ?>" class="sidebar-link">
                        <i class="bi bi-calendar-check-fill"></i>
                        <span>Lịch học</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo ($_GET['action'] == 'report') ? 'active' : ''; ?>">
                    <a href="?module=parent&action=report&student_id=<?= (int)($selectedStudentId ?? ($_GET['student_id'] ?? 0)) ?>" class="sidebar-link">
                        <i class="bi bi-journal-check"></i>
                        <span>Sổ liên lạc</span>
                    </a>
                </li>

                <li class="sidebar-item <?php echo ($_GET['action'] == 'packages') ? 'active' : ''; ?>">
                    <a href="?module=parent&action=packages&student_id=<?= (int)($selectedStudentId ?? ($_GET['student_id'] ?? 0)) ?>" class="sidebar-link">
                        <i class="bi bi-bag-plus-fill"></i>
                        <span>Đăng ký gói học</span>
                    </a>
                </li>

                <!-- LOGOUT -->
                <li class="sidebar-item mt-3">
                    <a href="?module=auth&action=logout" class="sidebar-link text-danger">

                        <i class="bi bi-box-arrow-right"></i>
                        <span>Đăng xuất</span>

                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>
