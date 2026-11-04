<?php

use App\Models\Address;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\User;
use App\Support\SmallBusinessTaxNotice;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use function Pest\Laravel\get;

beforeEach(function (): void {
    Artisan::call('db:seed', ['--force' => true, '--class' => 'DatabaseSeeder']);
    Artisan::call('db:seed', ['--force' => true, '--class' => 'DemoSeeder']);

    $this->company = Company::query()->first();
    Sanctum::actingAs(User::query()->where('role', 'super admin')->first(), ['*']);

    // The payment QR code needs imagick, which is not part of what these tests check.
    QrCode::shouldReceive('format->size->generate')->andReturn('png');
});

function customerIn(Company $company, string $countryCode, array $customer = [], array $address = []): Customer
{
    $customer = Customer::factory()->create(array_merge([
        'company_id' => $company->id,
        'contact_name' => null,
        'tax_id' => null,
    ], $customer));

    Address::factory()->create(array_merge([
        'customer_id' => $customer->id,
        'company_id' => $company->id,
        'type' => Address::BILLING_TYPE,
        'country_id' => Country::query()->where('code', $countryCode)->value('id'),
    ], $address));

    return $customer->fresh();
}

test('the invoice shows every billing line, the contact, the order number and the notes', function () {
    $customer = customerIn($this->company, 'DE', ['contact_name' => 'Rene Kurth'], [
        'name' => 'Universitätsklinikum Köln (AöR)',
        'address_street_1' => 'Zentrale Rechnungsstelle',
        'address_street_2' => 'Kerpener Str. 62',
        'zip' => '50937',
        'city' => 'Köln',
    ]);

    $invoice = Invoice::factory()->create([
        'company_id' => $this->company->id,
        'customer_id' => $customer->id,
        'template_name' => 'fankhauserl',
        'reference_number' => '8200218240',
        'notes' => '1. Teilrechnung: 48 von 128 Std.',
        'tax' => 0,
    ]);

    get('/invoices/pdf/'.$invoice->unique_hash.'?preview')
        ->assertOk()
        ->assertSeeInOrder([
            'Universitätsklinikum Köln (AöR)',
            'z. Hd. Rene Kurth',
            'Zentrale Rechnungsstelle',
            'Kerpener Str. 62',
            '50937 Köln',
        ])
        ->assertSee('Bestellnummer')
        ->assertSee('8200218240')
        ->assertSee('1. Teilrechnung: 48 von 128 Std.')
        ->assertSee(SmallBusinessTaxNotice::DOMESTIC)
        ->assertDontSee('19a');
});

test('the estimate uses the same recipient block and tax notice', function () {
    $customer = customerIn($this->company, 'AT', [], [
        'name' => 'Muster GmbH',
        'address_street_1' => 'Hauptplatz 1',
        'address_street_2' => '2. Stock',
        'zip' => '6020',
        'city' => 'Innsbruck',
    ]);

    $estimate = Estimate::factory()->create([
        'company_id' => $this->company->id,
        'customer_id' => $customer->id,
        'template_name' => 'fankhauser',
        'tax' => 0,
    ]);

    get('/estimates/pdf/'.$estimate->unique_hash.'?preview')
        ->assertOk()
        ->assertSeeInOrder(['Muster GmbH', 'Hauptplatz 1', '2. Stock', '6020 Innsbruck', 'Österreich'])
        ->assertSee(SmallBusinessTaxNotice::DOMESTIC);
});

test('the tax notice is left out when the document charges tax', function () {
    expect(SmallBusinessTaxNotice::for(0))->toBe(SmallBusinessTaxNotice::DOMESTIC)
        ->and(SmallBusinessTaxNotice::for(1900))->toBeNull();
});
