@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col">
            <data-table endpoint='{{ route($table . '.index') }}'></data-table>
        </div>
    </div>
</div>
@endsection
