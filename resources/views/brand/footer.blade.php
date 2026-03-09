@guest
    <p class="small text-center m-4 px-5">
        {{ __('Escuela Nacional de Enfermería de Cobán e INDAPSV - ') }} 
    </p>
@else
    <div class="text-center user-select-none my-4 d-none d-lg-block">
        <p class="small mb-0">
            {{ __('Escuela Nacional de Enfermería de Cobán e INDAPSV') }}  {{date('Y')}}
        </p>
    </div>
@endguest
