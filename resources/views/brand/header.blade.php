@push('head')
    <meta name="robots" content="noindex"/>
    <meta name="google" content="notranslate">
    <!-- Puedes cambiar el favicon aquí si lo deseas -->
    <link
          href="{{ asset('images/logoico.ico') }}"
          type="image/x-icon"
          id="favicon"
          rel="icon"
    >

    <!-- For Safari on iOS -->
    <meta name="theme-color" content="#000000ff">

    <style>
        /* Cambiar el fondo del panel lateral */
        .aside.bg-dark {
            background-color: #000000ff !important;
        }
        @media (min-width: 992px) {
            .aside.bg-dark {
                position: sticky !important;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                align-self: flex-start;
            }
        }
        /* Invertir el logo SOLO cuando está dentro de la marca del cabezal en la barra lateral */
        .header-brand img[alt="Logo SGP"] {
            filter: invert(1) brightness(100%) !important;
        }
    </style>
@endpush

<div class="h2 d-flex align-items-center">
    @auth
        <x-orchid-icon path="bs.house" class="d-inline d-lg-none"/>
    @endauth
    
    <div class="{{ auth()->check() ? 'd-none d-lg-block' : '' }} text-center">
        
        
        <div class="mt-3">
            <img src="{{ asset('images/logo2.png') }}" alt="Logo SGP" style="height: 8rem;">
        </div>
    
        <p class="my-0 fw-bold">
            Sistema de Control Patrimonial
        </p>
    </div>
</div>
