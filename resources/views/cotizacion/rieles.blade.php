<div id="rielesTradicional">
    <span class="bs-title mb-10">DISEÑO DEL RIEL TRADICIONAL</span>
    @php
    $bnd_color = true;
    @endphp
    <div class="row row-cols-1 row-cols-md-3 g-6 mb-4 mt-1">
        @foreach ($cards_rieles_tradicional as $index => $item)

        <div class="col">
            <div class="card">
                <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap"
                    onclick="showModal('{{ asset('images/' . $item['image']) }}')" style="cursor: pointer;">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_riel_Tradicional"
                            id="radioRielTradicional_{{ $loop->index }}" value="{{$item['opcion_radio']}}"
                            @if($item['a_selected']=='true' ) checked @endif
                            onclick="handleRielChange('Tradicional', {{ $loop->index }})">
                        <label class="form-check-label"
                            for="radio2_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                    </div>

                    <!-- SELECCIÓN DE COLOR -->
                    @php $total_colors = count($item['colors']); @endphp
                    <div class="color-container"
                        style="grid-template-rows: {{ $total_colors > 5 ? 'repeat(2, auto)' : 'repeat(1, auto)' }}; grid-template-columns: repeat(ceil({{ $total_colors }} / 2), 1fr);"
                        id="color-options-{{ $index }}">

                        @foreach ($item['colors'] as $color_index => $color_name)
                        @php $color_value = $color_palette[$color_name] ?? '#CCCCCC'; @endphp
                        <div class="color-option 
                                                {{ $bnd_color == true ? 'selected-color' : '' }}"
                            data-color="{{ $color_name }}" data-value="{{ $color_value }}"
                            data-group="color-group-{{ $index }}" onclick="selectColor(this, '{{ $index }}')"
                            style="background-color: {{ $color_value }};" title="{{ ucfirst($color_name) }}">
                        </div>
                        @php
                        $bnd_color = false;
                        @endphp
                        @endforeach

                    </div>

                </div>
            </div>
        </div>
        @endforeach


    </div>
</div>
<div id="rielesRipplefold" style="display: none;">
    <span class="bs-title mb-10">DISEÑO DEL RIEL RIPPLEFOLD</span>
    @php
    $bnd_color = true;
    @endphp
    <div class="row row-cols-1 row-cols-md-3 g-6 mb-4 mt-1">
        @foreach ($cards_rieles_ripplefold as $index => $item)

        <div class="col">
            <div class="card">
                <img class="card-img-top" src="{{ asset('images/' . $item['image'])}}" alt="Card image cap"
                    onclick="showModal('{{ asset('images/' . $item['image']) }}')" style="cursor: pointer;">
                <div class="card-body">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="radio_riel_Ripplefold"
                            id="radioRielRipplefold_{{ $loop->index }}" value="{{$item['opcion_radio']}}"
                            @if($item['a_selected']=='true' ) checked @endif
                            onclick="handleRielChange('Ripplefold', {{ $loop->index }})">
                        <label class="form-check-label"
                            for="radio2_{{ $loop->index }}">{{$item['opcion_radio']}}</label>
                    </div>

                    <!-- SELECCIÓN DE COLOR -->
                    @php $total_colors = count($item['colors']); @endphp
                    <div class="color-container"
                        style="grid-template-rows: {{ $total_colors > 5 ? 'repeat(2, auto)' : 'repeat(1, auto)' }}; grid-template-columns: repeat(ceil({{ $total_colors }} / 2), 1fr);"
                        id="color-options-{{ $index }}">

                        @foreach ($item['colors'] as $color_index => $color_name)
                        @php $color_value = $color_palette[$color_name] ?? '#CCCCCC';
                        @endphp
                        <div class="color-option 
                            {{ $bnd_color == true ? 'selected-color' : '' }}" data-color="{{ $color_name }}"
                            data-value="{{ $color_value }}" data-group="color-group-{{ $index }}"
                            onclick="selectColor(this, '{{ $index }}')" style="background-color: {{ $color_value }};"
                            title="{{ ucfirst($color_name) }}">
                        </div>
                        @php
                        $bnd_color = false;
                        @endphp
                        @endforeach
                    </div>

                </div>
            </div>
        </div>
        @endforeach

    </div>
</div>