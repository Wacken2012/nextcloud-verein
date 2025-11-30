<?php
namespace OCA\Verein\Service;

use Exception;
use OCA\Verein\Db\Member;
use OCA\Verein\Db\MemberMapper;

class MemberService {
    private MemberMapper $mapper;

    public function __construct(MemberMapper $mapper) {
        $this->mapper = $mapper;
    }

    public function findAll(): array {
        return $this->mapper->findAll();
    }

    /**
     * Search members by query (name or email), remote-friendly for autocomplete.
     *
     * @param string $query
     * @param int $limit
     * @return array
     */
    public function search(string $query, int $limit = 50): array {
        if (trim($query) === '') {
            return [];
        }
        return $this->mapper->search($query, $limit);
    }

    public function find(int $id): Member {
        try {
            return $this->mapper->find($id);
        } catch (Exception $e) {
            throw new Exception('Member not found');
        }
    }

    public function create(
        string $name,
        string $address,
        string $email,
        string $iban,
        string $bic,
        string $role
    ): Member {
        $member = new Member();
        $member->setName($name);
        $member->setAddress($address);
        $member->setEmail($email);
        $member->setIban($iban);
        $member->setBic($bic);
        $member->setRole($role);
        $member->setCreatedAt(date('Y-m-d H:i:s'));
        $member->setUpdatedAt(date('Y-m-d H:i:s'));
        return $this->mapper->insert($member);
    }

    public function update(
        int $id,
        string $name,
        string $address,
        string $email,
        string $iban,
        string $bic,
        string $role
    ): Member {
        $member = $this->mapper->find($id);
        $member->setName($name);
        $member->setAddress($address);
        $member->setEmail($email);
        $member->setIban($iban);
        $member->setBic($bic);
        $member->setRole($role);
        $member->setUpdatedAt(date('Y-m-d H:i:s'));
        return $this->mapper->update($member);
    }

    public function delete(int $id): Member {
        $member = $this->mapper->find($id);
        return $this->mapper->delete($member);
    }
}
