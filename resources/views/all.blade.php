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
                <a class="col-md-1 col-sm-2 col-xs-3" href="http://twitter.com/{{ $username->username }}">
                    <div class="pic-container"><img src="{{ $username->profile_pic }}" class="img-responsive"/></div>
                    <div class="username">{{ $username->username }}</div>
                    <div class="name">{{ $username->name }}</div>
                    <div class="status">{{ $username->status }}</div>
                    <div class="last-checked" title="Last date checked">{{ $username->last_checked }}</div>
                    <div class="registered" title="Date registered">{{ $username->date_registered }}</div>
                </a>
            @endforeach
        </div>
    @empty
        <div><strong>No usernames this long found</strong></div>
    @endforelse
    {!! $users->render() !!}
@endsection