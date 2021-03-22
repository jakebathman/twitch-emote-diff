@extends('layouts.app')

@section('content')
<div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">

    <h1>Tracked Channels</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">

        @foreach ($channels as $channel)

        <a class="flex flex-col items-center justify-center bg-white p-4 shadow rounded-lg" href="{{ route('emotes', $channel->name) }}">
            <div class="inline-flex shadow-lg border border-gray-200 rounded-full overflow-hidden h-40 w-40">
                <img src="{{ $channel->getImageUrl() }}"
                     alt=""
                     class="h-full w-full">
            </div>

            <h2 class="flex-1 mt-4 font-bold text-xl text-purple-800">{{ $channel->display_name }}</h2>
            <h6 class="flex-1 mt-2 text-sm font-medium text-purple-400">{{ $channel->broadcaster_type }}</h6>

            <p class="flex-1 text-xs text-gray-500 text-center mt-3">
                {{ $channel->description }}
            </p>

            <div class="flex-1">
                <ul class=" flex flex-row mt-4 space-x-2 justify-evenly">
                    @foreach ($channel->plans->first()->emotes->shuffle()->take(3) as $emote)
                        <li>
                            <div href="" class="flex items-center justify-center h-8 w-8 border rounded-full overflow-hidden text-gray-800 border-gray-800">
                                <img src="{{ $emote->getImageUrl() }}"/>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 text-gray-800">View emote changes</div>
            </div>
        </a>





        @endforeach




    </div>


</div>
@endsection
