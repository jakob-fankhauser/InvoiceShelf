{{--
    Recipient block for the Fankhauser templates, built straight from the customer's billing
    address so every line is printed in German order regardless of the configurable address format.
    Expects: $customer (App\Models\Customer|null)
--}}
@if($customer)
    @php
        $address = $customer->billingAddress;
        $recipientName = $address?->name ?: $customer->name;
        $contactName = $customer->contact_name;
        $countryCode = strtoupper((string) $address?->country?->code);
        $countryNamesDE = ['AT' => 'Österreich', 'CH' => 'Schweiz', 'IT' => 'Italien', 'LI' => 'Liechtenstein'];
    @endphp
    <strong>{{ $recipientName }}</strong><br>
    @if(filled($contactName) && $contactName !== $recipientName)
        z. Hd. {{ $contactName }}<br>
    @endif
    @if($address)
        @if($address->address_street_1)
            {{ $address->address_street_1 }}<br>
        @endif
        @if($address->address_street_2)
            {{ $address->address_street_2 }}<br>
        @endif
    @endif
    @if($address)
        @if($address->zip || $address->city)
            {{ trim($address->zip.' '.$address->city) }}<br>
        @endif
        @if($countryCode !== '' && $countryCode !== 'DE')
            {{ $countryNamesDE[$countryCode] ?? $address->country->name }}<br>
        @endif
    @endif
@endif
