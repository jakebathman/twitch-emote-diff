@extends('layouts.app')

@section('content')
<div class="flex flex-col justify-center min-h-screen py-12 bg-gray-50 sm:px-6 lg:px-8">

    <h1>{{ $channel->display_name }}</h1>

        <div>
        @foreach ($channel->plans as $plan)
            <h2>{{ $plan->label }}</h2>

            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 lg:grid-cols-7 xl:grid-cols-8">
                @foreach ($plan->emotes as $emote)
                    <div class="shadow-lg border border-gray-200 w-2/5 m-2">
                        <img src="{{ $emote->getImageUrl() }}"
                            alt="{{ $emote->code }}"
                            class="">
                        <div>{{ $emote->code }}</div>
                    </div>
                @endforeach
            </div>
        @endforeach

    </div>

</div>
@endsection
