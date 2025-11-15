<?php
namespace OCA\Verein\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method int getMemberId()
 * @method void setMemberId(int $memberId)
 * @method float getAmount()
 * @method void setAmount(float $amount)
 * @method string getStatus()
 * @method void setStatus(string $status)
 * @method string getDueDate()
 * @method void setDueDate(string $dueDate)
 * @method ?string getPaidDate()
 * @method void setPaidDate(?string $paidDate)
 * @method ?string getDescription()
 * @method void setDescription(?string $description)
 * @method ?string getCreatedAt()
 * @method void setCreatedAt(?string $createdAt)
 * @method ?string getUpdatedAt()
 * @method void setUpdatedAt(?string $updatedAt)
 */
class Fee extends Entity implements JsonSerializable {
    protected int $memberId = 0;
    protected float $amount = 0.0;
    protected string $status = 'open';
    protected string $dueDate = '';
    protected ?string $paidDate = null;
    protected ?string $description = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'memberId' => $this->memberId,
            'amount' => $this->amount,
            'status' => $this->status,
            'dueDate' => $this->dueDate,
            'paidDate' => $this->paidDate,
            'description' => $this->description,
        ];
    }
}
