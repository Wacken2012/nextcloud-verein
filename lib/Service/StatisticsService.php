<?php

namespace OCA\Verein\Service;

use OCA\Verein\Db\MemberMapper;
use OCA\Verein\Db\FeeMapper;

class StatisticsService {
    private MemberMapper $memberMapper;
    private FeeMapper $feeMapper;

    public function __construct(
        MemberMapper $memberMapper,
        FeeMapper $feeMapper
    ) {
        $this->memberMapper = $memberMapper;
        $this->feeMapper = $feeMapper;
    }

    public function getMemberStatistics(): array {
        $members = $this->memberMapper->findAll();
        $total = count($members);
        
        // Group by role
        $byRole = [];
        foreach ($members as $member) {
            $role = $member->getRole();
            if (!isset($byRole[$role])) {
                $byRole[$role] = 0;
            }
            $byRole[$role]++;
        }

        return [
            'total' => $total,
            'byRole' => $byRole,
            'active' => $total, // Assuming all are active for now
            'newThisMonth' => $this->countNewMembersThisMonth($members)
        ];
    }

    public function getFeeStatistics(): array {
        $fees = $this->feeMapper->findAll();
        $totalAmount = 0.0;
        $paidAmount = 0.0;
        $pendingAmount = 0.0;
        $overdueAmount = 0.0;
        $dueAmount = 0.0;

        $paidCount = 0;
        $pendingCount = 0;
        $overdueCount = 0;
        $dueCount = 0;

        $now = new \DateTime();

        foreach ($fees as $fee) {
            $amount = (float)$fee->getAmount();
            $totalAmount += $amount;
            $dueDate = $fee->getDueDate();
            $status = $fee->getStatus();

            switch ($status) {
                case 'paid':
                    $paidAmount += $amount;
                    $paidCount++;
                    break;
                case 'pending': // legacy / alternate status name (if any remain)
                case 'open':
                    $pendingAmount += $amount;
                    $pendingCount++;
                    if ($dueDate) {
                        try {
                            $dueDateObj = new \DateTime($dueDate);
                            if ($dueDateObj < $now) {
                                // Mark as fällig (past due but not yet in 'overdue' state)
                                $dueAmount += $amount;
                                $dueCount++;
                            }
                        } catch (\Exception $e) {
                            // ignore invalid date
                        }
                    }
                    break;
                case 'overdue':
                    $overdueAmount += $amount;
                    $overdueCount++;
                    break;
            }
        }

        return [
            'totalAmount' => $totalAmount,
            'paidAmount' => $paidAmount,
            'pendingAmount' => $pendingAmount,
            'overdueAmount' => $overdueAmount,
            'dueAmount' => $dueAmount,
            'counts' => [
                'total' => count($fees),
                'paid' => $paidCount,
                'pending' => $pendingCount,
                'overdue' => $overdueCount,
                'due' => $dueCount
            ]
        ];
    }

    private function countNewMembersThisMonth(array $members): int {
        $count = 0;
        $currentMonth = date('Y-m');
        
        foreach ($members as $member) {
            $createdAt = $member->getCreatedAt();
            if ($createdAt && strpos($createdAt, $currentMonth) === 0) {
                $count++;
            }
        }
        
        return $count;
    }
}
