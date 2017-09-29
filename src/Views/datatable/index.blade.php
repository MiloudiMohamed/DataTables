@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">

        <data-table endpoint='{{ route($table . '.index') }}'></data-table>
    </div>
</div>
@endsection
