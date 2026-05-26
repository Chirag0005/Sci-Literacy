@extends('layouts.app')

@section('content')
<div class="glass-panel" style="max-width: 900px; margin: 0 auto;">
    <div style="text-align: center; margin-bottom: 40px;">
        <span style="font-size: 3rem;">🕵️‍♂️</span>
        <h2 style="margin-top: 10px;">Science Mythbusters</h2>
        <p style="color: var(--text-muted); font-size: 1.1rem;">Common scientific misconceptions debunked by AI.</p>
    </div>

    @if(empty($myths))
        <div class="alert alert-error" style="text-align: center;">
            Unable to load myths at this time. Please check back later!
        </div>
    @else
        <div style="display: grid; gap: 25px;">
            @foreach($myths as $index => $item)
                <div style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 25px; transition: transform 0.3s; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -10px; right: 10px; font-size: 6rem; opacity: 0.03; font-weight: 800;">
                        {{ $index + 1 }}
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <span style="background: rgba(239, 68, 68, 0.15); color: #fca5a5; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; border: 1px solid rgba(239, 68, 68, 0.3);">
                            ❌ The Myth
                        </span>
                        <h3 style="margin-top: 15px; font-size: 1.3rem; color: #e2e8f0; line-height: 1.5;">
                            "{{ $item['myth'] ?? 'Myth text unavailable' }}"
                        </h3>
                    </div>
                    
                    <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid var(--success); padding: 15px 20px; border-radius: 0 8px 8px 0;">
                        <span style="color: #6ee7b7; font-weight: 800; display: block; margin-bottom: 8px;">✅ The Scientific Fact</span>
                        <p style="color: #cbd5e1; line-height: 1.6; margin: 0;">
                            {{ $item['fact'] ?? 'Fact unavailable' }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
