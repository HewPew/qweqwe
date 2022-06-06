<div>
    <p>
        <b>Калькуляция</b>
    </p>

    <p>
        <a href="{{ $data['uriAnketa'] }}">Перейти к калькуляции</a>
    </p>

    <ul>
        <li>ID сделки: {{ $data['calc']->id_deal }}</li>
        <li>Дата: {{ $data['calc']->date }}</li>
        <li>Адрес: {{ $data['calc']->address }}</li>
        <li>Макс. высота этажа: {{ $data['calc']->max_height_floor }}</li>
        <li>Количество зданий: {{ $data['calc']->count_buildings }}</li>
    </ul>
</div>
