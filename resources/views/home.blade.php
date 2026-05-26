@extends('layouts.app')

@section('content')
<div class="glass-panel" style="text-align: center;">
    <h1>Discover Your Scientific Temper</h1>
    <p style="color: var(--text-muted); margin-bottom: 30px; font-size: 1.1rem; line-height: 1.6;">
        Welcome to the Science Literacy Evaluation Platform. Test your understanding of fundamental scientific concepts and methodology.
    </p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @auth
        <a href="{{ route('quiz.show') }}" class="btn" style="width: 100%; max-width: 300px; padding: 15px; font-size: 1.2rem;">Start Evaluation</a>
    @else
        <a href="{{ route('login') }}" class="btn" style="width: 100%; max-width: 300px; padding: 15px; font-size: 1.2rem;">Log In to Start</a>
    @endauth
</div>
@endsection
