<x-mail::message>
# ⚠️ Alerta de Caducidad de Certificados

Hola,

El sistema ha detectado que los certificados SAT de tu empresa **{{ $company->commercial_name ?? $company->name }}** (RFC: {{ $company->rfc }}) requieren tu atención.

**Detalles de la alerta:**
@foreach($alerts as $alert)
- {{ $alert }}
@endforeach

Te recomendamos generar tus nuevos archivos `.cer` y `.key` en el portal del SAT y actualizarlos en el sistema lo antes posible para evitar interrupciones en tu facturación o en tus descargas automáticas.

<x-mail::button :url="route('companies.csd.form', $company->id)" color="error">
Actualizar Certificados Ahora
</x-mail::button>

Gracias,<br>
El equipo de {{ config('app.name') }}
</x-mail::message>