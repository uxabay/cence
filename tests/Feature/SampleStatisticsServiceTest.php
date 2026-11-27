<?php

namespace Tests\Feature;

use App\Support\Statistics\SampleStatisticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SampleStatisticsServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_flagged_contract_lines_are_excluded_from_lab_totals_but_contract_links_are_counted(): void
    {
        $now = now();
        $customerCategoryId = DB::table('customer_categories')->insertGetId([
            'name' => 'Δημόσιος φορέας',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $customerId = DB::table('lab_customers')->insertGetId([
            'name' => 'Πελάτης δοκιμής',
            'customer_category_id' => $customerCategoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $contractId = DB::table('contracts')->insertGetId([
            'title' => 'Σύμβαση δοκιμής',
            'lab_customer_id' => $customerId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $includedCategoryId = DB::table('contract_sample_categories')->insertGetId([
            'code' => 'INCLUDED',
            'name' => 'Μετρήσιμη κατηγορία',
            'exclude_from_lab_totals' => false,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $excludedCategoryId = DB::table('contract_sample_categories')->insertGetId([
            'code' => 'EXCLUDED',
            'name' => 'Εξαιρούμενη κατηγορία',
            'exclude_from_lab_totals' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $includedContractSampleId = DB::table('contract_samples')->insertGetId([
            'contract_id' => $contractId,
            'contract_sample_category_id' => $includedCategoryId,
            'name' => 'Μετρήσιμη γραμμή',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $excludedContractSampleId = DB::table('contract_samples')->insertGetId([
            'contract_id' => $contractId,
            'contract_sample_category_id' => $excludedCategoryId,
            'name' => 'Εξαιρούμενη γραμμή',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->insertRegistration($now, 10, contractId: $contractId, contractSampleId: $excludedContractSampleId);
        $this->insertRegistration($now, 3, contractSampleId: $includedContractSampleId);
        $this->insertRegistration($now, 4);

        $result = app(SampleStatisticsService::class)->build([
            'from' => '2026-01-01',
            'to' => '2026-01-31',
            'compare' => false,
            'contract_scope' => 'all',
        ]);

        $this->assertSame(7, $result['summary']['received']['current']);
        $this->assertSame(7, $result['summary']['valid']['current']);
        $this->assertSame(3, $result['contract_split']['with_contract']);
        $this->assertSame(4, $result['contract_split']['without_contract']);
    }

    private function insertRegistration(
        mixed $now,
        int $received,
        ?int $contractId = null,
        ?int $contractSampleId = null,
    ): void {
        DB::table('registrations')->insert([
            'date' => '2026-01-15',
            'contract_id' => $contractId,
            'contract_sample_id' => $contractSampleId,
            'num_samples_received' => $received,
            'not_valid_samples' => 0,
            'total_samples' => $received,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
