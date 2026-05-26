<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\User;
use App\Services\MongoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed MongoDB Admin User
        $existingAdmin = MongoService::execute('findOne', 'users', ['email' => 'admin@admin.com']);
        if (!$existingAdmin) {
            MongoService::execute('insert', 'users', [], [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'is_admin' => true,
                'xp' => 0,
                'streak' => 0,
                'created_at' => now()->toIso8601String(),
                'updated_at' => now()->toIso8601String()
            ]);
        }

        // 2. Standard SQLite Admin User
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $questions = [
            [
                'question_text' => 'What is the primary function of DNA in living organisms?',
                'category' => 'Biology',
                'option_a' => 'To store and transmit genetic information',
                'option_b' => 'To provide energy for the cell',
                'option_c' => 'To break down proteins',
                'option_d' => 'To transport oxygen in the blood',
                'correct_option' => 'A',
                'explanation' => 'DNA holds the instructions needed for an organism to develop, survive and reproduce.'
            ],
            [
                'question_text' => 'Which of the following best describes a scientific theory?',
                'category' => 'Scientific Method',
                'option_a' => 'A random guess made by a scientist',
                'option_b' => 'A well-substantiated explanation acquired through the scientific method',
                'option_c' => 'An absolute truth that can never change',
                'option_d' => 'A personal belief about how the world works',
                'correct_option' => 'B',
                'explanation' => 'In science, a theory is a rigorously tested and supported explanation for a phenomenon.'
            ],
            [
                'question_text' => 'How do vaccines primarily work?',
                'category' => 'Medicine',
                'option_a' => 'By killing viruses directly in the bloodstream',
                'option_b' => 'By providing vitamins to boost general health',
                'option_c' => 'By stimulating the immune system to recognize and fight specific pathogens',
                'option_d' => 'By physically blocking bacteria from entering the body',
                'correct_option' => 'C',
                'explanation' => 'Vaccines safely expose your immune system to a harmless piece of a pathogen.'
            ],
            [
                'question_text' => 'What is the process by which plants convert sunlight into energy?',
                'category' => 'Biology',
                'option_a' => 'Respiration',
                'option_b' => 'Transpiration',
                'option_c' => 'Photosynthesis',
                'option_d' => 'Fermentation',
                'correct_option' => 'C',
                'explanation' => 'Photosynthesis is the process used by plants to harness energy from sunlight.'
            ],
            [
                'question_text' => 'Which of these is a key characteristic of the scientific method?',
                'category' => 'Scientific Method',
                'option_a' => 'Ignoring evidence that contradicts your hypothesis',
                'option_b' => 'Making conclusions based on anecdotal evidence',
                'option_c' => 'Formulating testable hypotheses and conducting experiments',
                'option_d' => 'Accepting authoritative statements without question',
                'correct_option' => 'C',
                'explanation' => 'The scientific method relies on empirical evidence and experimentation.'
            ],
            [
                'question_text' => 'What causes the seasons on Earth?',
                'category' => 'Astronomy',
                'option_a' => 'The varying distance between the Earth and the Sun',
                'option_b' => 'The tilt of the Earth\'s rotational axis',
                'option_c' => 'Changes in the Sun\'s energy output',
                'option_d' => 'The gravitational pull of the Moon',
                'correct_option' => 'B',
                'explanation' => 'Earth\'s axis is tilted, creating summer and winter as it orbits.'
            ],
            [
                'question_text' => 'What is a placebo in the context of a medical trial?',
                'category' => 'Medicine',
                'option_a' => 'The most effective drug being tested',
                'option_b' => 'An inactive substance given to a control group',
                'option_c' => 'A severe side effect of a medication',
                'option_d' => 'The statistical method used to analyze results',
                'correct_option' => 'B',
                'explanation' => 'A placebo is a "dummy" treatment used to test if the actual drug has a real effect.'
            ],
            [
                'question_text' => 'Why is peer review important in scientific publishing?',
                'category' => 'Scientific Method',
                'option_a' => 'It ensures the author gets paid for their work',
                'option_b' => 'It guarantees that the research is 100% correct',
                'option_c' => 'It provides independent scrutiny of the methods and findings by experts',
                'option_d' => 'It makes the paper longer and harder to read',
                'correct_option' => 'C',
                'explanation' => 'Peer review acts as a quality control mechanism.'
            ],
            [
                'question_text' => 'Which of the following is an example of confirmation bias?',
                'category' => 'Psychology',
                'option_a' => 'Changing your mind when presented with new, conflicting evidence',
                'option_b' => 'Seeking out information that supports your existing beliefs while ignoring contradictory data',
                'option_c' => 'Conducting a double-blind scientific experiment',
                'option_d' => 'Basing your conclusions solely on mathematical models',
                'correct_option' => 'B',
                'explanation' => 'Confirmation bias is the human tendency to favor information that confirms prior beliefs.'
            ],
            [
                'question_text' => 'What is the main difference between weather and climate?',
                'category' => 'Earth Science',
                'option_a' => 'Weather is measured in degrees, climate is measured in inches',
                'option_b' => 'Weather describes short-term conditions, climate describes long-term averages',
                'option_c' => 'Weather only happens in the atmosphere, climate happens in the oceans',
                'option_d' => 'There is no difference; they mean the exact same thing',
                'correct_option' => 'B',
                'explanation' => 'Weather is day-to-day, while climate is the statistical average over decades.'
            ]
        ];

        // 3. Seed MongoDB Questions
        foreach ($questions as $q) {
            $existingQ = MongoService::execute('findOne', 'questions', ['question_text' => $q['question_text']]);
            if (!$existingQ) {
                MongoService::execute('insert', 'questions', [], $q);
            }
        }

        // 4. Standard SQLite Questions
        foreach ($questions as $q) {
            Question::updateOrCreate(
                ['question_text' => $q['question_text']],
                $q
            );
        }
    }
}
