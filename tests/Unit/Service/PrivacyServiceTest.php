<?php

namespace OCA\Verein\Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use OCA\Verein\Service\PrivacyService;
use OCA\Verein\Db\MemberMapper;
use OCA\Verein\Db\Member;
use OCP\IConfig;
use Psr\Log\LoggerInterface;

class PrivacyServiceTest extends TestCase {
	private PrivacyService $privacyService;
	private $memberMapper;
	private $logger;
	private $config;

	protected function setUp(): void {
		parent::setUp();

		$this->memberMapper = $this->createMock(MemberMapper::class);
		$this->logger = $this->createMock(LoggerInterface::class);
		$this->config = $this->createMock(IConfig::class);

		$this->privacyService = new PrivacyService(
			$this->memberMapper,
			$this->logger,
			$this->config
		);
	}

	public function testGetPrivacyPolicyReturnsString(): void {
		$this->config->method('getAppValue')
			->willReturn('Test Privacy Policy');

		$policy = $this->privacyService->getPrivacyPolicy();

		$this->assertEquals('Test Privacy Policy', $policy);
	}

	public function testGetPrivacyPolicyReturnsEmptyStringWhenNotSet(): void {
		$this->config->method('getAppValue')
			->willReturn('');

		$policy = $this->privacyService->getPrivacyPolicy();

		$this->assertEquals('', $policy);
	}

	public function testGetMemberConsentsReturnsEmptyArrayForNonexistentMember(): void {
		$this->memberMapper->method('find')
			->willThrowException(new \OCP\AppFramework\Db\DoesNotExistException('Member not found'));

		$consents = $this->privacyService->getMemberConsents(999);

		$this->assertIsArray($consents);
		$this->assertEmpty($consents);
	}

	public function testExportMemberDataThrowsExceptionForInvalidMember(): void {
		$this->memberMapper->method('find')
			->willThrowException(new \OCP\AppFramework\Db\DoesNotExistException('Member not found'));

		$this->expectException(\Exception::class);

		$this->privacyService->exportMemberData(999);
	}

	public function testSaveConsentReturnsTrue(): void {
		$result = $this->privacyService->saveConsent(1, 'newsletter', true);

		$this->assertTrue($result);
	}
}
