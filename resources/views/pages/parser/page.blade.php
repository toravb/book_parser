@extends('layouts.main')
@section('title', 'Pages')
@section('content')
    <?
    use Illuminate\Support\Facades\DB;
    $pages = DB::table('books_pages')->where('book_id', '=', 1)->paginate(1);
    ?>
    <div class="card">
        <!-- /.card-header -->
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>page #</th>
                    <th>content</th>
                </tr>
                </thead>
                <tbody>
                @foreach($pages as $page)
                    <tr>
                        <td>{{$page->book_page}}</td>
                        <td>{{$page->content}}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        <div class="card-footer clearfix">
            <ul class="pagination pagination-sm m-0 float-right">
                {{$pages}}
            </ul>
        </div>
    </div>







@endsection
