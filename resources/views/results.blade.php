@extends('layouts.app')

@section('content')
<style>
    .score-circle {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        background: conic-gradient(var(--accent) {{ ($result->score / max(1, $result->total_questions)) * 100 }}%, rgba(255,255,255,0.1) 0);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 30px;
        position: relative;
    }
    .score-inner {
        width: 130px;
        height: 130px;
        background: var(--card-bg);
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
    }
    .ai-feedback {
        background: rgba(167, 139, 250, 0.15);
        border: 1px solid rgba(167, 139, 250, 0.3);
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 40px;
        text-align: left;
    }
    .ai-feedback h3 {
        color: #a78bfa;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .summary-card {
        background: rgba(0,0,0,0.2);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        text-align: left;
    }
    .summary-correct { border-left: 4px solid var(--success); }
    .summary-incorrect { border-left: 4px solid var(--error); }
</style>

<div class="glass-panel" style="text-align: center;">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <h2>Your Results, {{ $result->user->name }}</h2>
    
    <div class="score-circle">
        <div class="score-inner">
            {{ $result->score }}<span style="font-size: 1rem; color: var(--text-muted);">/ {{ $result->total_questions }}</span>
        </div>
    </div>

    @if($result->ai_feedback)
        <div class="ai-feedback">
            <h3>✨ AI Scientific Analysis</h3>
            <p style="line-height: 1.6; color: #e2e8f0;">{{ $result->ai_feedback }}</p>
        </div>
    @else
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 30px;">
            @if($result->score == $result->total_questions)
                Exceptional! You have a profound scientific temper.
            @elseif($result->score >= $result->total_questions / 2)
                Good job! You have a solid understanding of scientific principles.
            @else
                Keep learning! Science is a journey of continuous discovery.
            @endif
        </p>
    @endif

    @if(!empty($summary))
        <h3 style="text-align: left; margin-top: 30px;">Question Breakdown</h3>
        @foreach($summary as $item)
            @php
                $q = $item['question'];
                $isCorrect = $item['is_correct'];
                $userAns = $item['user_answer'];
                $correctAns = $q->correct_option;
                $options = ['A' => $q->option_a, 'B' => $q->option_b, 'C' => $q->option_c, 'D' => $q->option_d];
            @endphp
            <div class="summary-card {{ $isCorrect ? 'summary-correct' : 'summary-incorrect' }}">
                <p style="font-weight: 600; margin-bottom: 10px;">{{ $q->question_text }}</p>
                <div style="font-size: 0.95rem; margin-bottom: 10px; color: #cbd5e1;">
                    Your Answer: <span style="color: {{ $isCorrect ? 'var(--success)' : 'var(--error)' }}">{{ $options[$userAns] ?? $userAns }}</span>
                    @if(!$isCorrect)
                        <br>Correct Answer: <span style="color: var(--success)">{{ $options[$correctAns] ?? $correctAns }}</span>
                    @endif
                </div>
                <div style="background: rgba(255,255,255,0.05); padding: 12px; border-radius: 8px; font-size: 0.9rem; color: #94a3b8; display: flex; justify-content: space-between; align-items: flex-start; gap: 15px;">
                    <div>
                        <strong>Standard Explanation:</strong> {{ $q->explanation }}
                    </div>
                    @if(!$isCorrect)
                        <button type="button" class="btn btn-explain" data-question="{{ $q->question_text }}" data-user="{{ $options[$userAns] ?? $userAns }}" data-correct="{{ $options[$correctAns] ?? $correctAns }}" style="padding: 6px 12px; font-size: 0.8rem; flex-shrink: 0; background: linear-gradient(135deg, #f59e0b, #d97706); border: none;">🧠 Ask AI</button>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <a href="{{ route('home') }}" class="btn" style="margin: 30px 0;">Take Another Quiz</a>
</div>

<!-- AI Explainer Modal -->
<div id="ai-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(5px); z-index: 2000; align-items: center; justify-content: center;">
    <div style="background: var(--card-bg); border: 1px solid rgba(167, 139, 250, 0.4); border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.8); position: relative;">
        <span id="close-modal" style="position: absolute; top: 15px; right: 20px; font-size: 1.5rem; cursor: pointer; color: #cbd5e1;">&times;</span>
        <h3 style="color: #a78bfa; display: flex; align-items: center; gap: 10px; margin-bottom: 20px;">
            🧠 AI Deep Explanation
        </h3>
        <div id="ai-modal-content" style="color: #e2e8f0; line-height: 1.6;">
            <div style="text-align: center; padding: 20px;">
                <span style="font-size: 2rem; display: inline-block; animation: pulse 1s infinite;">⏳</span>
                <p style="margin-top: 10px; color: var(--text-muted);">Generating personalized explanation...</p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.2); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }
</style>

<script>
    const modal = document.getElementById('ai-modal');
    const modalContent = document.getElementById('ai-modal-content');
    const closeModal = document.getElementById('close-modal');

    document.querySelectorAll('.btn-explain').forEach(btn => {
        btn.addEventListener('click', async function() {
            modal.style.display = 'flex';
            modalContent.innerHTML = `
                <div style="text-align: center; padding: 20px;">
                    <span style="font-size: 2rem; display: inline-block; animation: pulse 1s infinite;">⏳</span>
                    <p style="margin-top: 10px; color: var(--text-muted);">Analyzing your answer...</p>
                </div>`;
            
            const question = this.dataset.question;
            const userAnswer = this.dataset.user;
            const correctAnswer = this.dataset.correct;

            try {
                const response = await fetch('{{ route('chat.explain') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ question, user_answer: userAnswer, correct_answer: correctAnswer })
                });
                const result = await response.json();
                
                if (result.success) {
                    modalContent.innerHTML = `<p>${result.explanation}</p>`;
                } else {
                    modalContent.innerHTML = `<p style="color: var(--error);">Error generating explanation.</p>`;
                }
            } catch (error) {
                modalContent.innerHTML = `<p style="color: var(--error);">Network error occurred.</p>`;
            }
        });
    });

    closeModal.addEventListener('click', () => { modal.style.display = 'none'; });
    window.addEventListener('click', (e) => {
        if (e.target === modal) modal.style.display = 'none';
    });
</script>
@endsection
