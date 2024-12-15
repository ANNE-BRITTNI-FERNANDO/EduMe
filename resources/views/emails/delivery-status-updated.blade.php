@component('mail::message')
# Delivery Status Update

Your delivery with tracking number **{{ $tracking->tracking_number }}** has been updated.

**Current Status:** {{ ucwords(str_replace('_', ' ', $tracking->status)) }}  
**Current Location:** {{ $tracking->current_location }}  
**Estimated Delivery:** {{ $tracking->estimated_delivery_date->format('M d, Y') }}

@if($tracking->delivery_notes)
**Delivery Notes:**  
{{ $tracking->delivery_notes }}
@endif

@component('mail::button', ['url' => route('delivery.track', $tracking->tracking_number)])
Track Your Delivery
@endcomponent

Thank you for using our service!

Best regards,  
{{ config('app.name') }}
@endcomponent
