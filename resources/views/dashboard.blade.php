@extends('layouts.app')

@section('content')
<div class="glass-panel">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2>Hello, {{ $user->name }} 👋</h2>
        <a href="{{ route('quiz.show') }}" class="btn">Take a Quiz</a>
    </div>

    @if(!empty($dailyInsight))
    <div style="background: linear-gradient(135deg, rgba(167, 139, 250, 0.15), rgba(139, 92, 246, 0.05)); border: 1px solid rgba(167, 139, 250, 0.3); padding: 25px; border-radius: 12px; margin-bottom: 40px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: -20px; right: -20px; font-size: 8rem; opacity: 0.05;">✨</div>
        <h3 style="color: #a78bfa; margin-top: 0; display: flex; align-items: center; gap: 8px;">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Daily Science Insight
        </h3>
        <p style="font-size: 1.1rem; line-height: 1.6; margin-bottom: 0; color: #e2e8f0; font-style: italic;">
            "{{ $dailyInsight }}"
        </p>
    </div>
    @endif

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 12px; text-align: center;">
            <h4 style="color: var(--text-muted); margin-bottom: 10px;">Scientific Rank</h4>
            <div style="font-size: 1.8rem; font-weight: 800; color: #a78bfa;">{{ $rank }}</div>
        </div>
        <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 12px; text-align: center;">
            <h4 style="color: var(--text-muted); margin-bottom: 10px;">Total Score</h4>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--success);">{{ $totalScore }}</div>
        </div>
        <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 12px; text-align: center;">
            <h4 style="color: var(--text-muted); margin-bottom: 10px;">Average Accuracy</h4>
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--accent);">{{ $average }}%</div>
        </div>
    </div>

    <h3>Your Past Evaluations</h3>
    @if($results->count() > 0)
        <table style="width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="padding: 12px; color: var(--text-muted);">Date</th>
                    <th style="padding: 12px; color: var(--text-muted);">Score</th>
                    <th style="padding: 12px; color: var(--text-muted);">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results as $result)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                    <td style="padding: 12px;">{{ $result->created_at->format('M d, Y') }}</td>
                    <td style="padding: 12px;">{{ $result->score }} / {{ $result->total_questions }}</td>
                    <td style="padding: 12px;">
                        <a href="{{ route('results.show', $result->id) }}" style="color: var(--accent); text-decoration: none;">View Details & AI Analysis</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: var(--text-muted);">You haven't taken any quizzes yet.</p>
    @endif
</div>
@endsection
