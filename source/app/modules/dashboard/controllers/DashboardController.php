<?php

class DashboardController extends Controller {
    public function index() {
        $model = new DashboardModel();

        $summary = $model->getSummary();
        $todaySchedule = $model->getTodaySchedule();
        $roomConflicts = $model->getRoomConflicts();
        $unreviewedSessions = $model->getUnreviewedSessions();
        $recentEnrollments = $model->getRecentEnrollments();

        $view = ROOT_PATH . "/modules/dashboard/views/index.php";
        $header = ROOT_PATH . "/modules/layouts/header_dashboard.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }
}
