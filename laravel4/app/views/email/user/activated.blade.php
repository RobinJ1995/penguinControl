@extends ('email.template')

@section ('content')
<p>Beste {{ $userInfo->getFullName () }}</p>
<p>Je SIN-account is goedgekeurd en geactiveerd. Je zou je nu moeten kunnen aanmelden op sinners.be en op onze andere diensten.<br />
Indien er zich problemen voordoen, twijfel dan zeker niet om <a href="https://sinners.be/page/contact">contact met ons op te nemen</a>.</p>
@endsection