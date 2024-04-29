@extends('layouts.model.index')

@section('title')
    {{ 'Category Index' }}
@endsection
@section('content-header')
    {{ 'Cat:Index' }}
@endsection
@section('content-actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">Create Category</a>
@endsection
@section('variables')
    @php($tableHeaders = ['Id', 'Name', 'Description', 'Image', 'Kitchen Printer IP', 'Products', 'Actions'])
    @php($varValue = 'test-value')
    @php($varData = ['test' => 'data'])
@endsection

@section('route-index', route('categories.index'))

@section('table-rows')

    @foreach ($categories as $category)
        <tr>
            <td>{{ $category->id }}</td>
            <td><a href="{{ route('categories.show', $category) }}">{{ $category->name }}</a></td>

            <td>{{ $category->description }}</td>
            <td>
                <img width="50" src="{{ $category->image }}" alt="">
            </td>
            <td>{{ $category->kitchen_printer_ip }}</td>
            <td>
                <ul>
                    @foreach ($category->products()->get() as $product)
                        <li><a>{{ $product->name }}</a></li>
                    @endforeach
                </ul>
            </td>
            <td>
                <a href="{{ route('categories.edit', $category) }}" class="btn btn-primary"><i class="fas fa-edit"></i></a>
                <a class="btn btn-danger btn-delete" href="{{ route('categories.destroy', $category) }}"><i
                        class="fas fa-trash"></i></a>
            </td>
        </tr>
    @endforeach

@endsection
