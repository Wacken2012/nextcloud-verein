<?php

namespace OCA\Verein\Tests\Unit\Validation;

use OCA\Verein\Service\Validation\SepaXmlValidator;
use PHPUnit\Framework\TestCase;

class SepaXmlValidatorTest extends TestCase {
    private SepaXmlValidator $validator;

    protected function setUp(): void {
        parent::setUp();
        $this->validator = new SepaXmlValidator();
    }

    /**
     * Test valid minimal SEPA XML structure
     */
    public function testValidSepaXmlMinimal(): void {
        $xml = $this->getValidMinimalSepaXml();
        $result = $this->validator->validate($xml);

        $this->assertTrue($result['valid'], 'Should validate valid SEPA XML');
        $this->assertNull($result['code']);
    }

    /**
     * Test invalid XML - malformed
     */
    public function testInvalidXmlMalformed(): void {
        $xml = '<?xml version="1.0"?><Document><CstmrCdtTrfInitn>';
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('XML_INVALID', $result['code']);
    }

    /**
     * Test invalid structure - missing Document root
     */
    public function testInvalidStructureMissingRoot(): void {
        $xml = '<?xml version="1.0"?><InvalidRoot><CstmrCdtTrfInitn></CstmrCdtTrfInitn></InvalidRoot>';
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('SEPA_STRUCTURE_INVALID', $result['code']);
    }

    /**
     * Test invalid structure - missing CstmrCdtTrfInitn
     */
    public function testInvalidStructureMissingInitiation(): void {
        $xml = '<?xml version="1.0"?><Document></Document>';
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('SEPA_STRUCTURE_INVALID', $result['code']);
    }

    /**
     * Test invalid structure - missing GrpHdr
     */
    public function testInvalidStructureMissingGrpHdr(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <PmtInf></PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('SEPA_STRUCTURE_INVALID', $result['code']);
    }

    /**
     * Test invalid structure - missing PmtInf
     */
    public function testInvalidStructureMissingPmtInf(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('SEPA_STRUCTURE_INVALID', $result['code']);
    }

    /**
     * Test missing Debtor element
     */
    public function testMissingDebtor(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <CdtTrfTxInf>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
        <Amt>100.00</Amt>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('FIELD_MISSING', $result['code']);
    }

    /**
     * Test missing Debtor IBAN
     */
    public function testMissingDebtorIban(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id></Id></DbtrAcct>
      <CdtTrfTxInf>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
        <Amt>100.00</Amt>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('FIELD_MISSING', $result['code']);
    }

    /**
     * Test missing Creditor element
     */
    public function testMissingCreditor(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
      <CdtTrfTxInf>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
        <Amt>100.00</Amt>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('FIELD_MISSING', $result['code']);
    }

    /**
     * Test missing Creditor IBAN
     */
    public function testMissingCreditorIban(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
      <CdtTrfTxInf>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id></Id></CdtrAcct>
        <Amt>100.00</Amt>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('FIELD_MISSING', $result['code']);
    }

    /**
     * Test missing Amount element
     */
    public function testMissingAmount(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
      <CdtTrfTxInf>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        $this->assertFalse($result['valid']);
        $this->assertSame('FIELD_MISSING', $result['code']);
    }

    /**
     * Test invalid Amount format
     */
    public function testInvalidAmountFormat(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
      <CdtTrfTxInf>
        <PmtId><InstrId>123</InstrId></PmtId>
        <Amt>abc</Amt>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        // The amount "abc" is invalid (not numeric)
        $this->assertFalse($result['valid']);
        // Should report amount format error, not missing
        $this->assertNotNull($result['code']);
    }

    /**
     * Test invalid Amount format - too many decimals
     */
    public function testInvalidAmountFormatTooManyDecimals(): void {
        $xml = <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr></GrpHdr>
    <PmtInf>
      <Dbtr><Nm>Debtor</Nm></Dbtr>
      <DbtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></DbtrAcct>
      <CdtTrfTxInf>
        <PmtId><InstrId>123</InstrId></PmtId>
        <Amt>100.123</Amt>
        <Cdtr><Nm>Creditor</Nm></Cdtr>
        <CdtrAcct><Id><IBAN>DE89370400440532013000</IBAN></Id></CdtrAcct>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
        $result = $this->validator->validate($xml);

        // Amount "100.123" has 3 decimals (invalid, max 2)
        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['code']);
    }

    /**
     * Test validation result structure
     */
    public function testValidationResultStructure(): void {
        $xml = $this->getValidMinimalSepaXml();
        $result = $this->validator->validate($xml);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('valid', $result);
        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    /**
     * Helper: Get valid minimal SEPA XML
     */
    private function getValidMinimalSepaXml(): string {
        return <<<XML
<?xml version="1.0"?>
<Document>
  <CstmrCdtTrfInitn>
    <GrpHdr>
      <MsgId>MSG123</MsgId>
      <CreDtTm>2025-01-01T12:00:00</CreDtTm>
      <Ctry>DE</Ctry>
      <InitgPty>
        <Nm>Initiator</Nm>
      </InitgPty>
    </GrpHdr>
    <PmtInf>
      <PmtInfId>PmtInf1</PmtInfId>
      <PmtMtd>TRF</PmtMtd>
      <Dbtr>
        <Nm>Debtor Name</Nm>
      </Dbtr>
      <DbtrAcct>
        <Id>
          <IBAN>DE89370400440532013000</IBAN>
        </Id>
      </DbtrAcct>
      <DbtrAgt>
        <FinInstnId>
          <BIC>DEUTDEDD</BIC>
        </FinInstnId>
      </DbtrAgt>
      <CdtTrfTxInf>
        <PmtId>
          <InstrId>Instr1</InstrId>
          <EndToEndId>E2E1</EndToEndId>
        </PmtId>
        <Amt>100.00</Amt>
        <CdtrAgt>
          <FinInstnId>
            <BIC>DEUTDEDD</BIC>
          </FinInstnId>
        </CdtrAgt>
        <Cdtr>
          <Nm>Creditor Name</Nm>
        </Cdtr>
        <CdtrAcct>
          <Id>
            <IBAN>DE89370400440532013000</IBAN>
          </Id>
        </CdtrAcct>
        <RmtInf>
          <Ustrd>Payment Reference</Ustrd>
        </RmtInf>
      </CdtTrfTxInf>
    </PmtInf>
  </CstmrCdtTrfInitn>
</Document>
XML;
    }
}
