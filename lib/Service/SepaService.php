<?php
namespace OCA\Verein\Service;

use OCA\Verein\Db\FeeMapper;
use OCA\Verein\Db\MemberMapper;

/**
 * Service for generating SEPA-XML files for direct debit
 * Based on SEPA pain.008.001.02 format
 */
class SepaService {
    private FeeMapper $feeMapper;
    private MemberMapper $memberMapper;

    public function __construct(
        FeeMapper $feeMapper,
        MemberMapper $memberMapper
    ) {
        $this->feeMapper = $feeMapper;
        $this->memberMapper = $memberMapper;
    }

    /**
     * Generate SEPA-XML for all open fees
     * 
     * @param string $creditorName Name of the creditor (club/association)
     * @param string $creditorIban IBAN of the creditor
     * @param string $creditorBic BIC of the creditor
     * @param string $creditorId SEPA Creditor ID
     * @return string SEPA-XML content
     */
    public function generateSepaXml(
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorId
    ): string {
        // Get all open fees
        $openFees = $this->feeMapper->findByStatus('open');
        
        if (empty($openFees)) {
            throw new \Exception('No open fees found for SEPA export');
        }

        // Calculate total amount
        $totalAmount = 0;
        $transactions = [];
        
        foreach ($openFees as $fee) {
            $member = $this->memberMapper->find($fee->getMemberId());
            
            if (empty($member->getIban())) {
                continue; // Skip members without IBAN
            }
            
            $totalAmount += $fee->getAmount();
            $transactions[] = [
                'name' => $member->getName(),
                'iban' => $member->getIban(),
                'bic' => $member->getBic(),
                'amount' => $fee->getAmount(),
                'reference' => 'Mitgliedsbeitrag ' . date('Y'),
                'feeId' => $fee->getId()
            ];
        }

        // Generate SEPA-XML
        $xml = $this->buildSepaXml(
            $creditorName,
            $creditorIban,
            $creditorBic,
            $creditorId,
            $totalAmount,
            $transactions
        );

        return $xml;
    }

    /**
     * Preview SEPA export without generating XML
     */
    public function previewSepaExport(
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorId
    ): array {
        $openFees = $this->feeMapper->findByStatus('open');
        
        $totalAmount = 0;
        $transactions = [];
        
        foreach ($openFees as $fee) {
            $member = $this->memberMapper->find($fee->getMemberId());
            
            if (empty($member->getIban())) {
                continue;
            }
            
            $totalAmount += $fee->getAmount();
            $transactions[] = [
                'memberName' => $member->getName(),
                'iban' => $member->getIban(),
                'amount' => $fee->getAmount(),
                'dueDate' => $fee->getDueDate()
            ];
        }

        return [
            'creditorName' => $creditorName,
            'creditorIban' => $creditorIban,
            'totalAmount' => $totalAmount,
            'transactionCount' => count($transactions),
            'transactions' => $transactions
        ];
    }

    /**
     * Build SEPA-XML content (pain.008.001.02 format)
     */
    private function buildSepaXml(
        string $creditorName,
        string $creditorIban,
        string $creditorBic,
        string $creditorId,
        float $totalAmount,
        array $transactions
    ): string {
        $msgId = 'VEREIN-' . date('YmdHis');
        $creationDateTime = date('Y-m-d\TH:i:s');
        $collectionDate = date('Y-m-d', strtotime('+5 days'));
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Document xmlns="urn:iso:std:iso:20022:tech:xsd:pain.008.001.02" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' . "\n";
        $xml .= '  <CstmrDrctDbtInitn>' . "\n";
        
        // Group Header
        $xml .= '    <GrpHdr>' . "\n";
        $xml .= '      <MsgId>' . htmlspecialchars($msgId) . '</MsgId>' . "\n";
        $xml .= '      <CreDtTm>' . $creationDateTime . '</CreDtTm>' . "\n";
        $xml .= '      <NbOfTxs>' . count($transactions) . '</NbOfTxs>' . "\n";
        $xml .= '      <CtrlSum>' . number_format($totalAmount, 2, '.', '') . '</CtrlSum>' . "\n";
        $xml .= '      <InitgPty><Nm>' . htmlspecialchars($creditorName) . '</Nm></InitgPty>' . "\n";
        $xml .= '    </GrpHdr>' . "\n";
        
        // Payment Information
        $xml .= '    <PmtInf>' . "\n";
        $xml .= '      <PmtInfId>' . htmlspecialchars($msgId) . '-1</PmtInfId>' . "\n";
        $xml .= '      <PmtMtd>DD</PmtMtd>' . "\n";
        $xml .= '      <BtchBookg>true</BtchBookg>' . "\n";
        $xml .= '      <NbOfTxs>' . count($transactions) . '</NbOfTxs>' . "\n";
        $xml .= '      <CtrlSum>' . number_format($totalAmount, 2, '.', '') . '</CtrlSum>' . "\n";
        $xml .= '      <PmtTpInf><SvcLvl><Cd>SEPA</Cd></SvcLvl><LclInstrm><Cd>CORE</Cd></LclInstrm><SeqTp>RCUR</SeqTp></PmtTpInf>' . "\n";
        $xml .= '      <ReqdColltnDt>' . $collectionDate . '</ReqdColltnDt>' . "\n";
        
        // Creditor
        $xml .= '      <Cdtr><Nm>' . htmlspecialchars($creditorName) . '</Nm></Cdtr>' . "\n";
        $xml .= '      <CdtrAcct><Id><IBAN>' . $creditorIban . '</IBAN></Id></CdtrAcct>' . "\n";
        $xml .= '      <CdtrAgt><FinInstnId><BIC>' . $creditorBic . '</BIC></FinInstnId></CdtrAgt>' . "\n";
        $xml .= '      <CdtrSchmeId><Id><PrvtId><Othr><Id>' . $creditorId . '</Id><SchmeNm><Prtry>SEPA</Prtry></SchmeNm></Othr></PrvtId></Id></CdtrSchmeId>' . "\n";
        
        // Transactions
        foreach ($transactions as $idx => $txn) {
            $xml .= '      <DrctDbtTxInf>' . "\n";
            $xml .= '        <PmtId><EndToEndId>FEE-' . $txn['feeId'] . '</EndToEndId></PmtId>' . "\n";
            $xml .= '        <InstdAmt Ccy="EUR">' . number_format($txn['amount'], 2, '.', '') . '</InstdAmt>' . "\n";
            $xml .= '        <DrctDbtTx><MndtRltdInf><MndtId>MAND-' . $txn['feeId'] . '</MndtId><DtOfSgntr>2024-01-01</DtOfSgntr></MndtRltdInf></DrctDbtTx>' . "\n";
            $xml .= '        <DbtrAgt><FinInstnId><BIC>' . $txn['bic'] . '</BIC></FinInstnId></DbtrAgt>' . "\n";
            $xml .= '        <Dbtr><Nm>' . htmlspecialchars($txn['name']) . '</Nm></Dbtr>' . "\n";
            $xml .= '        <DbtrAcct><Id><IBAN>' . $txn['iban'] . '</IBAN></Id></DbtrAcct>' . "\n";
            $xml .= '        <RmtInf><Ustrd>' . htmlspecialchars($txn['reference']) . '</Ustrd></RmtInf>' . "\n";
            $xml .= '      </DrctDbtTxInf>' . "\n";
        }
        
        $xml .= '    </PmtInf>' . "\n";
        $xml .= '  </CstmrDrctDbtInitn>' . "\n";
        $xml .= '</Document>';
        
        return $xml;
    }
}
