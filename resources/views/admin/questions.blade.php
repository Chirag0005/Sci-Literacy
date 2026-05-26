@extends('layouts.app')

@section('content')
<div class="glass-panel" style="max-width: 900px;">
    <h2>Admin Panel - Manage Questions</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error">
            <ul style="margin-left: 20px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 12px; margin-bottom: 40px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
            <h3 style="margin: 0;">Add New Question</h3>
            <button type="button" id="ai-generate-btn" class="btn" style="background: linear-gradient(135deg, #a78bfa, #8b5cf6); border: none;">✨ Auto-Generate with AI</button>
        </div>
        <form action="{{ route('admin.questions.store') }}" method="POST" id="question-form">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="grid-column: span 2;">
                    <label>Question Text</label>
                    <textarea name="question_text" id="q_text" class="form-control" rows="2" required></textarea>
                </div>
                <div>
                    <label>Category</label>
                    <input type="text" name="category" id="q_category" class="form-control" placeholder="e.g. Physics" required>
                </div>
                <div>
                    <label>Correct Option (A, B, C, or D)</label>
                    <select name="correct_option" id="q_correct" class="form-control" required style="appearance: none;">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div>
                    <label>Option A</label>
                    <input type="text" name="option_a" id="q_opt_a" class="form-control" required>
                </div>
                <div>
                    <label>Option B</label>
                    <input type="text" name="option_b" id="q_opt_b" class="form-control" required>
                </div>
                <div>
                    <label>Option C</label>
                    <input type="text" name="option_c" id="q_opt_c" class="form-control" required>
                </div>
                <div>
                    <label>Option D</label>
                    <input type="text" name="option_d" id="q_opt_d" class="form-control" required>
                </div>
                <div style="grid-column: span 2;">
                    <label>Explanation (for the Results Page)</label>
                    <textarea name="explanation" id="q_explanation" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <button type="submit" class="btn" style="margin-top: 15px;">Save Question</button>
        </form>
    </div>

    <script>
        document.getElementById('ai-generate-btn').addEventListener('click', async function() {
            const btn = this;
            const originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Generating...';
            btn.disabled = true;

            try {
                const response = await fetch('{{ route('admin.questions.generate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('q_text').value = data.question_text || '';
                    document.getElementById('q_category').value = data.category || '';
                    document.getElementById('q_opt_a').value = data.option_a || '';
                    document.getElementById('q_opt_b').value = data.option_b || '';
                    document.getElementById('q_opt_c').value = data.option_c || '';
                    document.getElementById('q_opt_d').value = data.option_d || '';
                    document.getElementById('q_correct').value = data.correct_option || 'A';
                    document.getElementById('q_explanation').value = data.explanation || '';
                } else {
                    alert(result.message || 'Error generating question.');
                }
            } catch (error) {
                alert('Network error occurred.');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    </script>

    <h3>Existing Questions</h3>
    @foreach($questions as $q)
        <div style="background: rgba(0,0,0,0.1); padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid rgba(255,255,255,0.05); display: flex; justify-content: space-between; align-items: start;">
            <div>
                <span style="font-size:0.8rem; background: var(--accent); padding: 2px 6px; border-radius: 4px; margin-bottom: 5px; display: inline-block;">{{ $q->category }}</span>
                <p style="font-weight: 600;">{{ $q->question_text }}</p>
                <div style="font-size: 0.9rem; color: var(--text-muted); margin-top: 5px;">
                    A: {{ $q->option_a }} | B: {{ $q->option_b }} | C: {{ $q->option_c }} | D: {{ $q->option_d }}<br>
                    <strong>Correct: {{ $q->correct_option }}</strong>
                </div>
            </div>
            <form action="{{ route('admin.questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Delete this question?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn" style="background: var(--error); padding: 8px 12px; font-size: 0.9rem;">Delete</button>
            </form>
        </div>
    @endforeach
</div>
@endsection
