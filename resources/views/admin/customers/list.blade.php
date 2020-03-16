@extends('layouts.admin.app')

@section('content')
    <!-- Main content -->
    <section class="content">

    @include('layouts.errors-and-messages')
    <!-- Default box -->
        @if($elements)
            <div class="box">
                <div class="box-body">
                    <h2>Customers</h2>
                    @include('layouts.search', ['route' => route('admin.customers.index')])
                    <table class="table">
                        <thead>
                            <tr>
                                <td class="col-md-2">ID</td>
                                <td class="col-md-2">Name</td>
                                <td class="col-md-2">Email</td>
                                <td class="col-md-2">Status</td>
                                <td class="col-md-4">Actions</td>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach ($elements as $element)
                            <tr>
                                <td>{{ $element['id'] }}</td>
                                <td>{{ $element['name'] }}</td>
                                <td>{{ $element['email'] }}</td>
                                <td>@include('layouts.status', ['status' => $element['status']])</td>
                                <td>
                                    <form action="{{ route('admin.customers.destroy', $element['id']) }}" method="post" class="form-horizontal">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="_method" value="delete">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.customers.show', $element['id']) }}" class="btn btn-default btn-sm"><i class="fa fa-eye"></i> Show</a>
                                            <a href="{{ route('admin.customers.edit', $element['id']) }}" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i> Edit</a>
                                            <button onclick="return confirm('Are you sure?')" type="submit" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Delete</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {{ $elements->links() }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        @endif

    </section>
    <!-- /.content -->
@endsection