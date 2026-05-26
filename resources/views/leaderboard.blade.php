@extends('layouts.app')

@section('content')
<div class="glass-panel">
    <h2 style="text-align: center; margin-bottom: 30px;">Global Leaderboard 🏆</h2>

    @if($leaders->count() > 0)
        <table style="width: 100%; text-align: left; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <th style="padding: 12px; color: var(--text-muted);">Rank</th>
                    <th style="padding: 12px; color: var(--text-muted);">Scientist</th>
                    <th style="padding: 12px; color: var(--text-muted);">Total Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaders as $index => $leader)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); {{ $index == 0 ? 'background: rgba(251, 191, 36, 0.1);' : '' }}">
                    <td style="padding: 12px; font-weight: 800; color: {{ $index == 0 ? '#fbbf24' : 'inherit' }};">
                        #{{ $index + 1 }}
                    </td>
                    <td style="padding: 12px; font-weight: 600;">
                        {{ $leader->name }}
                        @if($leader->results_sum_score > 50) <span style="font-size:0.8rem; padding: 2px 6px; background: rgba(167, 139, 250, 0.2); color: #a78bfa; border-radius: 4px; margin-left: 8px;">Visionary</span>
                        @elseif($leader->results_sum_score > 30) <span style="font-size:0.8rem; padding: 2px 6px; background: rgba(59, 130, 246, 0.2); color: #60a5fa; border-radius: 4px; margin-left: 8px;">Scientist</span>
                        @elseif($leader->results_sum_score > 10) <span style="font-size:0.8rem; padding: 2px 6px; background: rgba(16, 185, 129, 0.2); color: #34d399; border-radius: 4px; margin-left: 8px;">Scholar</span>
                        @endif
                    </td>
                    <td style="padding: 12px; font-weight: 800; color: var(--success);">
                        {{ $leader->results_sum_score }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="text-align: center; color: var(--text-muted);">No evaluations have been taken yet.</p>
    @endif
</div>
@endsection
