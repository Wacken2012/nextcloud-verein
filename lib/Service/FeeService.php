<?php
namespace OCA\Verein\Service;

use Exception;
use OCA\Verein\Db\Fee;
use OCA\Verein\Db\FeeMapper;

class FeeService {
    private FeeMapper $mapper;

    public function __construct(FeeMapper $mapper) {
        $this->mapper = $mapper;
    }

    public function findAll(): array {
        return $this->mapper->findAll();
    }

    public function find(int $id): Fee {
        try {
            return $this->mapper->find($id);
        } catch (Exception $e) {
            throw new Exception('Fee not found');
        }
    }

    public function create(
        int $memberId,
        float $amount,
        string $status,
        string $dueDate,
        ?string $description = null
    ): Fee {
        $fee = new Fee();
        $fee->setMemberId($memberId);
        $fee->setAmount($amount);
        $fee->setStatus($status);
        $fee->setDueDate($dueDate);
        $fee->setDescription($description);
        $fee->setCreatedAt(date('Y-m-d H:i:s'));
        $fee->setUpdatedAt(date('Y-m-d H:i:s'));
        return $this->mapper->insert($fee);
    }

    public function update(
        int $id,
        int $memberId,
        float $amount,
        string $status,
        string $dueDate,
        ?string $description = null
    ): Fee {
        $fee = $this->mapper->find($id);
        $fee->setMemberId($memberId);
        $fee->setAmount($amount);
        $fee->setStatus($status);
        $fee->setDueDate($dueDate);
        $fee->setDescription($description);
        $fee->setUpdatedAt(date('Y-m-d H:i:s'));
        return $this->mapper->update($fee);
    }

    public function delete(int $id): Fee {
        $fee = $this->mapper->find($id);
        return $this->mapper->delete($fee);
    }

    public function exportToCsv(): string {
        $fees = $this->mapper->findAll();
        $csv = "ID,Member ID,Amount,Status,Due Date\n";
        foreach ($fees as $fee) {
            $csv .= sprintf(
                "%d,%d,%.2f,%s,%s\n",
                $fee->getId(),
                $fee->getMemberId(),
                $fee->getAmount(),
                $fee->getStatus(),
                $fee->getDueDate()
            );
        }
        return $csv;
    }
}
