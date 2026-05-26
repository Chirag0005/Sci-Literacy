@extends('layouts.app')

@section('content')
<style>
    .question-card {
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.1);
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        transition: transform 0.2s;
    }
    .question-card:hover {
        transform: translateX(5px);
        background: rgba(255,255,255,0.05);
    }
    .question-title {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 15px;
        color: #e2e8f0;
    }
    .option-label {
        display: block;
        padding: 12px 15px;
        margin-bottom: 10px;
        background: rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .option-label:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: var(--accent);
    }
    input[type="radio"] {
        margin-right: 10px;
        accent-color: var(--accent);
    }
</style>

<div class="glass-panel">
    <h2 style="text-align: center;">Scientific Evaluation</h2>
    
    @if ($errors->any())
        <div class="alert alert-error">
            <ul style="margin-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('quiz.submit') }}" method="POST">
        @csrf
        @foreach($questions as $index => $question)
            <div class="question-card">
                <div class="question-title">{{ $index + 1 }}. {{ $question->question_text }}</div>
                
                <label class="option-label">
                    <input type="radio" name="q_{{ $question->id }}" value="A" {{ old('q_'.$question->id) == 'A' ? 'checked' : '' }}> {{ $question->option_a }}
                </label>
                <label class="option-label">
                    <input type="radio" name="q_{{ $question->id }}" value="B" {{ old('q_'.$question->id) == 'B' ? 'checked' : '' }}> {{ $question->option_b }}
                </label>
                <label class="option-label">
                    <input type="radio" name="q_{{ $question->id }}" value="C" {{ old('q_'.$question->id) == 'C' ? 'checked' : '' }}> {{ $question->option_c }}
                </label>
                <label class="option-label">
                    <input type="radio" name="q_{{ $question->id }}" value="D" {{ old('q_'.$question->id) == 'D' ? 'checked' : '' }}> {{ $question->option_d }}
                </label>
            </div>
        @endforeach

        <button type="submit" class="btn" style="width: 100%; margin-top: 20px;">Submit Answers</button>
    </form>
</div>
@endsection
