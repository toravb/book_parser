@extends('layouts.main')
@section('title', 'Parser')
@section('content')
    <div style="display: none">
{{--    @foreach($parsingStatus as $element)--}}
{{--                @if($element->Count == 0)--}}
{{--                    {{$element->Count = 1}}--}}
{{--                @endif--}}
{{--    @endforeach--}}
    </div>
    <div class="container container-table">
        <div class="sub-container">
            @if($site->downloadedExcel)
                <div class="alert alert-success">
                    <p>Excel генерируется</p>
                </div>
            @endif
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif
            <form class="form-horizontal" method="post" action="{{route('parser.add.pages')}}">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <div class="col-sm-10">
                            <input name="site" type="hidden" class="form-control" id="site" value="{{$site->site}}">
                            <input name="id" type="hidden" class="form-control" id="id" value="{{$site->id}}">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-block btn-outline-secondary btn-lg">Добавить все страницы на переобход</button>
                </div>
            </form>
            <form class="form-horizontal" method="post" action="{{route('parser.parse.sitemap')}}">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <div class="col-sm-10">
                            <input name="site_id" type="hidden" class="form-control" id="site_id" value="{{$site->id}}">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-block btn-outline-secondary btn-lg" >
                        пересобрать sitemap
                    </button>
                </div>
            </form>
            @foreach($parsingStatus as $element)
                @if($element->parse_type == 'sitemap' && $element->Count != 0)

                    <span class="sr-only">
                                @if($element->last_parsing != null)
                            {{$element->last_parsing}} ({{$element->Count}}страниц)
                        @else
                            Статус сбора sitemap({{$element->Progress}}/{{$element->Count}})
                        @endif
                            </span>
                    <div class="progress">

                        <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{$element->Progress}}"
                             aria-valuemin="0" aria-valuemax="{{$element->Count}}"
                             style="width: {{($element->Progress/$element->Count) * 100}}%">
                        </div>
                    </div>
                @endif
            @endforeach
        <div class="form-container">
            <form class="form-horizontal" method="post" action="{{route('parser.parse.page.post')}}">
                {{ csrf_field() }}
                <div class="box-body">
                    <div class="form-group">
                        <div class="col-sm-10">
                            <input name="doParsePages" type="hidden" class="form-control" id="doParsePages" value="{{$site->doParsePages}}">
                            <input name="site" type="hidden" class="form-control" id="site" value="{{$site->site}}">
                            <input name="id" type="hidden" class="form-control" id="id" value="{{$site->id}}">
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-block btn-outline-secondary btn-lg"
                            style="background-color: <?=(!$site->doParsePages)?'':'red'?>">
                        @if($site->doParsePages) Выключить парсинг страниц @else Включить парсинг страниц @endif
                </div>
            </form>
            @if($statuses['page'] == 0)
                <span class="badge badge-success">
                    Парсер страниц готов к работе
                </span>
{{--            @else--}}
            @endif
            @foreach($parsingStatus as $element)
                @if($element->parse_type == 'page' && $element->Count != 0)

                    <span class="sr-only">
                                @if($element->last_parsing != null)
                            {{$element->last_parsing}} ({{$element->Progress}}/{{$element->Count}})
                        @else

                            Статус парсинга страниц({{$element->Progress}}/{{$element->Count}})

                            </span>
                    <div class="progress">

                        <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{$element->Progress}}"
                             aria-valuemin="0" aria-valuemax="{{$element->Count}}"
                             style="width: {{($element->Progress/$element->Count) * 100}}%">
                        </div>
                    </div>
                @endif
                @endif
            @endforeach
        </div>
    <form class="form-horizontal" method="post" action="{{route('parser.parse.pageImage')}}">
        {{ csrf_field() }}
        <div class="box-body">
            <div class="form-group">
                <div class="col-sm-10">
                    <input name="doParseImages" type="hidden" class="form-control" id="doParseImages" value="{{$site->doParseImages}}">
                    <input name="site" type="hidden" class="form-control" id="site" value="{{$site->site}}">
                    <input name="id" type="hidden" class="form-control" id="id" value="{{$site->id}}">
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-block btn-outline-secondary btn-lg" style="background-color: <?=(!$site->doParseImages)?'':'red'?>">

                @if($site->doParseImages) Выключить парсинг изображений @else Включить парсинг изображений @endif
            </button>
        </div>
    </form>
            @if($statuses['image'] == 0)
                <span class="badge badge-success">
                    Парсер изображений готов к работе
                </span>
{{--            @else--}}
            @endif
            @foreach($parsingStatus as $element)
                @if($element->parse_type == 'image' && $element->Count != 0)
                    <span class="sr-only">
                                @if($element->last_parsing != null)
                            {{$element->last_parsing}} ({{$element->Progress}}/{{$element->Count}})
                        @else
                            Статус парсинга картинок({{$element->Progress}}/{{$element->Count}})

                            </span>
                    <div class="progress">

                        <div class="progress-bar bg-info" role="progressbar" aria-valuenow="{{$element->Progress}}"
                             aria-valuemin="0" aria-valuemax="{{$element->Count}}"
                             style="width: {{($element->Progress/$element->Count) * 100}}%">
                        </div>
                    </div>
                @endif
                    @endif
            @endforeach
            <form class="form-horizontal" method="get" action="{{route('parser.parse.generate')}}">
                <div class="col-sm-10">
                    <input name="id" type="hidden" class="form-control" id="id" value="{{$site->id}}">
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-block btn-outline-secondary btn-lg"
                       @if($site->downloadedExcel)
                       disabled="disabled"
                       @endif
                    >Генерировать excel</button>
                </div>
            </form>
            <form class="form-horizontal" method="get" action="{{route('parser.parse.download')}}">
                <div class="col-sm-10">
                <input name="id" type="hidden" class="form-control" id="id" value="{{$site->id}}">
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-block btn-outline-secondary btn-lg"
                       @if($site->downloadedExcel)
                       disabled="disabled"
                       @endif
                    >Скачать excel</button>
                </div>
            </form>

    </div>
        <div class="card container-card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>Всего в базе</th>
                        <th>В очереди на парсинг</th>

                    </tr>
                    </thead>
                    <tbody>
                        <td>{{$counts['countPages']}} страниц</td>
                        <td>{{$counts['toParsePages']}} страниц</td>
                    </tbody>
                    <tbody>
                        <td>{{$counts['countImages']}} изображений</td>
                        <td>{{$counts['toParseImages']}} изображений</td>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
