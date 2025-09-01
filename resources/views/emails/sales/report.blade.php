<x-mail::message>
# Daily Sale Summary ({{ $branch_name }})

Date: {{ $date->format('d/m/Y')}}

<x-mail::panel>
@foreach($cards as $card)
@if($card['name'])
{{ Str::of($card['name']) }} : {{$card['amount']}} <br>
@else
<br>
@endif
@endforeach
</x-mail::panel>
 
Thanks,<br>
{{ config('app.name') }}
</x-mail::message>