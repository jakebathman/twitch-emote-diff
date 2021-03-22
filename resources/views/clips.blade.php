@extends('layouts.app')

@section('content')
<div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">

    <h1 class="font-bold text-lg">Count: {{ $clipsByDate->count() }}</h1>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

        @foreach (['Date' => $clipsByDate, 'Views' => $clipsByViews] as $sort => $clips)
        <div>
            <h2 class="font-bold text-center text-lg text-teal-700 sticky top-0 bg-gray-50 py-3">Sorted by {{ $sort }}</h2>
            <table class="table-fixed w-full">
                <thead>
                    <tr>
                        <th class="w-1/12 px-2 py-2 sticky top-10 bg-teal-100">&nbsp;</th>
                        <th class="w-2/12 px-4 py-2 sticky top-10 bg-teal-100">Created At</th>
                        <th class="w-auto px-4 py-2 sticky top-10 bg-teal-100">Views</th>
                        <th class="w-3/12 px-4 py-2 sticky top-10 bg-teal-100">Creator</th>
                        <th class="w-2/12 px-4 py-2 sticky top-10 bg-teal-100">Title</th>
                        <th class="w-2/12 px-4 py-2 sticky top-10 bg-teal-100">ID</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($clips as $k => $clip)
                    <tr>
                        <td class="border px-2 py-2">{{ $k+1 }}</td>
                        <td class="border px-4 py-2">{{ $clip->created_at }}</td>
                        <td class="border px-4 py-2">{{ number_format($clip->view_count) }}</td>
                        <td class="border px-4 py-2">{{ $clip->creator_name }}</td>
                        <td class="border px-4 py-2">{{ $clip->title }}</td>
                        <td class="border break-words px-4 py-2"><a class="text-teal-500" href="{{ $clip->url }}"
                                target="_blank">{!! $clip->clipIdWithBreaks() !!}</a></td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
        @endforeach

    </div>
</div>
@endsection
