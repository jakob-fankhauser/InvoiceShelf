<!DOCTYPE html>
<html>

<head>
    <title>@lang('pdf_invoice_label') - {{ $invoice->invoice_number }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <style type="text/css">
        /* -- Base -- */

        body {
            font-family: "DejaVu Sans";
        }

        html {
            margin: 0px;
            padding: 0px;
            margin-top: 50px;
        }

        table {
            border-collapse: collapse;
        }

        hr {
            color: rgba(0, 0, 0, 0.2);
            border: 0.5px solid #00C2A8;
        }

        /* -- Header -- */

        .header-container {
            margin-top: -30px;
            width: 100%;
            padding: 0px 30px;
            margin-bottom: 10px;
        }

        .header-logo {
            text-transform: capitalize;
            color: #2F3B46;
            padding-top: 40px;
            padding-right: 60px;
        }

        .company-address-container {
            width: 50%;
            margin-bottom: 2px;
            padding-right: 60px;
        }

        .company-address {
            margin-top: 12px;
            font-size: 12px;
            line-height: 15px;
            color: #595959;
            word-wrap: break-word;
        }

        /* -- Content Wrapper  */

        .content-wrapper {
            display: block;
            padding-top: 0px;
            padding-bottom: 20px;
        }

        .customer-address-container {
            display: block;
            float: left;
            width: 45%;
            padding: 10px 0 0 30px;
        }

        /* -- Shipping -- */
        .shipping-address-container {
            float: right;
            display: block;
        }

        .shipping-address-container--left {
            float: left;
            display: block;
            padding-left: 0;
        }

        .shipping-address {
            font-size: 10px;
            line-height: 15px;
            color: #595959;
            margin-top: 5px;
            width: 160px;
            word-wrap: break-word;
        }

        /* -- Billing -- */

        .billing-address-container {
            display: block;
            float: left;
        }

        .billing-address {
            font-size: 10px;
            line-height: 15px;
            color: #595959;
            margin-top: 5px;
            width: 250px;
            word-wrap: break-word;
        }

        /*  -- Estimate Details -- */

        .invoice-details-container {
            display: block;
            float: right;
            padding: 10px 30px 0 0;
        }

        .attribute-label {
            font-size: 12px;
            line-height: 18px;
            text-align: left;
            color: #6B7280;
        }

        .attribute-value {
            font-size: 12px;
            line-height: 18px;
            text-align: right;
        }

        /* -- Items Table -- */

        .items-table {
            margin-top: 35px;
            padding: 0px 30px 10px 30px;
            page-break-before: avoid;
            page-break-after: auto;
        }

        .items-table hr {
            height: 0.1px;
        }

        .item-table-heading {
            font-size: 13.5;
            text-align: center;
            color: rgba(0, 0, 0, 0.85);
            padding: 5px;
            color: #2F3B46;
        }

        tr.item-table-heading-row th {
            border-bottom: 0.620315px solid #00C2A8;
            font-size: 12px;
            line-height: 18px;
        }

        tr.item-row td {
            font-size: 12px;
            line-height: 18px;
        }

        .item-cell {
            font-size: 13;
            text-align: center;
            padding: 5px;
            padding-top: 10px;
            color: #040405;
        }

        .item-description {
            color: #595959;
            font-size: 9px;
            line-height: 12px;
            display: block;
        }

        .item-cell-table-hr {
            margin: 0 30px 0 30px;
        }

        /* -- Total Display Table -- */

        .total-display-container {
            padding: 0 25px;
        }


        .total-display-table {
            border-top: none;
            page-break-inside: avoid;
            page-break-before: auto;
            page-break-after: auto;
            margin-top: 20px;
            float: right;
            width: auto;
        }

        .total-table-attribute-label {
            font-size: 12px;
            color: #6B7280;
            text-align: left;
            padding-left: 10px;
        }

        .total-table-attribute-value {
            font-weight: bold;
            text-align: right;
            font-size: 12px;
            color: #040405;
            padding-right: 10px;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .total-border-left {
            border: 1px solid #E8E8E8 !important;
            border-right: 0px !important;
            padding-top: 0px;
            padding: 8px !important;
        }

        .total-border-right {
            border: 1px solid #E8E8E8 !important;
            border-left: 0px !important;
            padding-top: 0px;
            padding: 8px !important;
        }

        /* -- Notes -- */
        .notes {
            font-size: 12px;
            color: #595959;
            margin-top: 15px;
            margin-left: 30px;
            width: 442px;
            text-align: left;
            page-break-inside: avoid;
        }

        .notes-label {
            font-size: 15px;
            line-height: 22px;
            letter-spacing: 0.05em;
            color: #040405;
            width: 108px;
            white-space: nowrap;
            height: 19.87px;
            padding-bottom: 10px;
        }

        /* -- Tax Notice -- */
        .tax-notice {
            font-size: 9px;
            color: #6B7280;
            margin-top: 40px;
            margin-left: 30px;
            margin-right: 30px;
            padding: 8px 12px;
            background: #F9FAFB;
            border-left: 2px solid #E5E7EB;
            page-break-inside: avoid;
            line-height: 14px;
            clear: both;
        }

        .tax-notice strong {
            color: #2F3B46;
        }

        /* -- Footer -- */
        .footer-info {
            font-size: 9px;
            color: #595959;
            margin-top: 25px;
            margin-left: 30px;
            margin-right: 30px;
            padding-top: 15px;
            border-top: 1px solid #E8E8E8;
            page-break-inside: avoid;
            line-height: 14px;
        }

        .footer-info table {
            width: 100%;
            margin-top: 10px;
        }

        .footer-info td {
            vertical-align: top;
            width: 50%;
            padding-right: 15px;
        }

        .footer-info strong {
            color: #2F3B46;
            font-size: 10px;
        }

        /* -- Helpers -- */

        .text-primary {
            color: #00C2A8;
        }

        .text-center {
            text-align: center
        }

        table .text-left {
            text-align: left;
        }

        table .text-right {
            text-align: right;
        }

        .border-0 {
            border: none;
        }

        .py-2 {
            padding-top: 2px;
            padding-bottom: 2px;
        }

        .py-8 {
            padding-top: 8px;
            padding-bottom: 8px;
        }

        .py-3 {
            padding: 3px 0;
        }

        .pr-20 {
            padding-right: 20px;
        }

        .pr-10 {
            padding-right: 10px;
        }

        .pl-20 {
            padding-left: 20px;
        }

        .pl-10 {
            padding-left: 10px;
        }

        .pl-0 {
            padding-left: 0;
        }

        .footer-info {
    font-size: 9px;
    color: #595959;
    margin-top: 25px;
    margin-left: 30px;
    margin-right: 30px;
    padding-top: 15px;
    border-top: 1px solid #E8E8E8;
    page-break-inside: avoid;
    line-height: 14px;
    position: fixed;
    bottom: 30px;
    left: 0;
    right: 0;
}

.header-bottom-divider {
    margin-top: 0;
    margin-bottom: 15px;
}

    </style>

    @if (App::isLocale('th'))
        @include('app.pdf.locale.th')
    @endif
</head>

<body>
    <div class="header-container">
        <table width="100%">
            <tr>
            
                <td width="50%" class="text-left header-section-left company-address-container company-address">
                    @if($invoice->company)
                        <strong>{{ $invoice->company->name }}</strong><br>
                        @if($invoice->company->address)
                            @if($invoice->company->address->address_street_1)
                                {{ $invoice->company->address->address_street_1 }}
                                @if($invoice->company->address->address_street_2)
                                    {{ $invoice->company->address->address_street_2 }}
                                @endif
                                <br>
                            @endif
                            @if($invoice->company->address->zip || $invoice->company->address->city)
                                {{ $invoice->company->address->zip }} {{ $invoice->company->address->city }}<br>
                            @endif
                            @if($invoice->company->address->country)
                                Deutschland<br>
                            @endif
                        @endif
                    @endif
                </td>

                <td width="50%" class="text-right">
                    @if ($logo)
                        <img class="header-logo" style="height:150px" src="{{ \App\Space\ImageUtils::toBase64Src($logo) }}" alt="Company Logo">
                    @else
                        <h1 class="header-logo"> {{ $invoice->customer->company->name }} </h1>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <hr class="header-bottom-divider">

    <div class="content-wrapper">
        <div class="main-content">
            <div class="customer-address-container">
                <div class="billing-address-container billing-address">
                    <b>@lang('pdf_bill_to')</b> <br>
                @if($invoice->customer)
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    @if($invoice->customer->billingAddress)
                        @if($invoice->customer->billingAddress->address_street_1)
                            {{ $invoice->customer->billingAddress->address_street_1 }}<br>
                        @endif
                        @if($invoice->customer->billingAddress->zip || $invoice->customer->billingAddress->city)
                            {{ $invoice->customer->billingAddress->zip }} {{ $invoice->customer->billingAddress->city }}<br>
                        @endif
                        Österreich
                    @endif
                @endif
                </div>

                <div style="clear: both;"></div>
            </div>

            <div class="invoice-details-container">
                <table>
                    <tr>
                        <td class="attribute-label">@lang('pdf_invoice_number')</td>
                        <td class="attribute-value"> &nbsp;{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td class="attribute-label">@lang('pdf_invoice_date')</td>
                        <td class="attribute-value"> &nbsp;{{ $invoice->formattedInvoiceDate }}</td>
                    </tr>
                    <tr>
                        <td class="attribute-label">@lang('pdf_invoice_due_date')</td>
                        <td class="attribute-value"> &nbsp;{{ $invoice->formattedDueDate }}</td>
                    </tr>
                </table>
            </div>
            <div style="clear: both;"></div>
        </div>

        <table width="100%" class="items-table" cellspacing="0" border="0">
    <tr class="item-table-heading-row">
        <th width="2%" class="pr-20 text-right item-table-heading">#</th>
        <th width="40%" class="pl-0 text-left item-table-heading">@lang('pdf_items_label')</th>
        @foreach($customFields as $field)
            <th class="text-right item-table-heading">{{ $field->label }}</th>
        @endforeach
        <th class="pr-20 text-right item-table-heading">@lang('pdf_quantity_label')</th>
        <th class="pr-20 text-right item-table-heading">@lang('pdf_price_label')</th>
        @if($invoice->discount_per_item === 'YES')
        <th class="pl-10 text-right item-table-heading">@lang('pdf_discount_label')</th>
        @endif
        @if($invoice->tax_per_item === 'YES')
        <th class="pl-10 text-right item-table-heading">@lang('pdf_tax_label')</th>
        @endif
        <th class="text-right item-table-heading">@lang('pdf_amount_label')</th>
    </tr>
    @php
        $index = 1
    @endphp
    @foreach ($invoice->items as $item)
        <tr class="item-row">
            <td
                class="pr-20 text-right item-cell"
                style="vertical-align: top;"
            >
                {{$index}}
            </td>
            <td
                class="pl-0 text-left item-cell"
                style="vertical-align: top;"
            >
                <span>{{ $item->name }}</span><br>
                <span class="item-description">{!! nl2br(htmlspecialchars($item->description)) !!}</span>
            </td>
            @foreach($customFields as $field)
                <td class="text-right item-cell" style="vertical-align: top;">
                    {{ $item->getCustomFieldValueBySlug($field->slug) }}
                </td>
            @endforeach
            <td
                class="pr-20 text-right item-cell"
                style="vertical-align: top;"
            >
                {{$item->quantity}} @if($item->unit_name) {{$item->unit_name}} @endif
            </td>
            <td
                class="pr-20 text-right item-cell"
                style="vertical-align: top;"
            >
                {!! format_money_pdf($item->price, $invoice->customer->currency) !!}
            </td>

            @if($invoice->discount_per_item === 'YES')
                <td
                    class="pl-10 text-right item-cell"
                    style="vertical-align: top;"
                >
                    @if($item->discount_type === 'fixed')
                            {!! format_money_pdf($item->discount_val, $invoice->customer->currency) !!}
                        @endif
                        @if($item->discount_type === 'percentage')
                            {{$item->discount}}%
                        @endif
                </td>
            @endif

            @if($invoice->tax_per_item === 'YES')
                <td
                    class="pl-10 text-right item-cell"
                    style="vertical-align: top;"
                >
                    {!! format_money_pdf($item->tax, $invoice->customer->currency) !!}
                </td>
            @endif

            <td
                class="text-right item-cell"
                style="vertical-align: top;"
            >
                {!! format_money_pdf($item->total, $invoice->customer->currency) !!}
            </td>
        </tr>
        @php
            $index += 1
        @endphp
    @endforeach
</table>

<hr class="item-cell-table-hr">

<div class="total-display-container">
    <table width="100%" cellspacing="0px" border="0" class="total-display-table @if(count($invoice->items) > 12) page-break @endif">
        <tr>
            <td class="border-0 total-table-attribute-label">@lang('pdf_subtotal')</td>
            <td class="py-2 border-0 item-cell total-table-attribute-value">
                {!! format_money_pdf($invoice->sub_total, $invoice->customer->currency) !!}
            </td>
        </tr>

        @if($invoice->discount > 0)
            @if ($invoice->discount_per_item === 'NO')
                <tr>
                    <td class="border-0 total-table-attribute-label">
                        @if($invoice->discount_type === 'fixed')
                            @lang('pdf_discount_label')
                        @endif
                        @if($invoice->discount_type === 'percentage')
                            @lang('pdf_discount_label') ({{$invoice->discount}}%)
                        @endif
                    </td>
                    <td class="py-2 border-0 item-cell total-table-attribute-value" >
                        @if($invoice->discount_type === 'fixed')
                            {!! format_money_pdf($invoice->discount_val, $invoice->customer->currency) !!}
                        @endif
                        @if($invoice->discount_type === 'percentage')
                            {!! format_money_pdf($invoice->discount_val, $invoice->customer->currency) !!}
                        @endif
                    </td>
                </tr>
            @endif
        @endif

        @if ($invoice->tax_included)
        <tr>
            <td class="border-0 total-table-attribute-label">
                @lang('pdf_net_total')
            </td>
            <td class="py-2 border-0 item-cell total-table-attribute-value">
                {!! format_money_pdf($invoice->sub_total - $invoice->discount - $invoice->tax, $invoice->customer->currency) !!}
            </td>
        </tr>
        @endif

        @if ($invoice->tax_per_item === 'YES')
            @foreach ($taxes as $tax)
                <tr>
                    <td class="border-0 total-table-attribute-label">
                        @if($tax->calculation_type === 'fixed')
                            {{$tax->name }} ({!! format_money_pdf($tax->fixed_amount, $invoice->customer->currency) !!})
                        @else
                            {{$tax->name.' ('.$tax->percent.'%)'}}
                        @endif
                    </td>
                    <td class="py-2 border-0 item-cell total-table-attribute-value">
                        {!! format_money_pdf($tax->amount, $invoice->customer->currency) !!}
                    </td>
                </tr>
            @endforeach
        @else
            @foreach ($invoice->taxes as $tax)
                <tr>
                    <td class="border-0 total-table-attribute-label">
                        @if($tax->calculation_type === 'fixed')
                            {{$tax->name }} ({!! format_money_pdf($tax->fixed_amount, $invoice->customer->currency) !!})
                        @else
                            {{$tax->name.' ('.$tax->percent.'%)'}}
                        @endif
                    </td>
                    <td class="py-2 border-0 item-cell total-table-attribute-value">
                        {!! format_money_pdf($tax->amount, $invoice->customer->currency) !!}
                    </td>
                </tr>
            @endforeach
        @endif

        <tr>
            <td class="py-3"></td>
            <td class="py-3"></td>
        </tr>
        <tr>
            <td class="border-0 total-border-left total-table-attribute-label">
                @lang('pdf_total')
            </td>
            <td
                class="py-8 border-0 total-border-right item-cell total-table-attribute-value"
                style="color: #00C2A8"
            >
                {!! format_money_pdf($invoice->total, $invoice->customer->currency)!!}
            </td>
        </tr>
    </table>
</div>



<br>

        {{-- Tax Notice for Kleinunternehmer --}}
     <div class="tax-notice">
    <strong>Kleinunternehmerregelung:</strong> Gemäß § 19a UStG (EU-Kleinunternehmerregelung) wird keine Umsatzsteuer ausgewiesen.
</div>


{{-- QR-Code für Überweisung --}}
@if(!empty($qr_code))
<div style="position: absolute; bottom: 160px; right: 30px; text-align: center; border: 1.5px solid #E5E7EB; padding: 10px; border-radius: 1px; background: white; width: 100px;">
    <img src="{{ $qr_code }}" alt="QR Code" style="width: 80px; height: 80px;"><br>
    <span style="font-size: 7pt; color: #6B7280; margin-top: 2px; display: block; line-height: 1.3;">
        <strong style="color: #00C2A8;">Bezahlcode</strong><br>
        {!! format_money_pdf($invoice->total, $invoice->customer->currency) !!}
    </span>
</div>
@endif




       {{-- Footer with bank and contact info --}}
@php
    $fields = $invoice->fields ?? [];
    $bank_name = isset($fields[0]) ? ($fields[0]->string_answer ?? $fields[0]->defaultAnswer ?? '') : '';
    $iban = isset($fields[1]) ? ($fields[1]->string_answer ?? $fields[1]->defaultAnswer ?? '') : '';
    $bic = isset($fields[2]) ? ($fields[2]->string_answer ?? $fields[2]->defaultAnswer ?? '') : '';
    $email = isset($fields[3]) ? ($fields[3]->string_answer ?? $fields[3]->defaultAnswer ?? '') : '';
    $phone = isset($fields[4]) ? ($fields[4]->number_answer ?? $fields[4]->defaultAnswer ?? '') : '';
    $tax_number = isset($fields[5]) ? ($fields[5]->string_answer ?? $fields[5]->defaultAnswer ?? '') : '';
@endphp

<div class="footer-info">
    <table width="100%">
        <tr>
            <td width="33%" style="vertical-align: top;">
                <strong>{{ $invoice->company->name }}</strong><br>
                @if($invoice->company->address)
                    @if($invoice->company->address->address_street_1)
                        {{ $invoice->company->address->address_street_1 }}<br>
                    @endif
                    @if($invoice->company->address->zip || $invoice->company->address->city)
                        {{ $invoice->company->address->zip }} {{ $invoice->company->address->city }}<br>
                    @endif
                    @if($invoice->company->address->country)
                        Deutschland
                    @endif
                @endif
            </td>

            <td width="34%" style="vertical-align: top; text-align: center;">
    <div style="display: inline-block; text-align: left;">
        <strong>Bankverbindung</strong><br>
        Jakob Fankhauser<br>
        @if(!empty($iban))IBAN: {{ $iban }}<br>@endif
        @if(!empty($bic))BIC: {{ $bic }}@endif
    </div>
</td>
            <td width="33%" style="vertical-align: top; text-align: right;">
                <strong>Kontakt</strong><br>
                @if(!empty($email)){{ $email }}<br>@endif
                @if(!empty($phone))+{{ $phone }}<br>@endif
                @if(!empty($tax_number))Steuernr: {{ $tax_number }}@endif
            </td>
        </tr>
    </table>
</div>
      
    </div>
</body>

</html>
