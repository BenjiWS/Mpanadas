{{--
    Distribución de los formularios chicos (categorías, ingredientes, usuarios, perfil), según el ancho
    del contenido (container queries):
    - angosto: una columna, en orden formulario, vista previa, contexto y eliminar;
    - desde 60rem: dos columnas independientes (formulario + eliminar | vista previa + contexto);
    - desde 100rem: tres columnas (formulario + eliminar | vista previa | contexto).
    Cada columna apila lo suyo sin compartir filas, así una columna alta no deja huecos en las otras.
    El formulario nunca pasa de 46rem, así los campos no se estiran en pantallas grandes.
    Slots: el formulario va en el slot por defecto; $preview, $context y $danger son opcionales.
--}}
@php
    $hasPreview = isset($preview) && $preview->hasActualContent();
    $hasContext = isset($context) && $context->hasActualContent();
    $hasDanger = isset($danger) && $danger->hasActualContent();
@endphp

<div class="@container">
    <div class="grid items-start gap-8 @[60rem]:grid-cols-[minmax(0,46rem)_minmax(20rem,26rem)] @[100rem]:grid-cols-[minmax(0,46rem)_minmax(22rem,28rem)_minmax(20rem,26rem)]">
        {{-- Columna 1: formulario y, debajo, eliminar. En una sola columna sus hijos se ordenan con el resto (order). --}}
        <div class="contents @[60rem]:col-start-1 @[60rem]:row-start-1 @[60rem]:flex @[60rem]:flex-col @[60rem]:gap-8">
            <div class="order-1 min-w-0 space-y-8">
                {{ $slot }}
            </div>

            @if ($hasDanger)
                <div class="order-4 min-w-0">
                    {{ $danger }}
                </div>
            @endif
        </div>

        {{-- Columna 2: vista previa y contexto; desde 100rem el contexto pasa a la tercera columna --}}
        @if ($hasPreview || $hasContext)
            <div class="contents @[60rem]:col-start-2 @[60rem]:row-start-1 @[60rem]:flex @[60rem]:flex-col @[60rem]:gap-6 @[100rem]:contents">
                @if ($hasPreview)
                    <div class="order-2 min-w-0 space-y-4 @[100rem]:col-start-2 @[100rem]:row-start-1">
                        {{ $preview }}
                    </div>
                @endif

                @if ($hasContext)
                    <div class="order-3 min-w-0 space-y-4 @[100rem]:col-start-3 @[100rem]:row-start-1 @[100rem]:pt-9">
                        {{ $context }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
