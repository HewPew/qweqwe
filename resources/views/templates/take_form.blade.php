<p>Количество элементов на странице:

<form action="" method="GET" id="FORM_COUNT_ELEMENTS">
    @include('templates.GET_INPUTS')

    <select onchange="FORM_COUNT_ELEMENTS.submit()" name="take">
        @foreach([20, 50, 100, 250, 500, 1000, 2500, 5000, 10000] as $numb)
            <option
                @isset($take)
                    @if($take == $numb || $take > 500)
                    selected
                    @endif
                @endisset
                value="{{ $numb }}">{{ $take > 500 ? $take : $numb }}</option>
        @endforeach
    </select>
</form>
</p>
