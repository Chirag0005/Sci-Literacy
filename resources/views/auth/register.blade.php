@extends('layouts.app')

@section('content')
<div class="glass-panel" style="max-width: 400px; margin: 0 auto;">
    <h2 style="text-align: center;">Create Account</h2>
    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            @error('name') <div style="color: var(--error); margin-top: 5px; font-size: 0.9rem;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            @error('email') <div style="color: var(--error); margin-top: 5px; font-size: 0.9rem;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Password</label>
            <input type="password" name="password" class="form-control" required>
            @error('password') <div style="color: var(--error); margin-top: 5px; font-size: 0.9rem;">{{ $message }}</div> @enderror
        </div>
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; color: var(--text-muted);">Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required>
        </div>
        <button type="submit" class="btn" style="width: 100%;">Sign Up</button>
    </form>
    <p style="text-align: center; margin-top: 20px; color: var(--text-muted);">
        Already have an account? <a href="{{ route('login') }}" style="color: var(--accent);">Login</a>
    </p>
</div>
@endsection
