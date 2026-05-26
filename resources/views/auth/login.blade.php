@extends('layouts.app')

@section('content')
<div class="glass-panel" style="max-width: 400px; margin: 0 auto;">
    <h2 style="text-align: center;">Welcome Back</h2>
    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div style="color: var(--error); margin-top: 5px; font-size: 0.9rem;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn" style="width: 100%;">Login</button>
    </form>
    <p style="text-align: center; margin-top: 20px; color: var(--text-muted);">
        Don't have an account? <a href="{{ route('register') }}" style="color: var(--accent);">Sign Up</a>
    </p>
</div>
@endsection
