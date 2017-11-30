@extends('welcome')

@section('content')

    <div class="panel-body">
        <!-- Отображение ошибок проверки ввода -->
        @include('common.errors')
    </div>
    <!-- Состав -->
    <div class="panel panel-default">
        <div>
            <h5>{{ $food->name }}</h5>
        </div>
            <table class="table table-striped task-table">
                <thead>
                <th>Ингредиент</th>
                <th>Масса, г.</th>
                <th>Стоимость (за 100г)</th>
                </thead>
                <!-- Тело таблицы -->
                @foreach ($ingredients as $ingredient)
                    <tr>
                        <!-- Ингредиент -->
                        <td class="table-text">
                            <div>{{ $ingredient->name }}</div>
                        </td>
                        <!-- Масса -->
                        <td class="table-text">
                            <div>{{ $ingredient->pivot->mass }}</div>
                        </td>
                        <!-- Стоимость ингредиента -->
                        <td class="table-text">
                            <div>{{ $ingredient->prices->sortByDesc('dateTime')->first()->price }}</div>
                        </td>
                        <!-- Кнопка Удалить -->
                        <td>
                            <form action="{{ url('food/'.$food->id.'/content/'.$ingredient->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}

                                <button type="submit" class="btn btn-danger">
                                    <i class="fa fa-trash"></i> Удалить
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
    </div>
    <div>
        <table class="table table-striped task-table">

            <!-- Заголовок таблицы -->
            <thead>
            <th>ID</th>
            <th>Ингредиент</th>
            </thead>
            <!-- Тело таблицы -->
            <tbody>
            @foreach ($allIngredients as $oneIngredient)
                <form action="{{ url('food/'.$food->id.'/content/'.$oneIngredient->id) }}" method="POST" class="form-horizontal">
                {{ csrf_field() }}
                    <tr>
                        <!-- ID ингредиента -->
                        <td class="table-text">
                            <div>{{ $oneIngredient->id }} </div>
                        </td>
                        <!-- Ингредиент -->
                        <td class="table-text">
                            <div>{{ $oneIngredient->name }}</div>
                        </td>
                        <td>
                            <div class="form-group">
                                <label for="role" class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <input type="text" name="mass" id="ingredient-mass" class="form-control" placeholder="Масса г.">
                                </div>
                            </div>
                        </td>
                        <!-- Кнопка Добавить -->
                        <td class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-default">
                                    <i class="fa fa-plus"></i> Добавить
                                </button>
                            </div>
                        </td>
                    </tr>
                </form>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection