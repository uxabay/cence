<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractSample;
use App\Models\ContractSampleCategory;
use App\Models\CustomerCategory;
use App\Models\LabCustomer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPdf\Facades\Pdf;
use Tests\TestCase;

class ContractOverviewPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_contract_overview_pdf(): void
    {
        $contract = $this->makeContract();

        $this->get(route('admin.contracts.overview.pdf', $contract))
            ->assertRedirect('/admin/login');
    }

    public function test_authorized_user_receives_inline_pdf_with_selected_period_and_overview_data(): void
    {
        $contract = $this->makeContract();
        $category = ContractSampleCategory::query()->create(['code' => 'WATER', 'name' => 'Νερό']);
        ContractSample::query()->create([
            'contract_id' => $contract->id,
            'contract_sample_category_id' => $category->id,
            'name' => 'Νερό',
            'forecasted_samples' => 12,
            'forecasted_amount' => 600,
        ]);

        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());
        $pdf = Pdf::fake();

        $response = $this->get(route('admin.contracts.overview.pdf', [
            'contract' => $contract,
            'from' => '2026-01-01',
            'to' => '2026-01-31',
        ]));

        $response->assertOk();
        $pdf->assertRespondedWithPdf(fn ($builder) => $builder->isInline()
            && $builder->viewName === 'pdf.contracts.overview'
            && $builder->viewData['periodLabel'] === 'Πρωτόκολλα από 01/01/2026 έως 31/01/2026'
            && $builder->viewData['summary']['forecasted_amount'] === 600.0
            && $builder->viewData['rows'][0]['category_name'] === 'Νερό');
    }

    public function test_invalid_or_reversed_period_is_rejected(): void
    {
        $contract = $this->makeContract();
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.contracts.overview.pdf', [
            'contract' => $contract,
            'from' => '2026-02-01',
            'to' => '2026-01-01',
        ]))->assertSessionHasErrors('to');
    }

    public function test_dompdf_renders_an_inline_pdf_response(): void
    {
        $contract = $this->makeContract();
        $category = ContractSampleCategory::query()->create(['code' => 'AIR', 'name' => 'Αέρας']);
        ContractSample::query()->create([
            'contract_id' => $contract->id,
            'contract_sample_category_id' => $category->id,
            'name' => 'Αέρας',
            'forecasted_samples' => 2,
            'forecasted_amount' => 40,
        ]);
        Gate::before(fn () => true);
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('admin.contracts.overview.pdf', $contract));

        $response->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename="episkopisi-symvasis-'.$contract->id.'.pdf"');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    private function makeContract(): Contract
    {
        $customerCategory = CustomerCategory::query()->create(['name' => 'Δημόσιος φορέας']);
        $customer = LabCustomer::query()->create([
            'name' => 'Πελάτης δοκιμής',
            'customer_category_id' => $customerCategory->id,
        ]);

        return Contract::query()->create([
            'title' => 'Τοπική σύμβαση 1',
            'contract_number' => 'LOCAL-C-1',
            'lab_customer_id' => $customer->id,
        ]);
    }
}
