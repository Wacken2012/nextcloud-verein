<?php
namespace OCA\Verein\Db;

use JsonSerializable;
use OCP\AppFramework\Db\Entity;

/**
 * @method int getId()
 * @method void setId(int $id)
 * @method string getName()
 * @method void setName(string $name)
 * @method ?string getAddress()
 * @method void setAddress(?string $address)
 * @method string getEmail()
 * @method void setEmail(string $email)
 * @method ?string getIban()
 * @method void setIban(?string $iban)
 * @method ?string getBic()
 * @method void setBic(?string $bic)
 * @method string getRole()
 * @method void setRole(string $role)
 * @method ?string getUserId()
 * @method void setUserId(?string $userId)
 * @method ?string getCreatedAt()
 * @method void setCreatedAt(?string $createdAt)
 * @method ?string getUpdatedAt()
 * @method void setUpdatedAt(?string $updatedAt)
 */
class Member extends Entity implements JsonSerializable {
    protected string $name = '';
    protected ?string $address = null;
    protected string $email = '';
    protected ?string $iban = null;
    protected ?string $bic = null;
    protected string $role = 'member';
    protected ?string $userId = null;
    protected ?string $createdAt = null;
    protected ?string $updatedAt = null;

    public function jsonSerialize(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'email' => $this->email,
            'iban' => $this->iban,
            'bic' => $this->bic,
            'role' => $this->role,
        ];
    }
}
