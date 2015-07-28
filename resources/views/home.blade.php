@extends('layout.app')

@section('content')
    <div>Name length</div>
    <ul class="pagination">
        @foreach($lengths as $l)
            <li {{ ($l == $length) ? 'class=active' : ''  }}><a href="{{ url('/'.$l) }}">{{ $l }}</a></li>
        @endforeach
    </ul>
    @forelse($users->chunk(12) as $chunk)
        <div class="row">
            @foreach($chunk as $username)
                <a class="col-md-1 col-sm-2 col-xs-3" href="http://twitter.com/{{ $username->username }}" target="_blank">
                    <div class="pic-container"><img src="{{ $username->profile_pic }}" class="img-responsive"/></div>
                    <div class="username">{{ $username->username }}</div>
                    <div class="last-checked" title="Last date checked">{{ $username->last_checked }}</div>
                </a>
            @endforeach
        </div>
    @empty
        <div><strong>No Available Usernames Found</strong></div>
        @if($last == null)
            <div>However, there are still usernames this length that are still being checked</div>
        @else
            <div>The last {{ $length }} character name available was "{{ $last->username }}" snapped up on {{ $last->date_registered }}</div>
        @endif
        <div><a href="{{ url('/all/'.$length) }}">Show all users</a></div>
    @endforelse
@endsection