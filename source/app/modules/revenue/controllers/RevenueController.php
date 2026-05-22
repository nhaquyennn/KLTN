<?php

class RevenueController extends Controller
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function index()
    {
        $this->role(['admin']);

        [$fromDate, $toDate] = $this->getDateRange();
        $model = new RevenueModel($this->db);

        $summary = $model->getSummary($fromDate, $toDate);
        $dailyRevenue = $model->getDailyRevenue($fromDate, $toDate);
        $paymentMethods = $model->getPaymentMethodTotals($fromDate, $toDate);
        $recentPayments = $model->getRecentPayments($fromDate, $toDate);

        $header = ROOT_PATH . "/modules/layouts/header_dashboard.php";
        $view = ROOT_PATH . "/modules/revenue/views/index.php";
        require_once ROOT_PATH . "/modules/layouts/main.php";
    }

    private function getDateRange()
    {
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Ho_Chi_Minh'));
        $defaultFrom = $today->modify('first day of this month')->format('Y-m-d');
        $defaultTo = $today->format('Y-m-d');

        $fromDate = $this->validDate($_GET['from_date'] ?? '') ?: $defaultFrom;
        $toDate = $this->validDate($_GET['to_date'] ?? '') ?: $defaultTo;

        if ($fromDate > $toDate) {
            [$fromDate, $toDate] = [$toDate, $fromDate];
        }

        return [$fromDate, $toDate];
    }

    private function validDate($value)
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $value, new DateTimeZone('Asia/Ho_Chi_Minh'));
        return $date && $date->format('Y-m-d') === $value ? $value : null;
    }
}
