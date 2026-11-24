<?php
namespace App\Models;

use App\Core\Model;

class Journal extends Model
{
    protected string $table = 'journals';
    protected bool $tenantScoped = true;

    public function getJournalWithEntries(int $id): ?array
    {
        $companyId = $this->getCompanyId();
        $journal = $this->find($id);
        if (!$journal) return null;

        $entries = $this->db->fetchAll(
            "SELECT je.*, a.account_name 
             FROM `journal_entries` je 
             JOIN `accounts` a ON je.account_id = a.id 
             WHERE je.journal_id = :jrn_id AND je.company_id = :comp_id",
            ['jrn_id' => $id, 'comp_id' => $companyId]
        );

        $journal['entries'] = $entries;
        return $journal;
    }
}
